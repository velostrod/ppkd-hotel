<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingRequest;
use App\Models\HousekeepingRequestItem;
use App\Models\RoomInspection;
use App\Models\RoomInspectionItem;
use App\Models\Room;
use App\Models\User;
use App\Models\LaundryRequest;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HousekeepingController extends Controller
{
    // ==========================================
    // CLEANING OPERATIONS
    // ==========================================
    
    public function dashboard()
    {
        $pendingRequests = HousekeepingRequest::whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with(['room.roomType', 'reservation.guest', 'requester', 'assignee'])
            ->orderBy('priority', 'desc')
            ->get();

        $pendingInspections = RoomInspection::where('status', 'pending')
            ->with(['room', 'reservation.guest'])
            ->get();

        $pendingLaundry = LaundryRequest::whereIn('status', ['requested', 'picked_up', 'processing', 'ready'])
            ->with(['reservation.room', 'guest', 'requester'])
            ->get();

        $hkStaff = User::whereHas('role', function($q) {
            $q->where('name', 'housekeeping');
        })->where('status', 'active')->get();

        $rooms = Room::with('roomType')->orderBy('room_number')->get();

        return view('housekeeping.dashboard', compact('pendingRequests', 'pendingInspections', 'pendingLaundry', 'hkStaff', 'rooms'));
    }

    public function assignRequest(Request $request, HousekeepingRequest $hkRequest)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $hkRequest->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
        ]);

        ActivityLogger::log('update', 'housekeeping_requests', "Menugaskan tiket cleaning Kamar {$hkRequest->room->room_number} ke staf ID: {$validated['assigned_to']}");

        return back()->with('success', 'Petugas Housekeeping berhasil ditugaskan.');
    }

    public function startRequest(HousekeepingRequest $hkRequest)
    {
        $hkRequest->update(['status' => 'in_progress']);
        $hkRequest->room->update(['status' => 'cleaning']);

        ActivityLogger::log('update', 'housekeeping_requests', "Mulai membersihkan Kamar {$hkRequest->room->room_number}");

        return back()->with('success', 'Status pengerjaan diperbarui menjadi IN PROGRESS.');
    }

    public function completeRequest(HousekeepingRequest $hkRequest)
    {
        DB::beginTransaction();
        try {
            $hkRequest->update([
                'status' => 'completed',
                'completed_time' => now(),
            ]);

            // Determine final room status based on request type
            // checkout_cleaning -> inspected
            // stayover_cleaning/linen -> occupied (back to active guest occupancy)
            // deep_cleaning/maintenance -> available
            $finalStatus = 'available';
            if ($hkRequest->request_type === 'checkout_cleaning') {
                $finalStatus = 'inspected';
            } elseif (in_array($hkRequest->request_type, ['stayover_cleaning', 'linen_replacement'])) {
                $finalStatus = 'occupied';
            }

            $hkRequest->room->update(['status' => $finalStatus]);

            ActivityLogger::log('update', 'housekeeping_requests', "Menyelesaikan pengerjaan Kamar {$hkRequest->room->room_number}. Status kamar menjadi {$finalStatus}");

            DB::commit();
            return back()->with('success', 'Cleaning request diselesaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penyelesaian.');
        }
    }

    // ==========================================
    // INSPECTION OPERATIONS
    // ==========================================

    public function showInspectionForm(RoomInspection $inspection)
    {
        $inspection->load(['room', 'reservation.guest']);
        return view('housekeeping.inspect_form', compact('inspection'));
    }

    public function submitInspection(Request $request, RoomInspection $inspection)
    {
        $validated = $request->validate([
            'room_condition' => 'required|in:good,needs_cleaning,damaged',
            'damage_found' => 'required|boolean',
            'damage_cost' => 'required_if:damage_found,1|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required|string',
            'items.*.condition' => 'required|in:good,damaged,missing',
            'items.*.charge_amount' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update Inspection record
            $inspection->update([
                'inspected_by' => auth()->id(),
                'inspection_date' => now(),
                'room_condition' => $validated['room_condition'],
                'damage_found' => $validated['damage_found'],
                'damage_cost' => $validated['damage_found'] ? $validated['damage_cost'] : 0,
                'notes' => $validated['notes'],
                'status' => 'completed',
            ]);

            // Save Inspection items
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    RoomInspectionItem::create([
                        'room_inspection_id' => $inspection->id,
                        'item_name' => $itemData['item_name'],
                        'condition' => $itemData['condition'],
                        'charge_amount' => $itemData['charge_amount'],
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            // If damage/loss found, add charge automatically to reservation
            if ($validated['damage_found'] && $validated['damage_cost'] > 0 && $inspection->reservation_id) {
                $damageType = ChargeType::where('code', 'damage')->first();
                Charge::create([
                    'reservation_id' => $inspection->reservation_id,
                    'charge_type_id' => $damageType ? $damageType->id : 2, // Fallback to 2
                    'amount' => $validated['damage_cost'],
                    'description' => "Hasil Room Inspection Kamar {$inspection->room->room_number}: " . $validated['notes'],
                    'created_by' => auth()->id(),
                ]);
            }

            // Update room status
            // If condition needs_cleaning or damaged, mark as dirty
            // Else, mark room as dirty for normal checkout cleaning process
            $inspection->room->update(['status' => 'dirty']);

            ActivityLogger::log('update', 'room_inspections', "Inspeksi Kamar {$inspection->room->room_number} selesai. Hasil: {$validated['room_condition']}, Biaya kerusakan: Rp " . number_format($validated['damage_cost'], 0, ',', '.'));

            DB::commit();
            return redirect()->route('housekeeping.dashboard')->with('success', 'Hasil inspeksi kamar berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses hasil inspeksi: ' . $e->getMessage())->withInput();
        }
    }

    // ==========================================
    // LAUNDRY PROCESSING (HK Side)
    // ==========================================

    public function updateLaundryStatus(Request $request, LaundryRequest $laundry)
    {
        $validated = $request->validate([
            'status' => 'required|in:picked_up,processing,ready,delivered,cancelled',
        ]);

        $laundry->update([
            'status' => $validated['status'],
            'handled_by' => auth()->id(),
        ]);

        ActivityLogger::log('update', 'laundry_requests', "Mengubah status laundry Kamar {$laundry->reservation->room->room_number} menjadi {$validated['status']}");

        return back()->with('success', 'Status request laundry berhasil diperbarui.');
    }

    // ==========================================
    // MANUAL ROOM STATUS UPDATES
    // ==========================================

    public function updateRoomStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,reserved,occupied,dirty,cleaning,inspected,maintenance,out_of_order',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $room->status;
        $room->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $room->notes,
        ]);

        ActivityLogger::log('update', 'rooms', "Mengubah manual status Kamar {$room->room_number} dari {$oldStatus} ke {$validated['status']}");

        return back()->with('success', "Status Kamar {$room->room_number} berhasil diubah menjadi " . strtoupper($validated['status']));
    }

    // ==========================================
    // HISTORIES
    // ==========================================

    public function cleaningHistory()
    {
        $cleanings = HousekeepingRequest::where('status', 'completed')
            ->with(['room.roomType', 'reservation.guest', 'assignee'])
            ->orderBy('completed_time', 'desc')
            ->paginate(15);
        return view('housekeeping.cleaning_history', compact('cleanings'));
    }

    public function inspectionHistory()
    {
        $inspections = RoomInspection::where('status', 'completed')
            ->with(['room', 'inspector', 'reservation.guest'])
            ->orderBy('inspection_date', 'desc')
            ->paginate(15);
        return view('housekeeping.inspection_history', compact('inspections'));
    }
}
