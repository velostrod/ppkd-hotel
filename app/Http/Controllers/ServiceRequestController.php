<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\FoodItem;
use App\Models\FoodCategory;
use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use App\Models\LaundryRequest;
use App\Models\HousekeepingRequest;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    // List active checked-in rooms for dropdown/list selection
    private function getActiveReservations()
    {
        return Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->get();
    }

    // ==========================================
    // LAUNDRY REQUESTS (FO Side)
    // ==========================================
    
    public function indexLaundry()
    {
        $activeReservations = $this->getActiveReservations();
        $laundryRequests = LaundryRequest::with(['reservation.room', 'guest', 'requester', 'handler'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('services.laundry', compact('activeReservations', 'laundryRequests'));
    }

    public function storeLaundry(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'notes' => 'required|string',
            'total_charge' => 'required|numeric|min:0',
        ]);

        $res = Reservation::find($validated['reservation_id']);
        if ($res->status !== 'checked_in') {
            return back()->with('error', 'Laundry request hanya untuk tamu yang berstatus Checked In.');
        }

        DB::beginTransaction();
        try {
            // Create Laundry Request
            $laundry = LaundryRequest::create([
                'reservation_id' => $res->id,
                'guest_id' => $res->guest_id,
                'requested_by' => auth()->id(),
                'request_date' => now(),
                'status' => 'requested',
                'notes' => $validated['notes'],
                'total_charge' => $validated['total_charge'],
            ]);

            // Add charge to reservation
            $laundryType = ChargeType::where('code', 'laundry')->first();
            Charge::create([
                'reservation_id' => $res->id,
                'charge_type_id' => $laundryType ? $laundryType->id : 1, // Fallback to 1 if not exists
                'amount' => $validated['total_charge'],
                'description' => "Laundry Service: " . $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            ActivityLogger::log('create', 'laundry_requests', "Input request laundry untuk Kamar {$res->room->room_number}, biaya: Rp " . number_format($validated['total_charge'], 0, ',', '.'));

            DB::commit();
            return back()->with('success', 'Laundry request berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat request laundry: ' . $e->getMessage());
        }
    }

    // ==========================================
    // FOOD AND BEVERAGE ORDERS (FO Side)
    // ==========================================

    public function indexFnb()
    {
        $activeReservations = $this->getActiveReservations();
        $foodItems = FoodItem::where('is_available', true)->with('foodCategory')->get();
        $orders = FnbOrder::with(['reservation.room', 'guest', 'requester', 'handler', 'items.foodItem'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('services.fnb', compact('activeReservations', 'foodItems', 'orders'));
    }

    public function storeFnb(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'items' => 'required|array|min:1',
            'items.*.food_item_id' => 'required|exists:food_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $res = Reservation::find($validated['reservation_id']);
        if ($res->status !== 'checked_in') {
            return back()->with('error', 'Pesanan FnB hanya untuk tamu yang berstatus Checked In.');
        }

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            
            // Generate temporary order record
            $order = FnbOrder::create([
                'reservation_id' => $res->id,
                'guest_id' => $res->guest_id,
                'requested_by' => auth()->id(),
                'order_time' => now(),
                'status' => 'pending',
                'total_price' => 0, // Will update shortly
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $food = FoodItem::find($itemData['food_item_id']);
                $subtotal = $food->price * $itemData['qty'];
                $totalPrice += $subtotal;

                FnbOrderItem::create([
                    'fnb_order_id' => $order->id,
                    'food_item_id' => $food->id,
                    'qty' => $itemData['qty'],
                    'price' => $food->price,
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Update order price
            $order->update(['total_price' => $totalPrice]);

            // Add charge to reservation invoice
            $fnbType = ChargeType::where('code', 'fnb')->first();
            Charge::create([
                'reservation_id' => $res->id,
                'charge_type_id' => $fnbType ? $fnbType->id : 1,
                'amount' => $totalPrice,
                'description' => "FnB Order #{$order->id}: " . ($validated['notes'] ?? 'Order makanan/minuman'),
                'created_by' => auth()->id(),
            ]);

            ActivityLogger::log('create', 'fnb_orders', "Membuat order FnB #{$order->id} untuk Kamar {$res->room->room_number}, total: Rp " . number_format($totalPrice, 0, ',', '.'));

            DB::commit();
            return back()->with('success', 'Order FnB berhasil dikirim ke dapur.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat order FnB: ' . $e->getMessage());
        }
    }

    // ==========================================
    // HOUSEKEEPING REQUESTS (FO Side)
    // ==========================================

    public function indexCleaning()
    {
        $activeReservations = $this->getActiveReservations();
        $cleaningRequests = HousekeepingRequest::with(['reservation.room', 'room', 'requester', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('services.cleaning', compact('activeReservations', 'cleaningRequests'));
    }

    public function storeCleaning(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'request_type' => 'required|in:stayover_cleaning,deep_cleaning,linen_replacement,maintenance',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        $res = Reservation::find($validated['reservation_id']);
        if ($res->status !== 'checked_in') {
            return back()->with('error', 'Stayover cleaning/linen replacement hanya untuk tamu aktif.');
        }

        DB::beginTransaction();
        try {
            $hkRequest = HousekeepingRequest::create([
                'reservation_id' => $res->id,
                'room_id' => $res->room_id,
                'requested_by' => auth()->id(),
                'request_type' => $validated['request_type'],
                'priority' => $validated['priority'],
                'status' => 'pending',
                'request_time' => now(),
                'notes' => $validated['notes'],
            ]);

            // Update room status temporary to cleaning if stayover
            // Wait, stayover cleaning temporary changes room status to cleaning
            $res->room->update(['status' => 'cleaning']);

            ActivityLogger::log('create', 'housekeeping_requests', "Request housekeeping untuk Kamar {$res->room->room_number} (Tipe: {$validated['request_type']})");

            DB::commit();
            return back()->with('success', 'Permintaan Housekeeping berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim request Housekeeping: ' . $e->getMessage());
        }
    }
}
