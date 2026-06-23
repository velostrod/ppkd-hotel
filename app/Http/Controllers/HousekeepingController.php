<?php

namespace App\Http\Controllers;

use App\Enums\HousekeepingRequestStatus;
use App\Enums\HousekeepingRequestType;
use App\Enums\RoomStatus;
use App\Http\Requests\SubmitInspectionRequest;
use App\Models\HousekeepingRequest;
use App\Models\RoomInspection;
use App\Models\RoomInspectionItem;
use App\Models\Room;
use App\Models\User;
use App\Models\LaundryRequest;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Helpers\ActivityLogger;
use App\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HousekeepingController extends Controller
{
    // ==========================================
    // CLEANING OPERATIONS
    // ==========================================
    
    public function dashboard()
    {
        $pendingRequests = HousekeepingRequest::whereIn('status', HousekeepingRequestStatus::activeStatuses())
            ->with(['room.roomType', 'reservation.guest', 'requester', 'assignee'])
            ->orderBy('priority', 'desc')
            ->get();

        $pendingInspections = RoomInspection::where('status', 'pending')
            ->with(['room', 'reservation.guest'])
            ->get();

        $pendingLaundry = LaundryRequest::whereIn('status', \App\Enums\LaundryStatus::activeStatuses())
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
            'status' => HousekeepingRequestStatus::Assigned->value,
        ]);

        ActivityLogger::log('update', 'housekeeping_requests', "Menugaskan tiket cleaning Kamar {$hkRequest->room->room_number} ke staf ID: {$validated['assigned_to']}");

        return back()->with('success', 'Petugas Housekeeping berhasil ditugaskan.');
    }

    public function startRequest(HousekeepingRequest $hkRequest)
    {
        $hkRequest->update(['status' => HousekeepingRequestStatus::InProgress->value]);
        $hkRequest->room->update(['status' => RoomStatus::Cleaning->value]);

        ActivityLogger::log('update', 'housekeeping_requests', "Mulai membersihkan Kamar {$hkRequest->room->room_number}");

        return back()->with('success', 'Status pengerjaan diperbarui menjadi IN PROGRESS.');
    }

    public function completeRequest(HousekeepingRequest $hkRequest)
    {
        DB::beginTransaction();
        try {
            $hkRequest->update([
                'status' => HousekeepingRequestStatus::Completed->value,
                'completed_time' => now(),
            ]);

            $requestType = HousekeepingRequestType::tryFrom($hkRequest->request_type);
            if ($requestType === null) {
                DB::rollBack();
                return back()->with('error', "Tipe request tidak dikenal: {$hkRequest->request_type}. Hubungi administrator.");
            }
            $finalStatus = $requestType->completedRoomStatus();

            $hkRequest->room->update(['status' => $finalStatus->value]);

            ActivityLogger::log('update', 'housekeeping_requests', "Menyelesaikan pengerjaan Kamar {$hkRequest->room->room_number}. Status kamar menjadi {$finalStatus->value}");

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

    public function submitInspection(SubmitInspectionRequest $request, RoomInspection $inspection)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
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

            // If damage/loss found, add charge to reservation
            if ($validated['damage_found'] && $validated['damage_cost'] > 0 && $inspection->reservation_id) {
                $damageType = ChargeType::where('code', 'damage')->firstOrFail();
                Charge::create([
                    'reservation_id' => $inspection->reservation_id,
                    'charge_type_id' => $damageType->id,
                    'amount' => $validated['damage_cost'],
                    'description' => "Hasil Room Inspection Kamar {$inspection->room->room_number}: " . $validated['notes'],
                    'created_by' => auth()->id(),
                ]);
            }

            // Update room status to dirty for cleaning
            $inspection->room->update(['status' => RoomStatus::Dirty->value]);

            ActivityLogger::log('update', 'room_inspections', "Inspeksi Kamar {$inspection->room->room_number} selesai. Hasil: {$validated['room_condition']}, Biaya kerusakan: " . CurrencyHelper::formatIDRWithPrefix($validated['damage_cost']));

            DB::commit();
            return redirect()->route('housekeeping.dashboard')->with('success', 'Hasil inspeksi kamar berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memproses hasil inspeksi', ['exception' => $e]);
            return back()->with('error', 'Gagal memproses hasil inspeksi. Silakan coba lagi.')->withInput();
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
        $newStatus = RoomStatus::from($validated['status']);

        DB::beginTransaction();
        try {
            $room->update([
                'status' => $newStatus->value,
                'notes' => $validated['notes'] ?? $room->notes,
            ]);

            $this->syncHousekeepingForStatusChange($room, $newStatus, $validated['notes'] ?? null);

            ActivityLogger::log('update', 'rooms', "Mengubah manual status Kamar {$room->room_number} dari {$oldStatus} ke {$newStatus->value}");

            DB::commit();
            return back()->with('success', "Status Kamar {$room->room_number} berhasil diubah menjadi " . strtoupper($newStatus->value));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui status kamar', ['exception' => $e]);
            return back()->with('error', 'Gagal memperbarui status kamar. Silakan coba lagi.');
        }
    }

    /**
     * Sync housekeeping requests and inspections when room status is manually changed.
     */
    private function syncHousekeepingForStatusChange(Room $room, RoomStatus $newStatus, ?string $notes): void
    {
        $activeStatuses = HousekeepingRequestStatus::activeStatuses();

        if (in_array($newStatus, RoomStatus::completionStatuses())) {
            // Complete active housekeeping requests
            HousekeepingRequest::where('room_id', $room->id)
                ->whereIn('status', $activeStatuses)
                ->update([
                    'status' => HousekeepingRequestStatus::Completed->value,
                    'completed_time' => now(),
                ]);

            // Complete active room inspections
            RoomInspection::where('room_id', $room->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'inspection_date' => now(),
                    'notes' => $notes ?? 'Diselesaikan secara manual melalui update status kamar.',
                ]);
        } elseif ($newStatus === RoomStatus::Cleaning) {
            $activeReq = HousekeepingRequest::where('room_id', $room->id)
                ->whereIn('status', [HousekeepingRequestStatus::Pending->value, HousekeepingRequestStatus::Assigned->value])
                ->first();

            if ($activeReq) {
                $activeReq->update(['status' => HousekeepingRequestStatus::InProgress->value]);
            } else {
                HousekeepingRequest::create([
                    'room_id' => $room->id,
                    'requested_by' => auth()->id(),
                    'request_type' => HousekeepingRequestType::DeepCleaning->value,
                    'priority' => 'normal',
                    'status' => HousekeepingRequestStatus::InProgress->value,
                    'request_time' => now(),
                    'notes' => $notes ?? 'Pembersihan manual via update status.',
                ]);
            }
        } elseif ($newStatus === RoomStatus::Dirty) {
            $activeExists = HousekeepingRequest::where('room_id', $room->id)
                ->whereIn('status', $activeStatuses)
                ->exists();

            if (!$activeExists) {
                HousekeepingRequest::create([
                    'room_id' => $room->id,
                    'requested_by' => auth()->id(),
                    'request_type' => HousekeepingRequestType::DeepCleaning->value,
                    'priority' => 'normal',
                    'status' => HousekeepingRequestStatus::Pending->value,
                    'request_time' => now(),
                    'notes' => $notes ?? 'Pembersihan manual via update status.',
                ]);
            }
        } elseif ($newStatus === RoomStatus::Maintenance) {
            $activeExists = HousekeepingRequest::where('room_id', $room->id)
                ->whereIn('status', $activeStatuses)
                ->exists();

            if (!$activeExists) {
                HousekeepingRequest::create([
                    'room_id' => $room->id,
                    'requested_by' => auth()->id(),
                    'request_type' => HousekeepingRequestType::Maintenance->value,
                    'priority' => 'normal',
                    'status' => HousekeepingRequestStatus::Pending->value,
                    'request_time' => now(),
                    'notes' => $notes ?? 'Pemeliharaan manual via update status.',
                ]);
            }
        }
    }

    // ==========================================
    // HISTORIES
    // ==========================================

    public function cleaningHistory()
    {
        $cleanings = HousekeepingRequest::where('status', HousekeepingRequestStatus::Completed->value)
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
