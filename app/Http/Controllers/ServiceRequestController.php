<?php

namespace App\Http\Controllers;

use App\Enums\FnbOrderStatus;
use App\Enums\HousekeepingRequestStatus;
use App\Enums\LaundryStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Http\Requests\StoreCleaningRequest;
use App\Http\Requests\StoreFnbOrderRequest;
use App\Http\Requests\StoreLaundryRequest;
use App\Helpers\CurrencyHelper;
use App\Models\Reservation;
use App\Models\FoodItem;
use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use App\Models\LaundryRequest;
use App\Models\HousekeepingRequest;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceRequestController extends Controller
{
    /**
     * Get active checked-in reservations for dropdown selection.
     */
    private function getActiveReservations()
    {
        return Reservation::with(['guest', 'room'])
            ->checkedIn()
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

    public function storeLaundry(StoreLaundryRequest $request)
    {
        $validated = $request->validated();

        $res = Reservation::find($validated['reservation_id']);
        if ($res->status !== ReservationStatus::CheckedIn->value) {
            return back()->with('error', 'Laundry request hanya untuk tamu yang berstatus Checked In.');
        }

        DB::beginTransaction();
        try {
            LaundryRequest::create([
                'reservation_id' => $res->id,
                'guest_id' => $res->guest_id,
                'requested_by' => auth()->id(),
                'request_date' => now(),
                'status' => LaundryStatus::Requested->value,
                'notes' => $validated['notes'],
                'total_charge' => $validated['total_charge'],
            ]);

            // Add charge to reservation
            $laundryType = ChargeType::where('code', 'laundry')->firstOrFail();
            Charge::create([
                'reservation_id' => $res->id,
                'charge_type_id' => $laundryType->id,
                'amount' => $validated['total_charge'],
                'description' => "Laundry Service: " . $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            ActivityLogger::log('create', 'laundry_requests', "Input request laundry untuk Kamar {$res->room->room_number}, biaya: " . CurrencyHelper::formatIDRWithPrefix($validated['total_charge']));

            DB::commit();
            return back()->with('success', 'Laundry request berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mencatat laundry request', ['exception' => $e]);
            return back()->with('error', 'Gagal mencatat request laundry. Silakan coba lagi.');
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

    public function storeFnb(StoreFnbOrderRequest $request)
    {
        $validated = $request->validated();

        $res = Reservation::find($validated['reservation_id']);
        if ($res->status !== ReservationStatus::CheckedIn->value) {
            return back()->with('error', 'Pesanan FnB hanya untuk tamu yang berstatus Checked In.');
        }

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            
            $order = FnbOrder::create([
                'reservation_id' => $res->id,
                'guest_id' => $res->guest_id,
                'requested_by' => auth()->id(),
                'order_time' => now(),
                'status' => FnbOrderStatus::Pending->value,
                'total_price' => 0,
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

            $order->update(['total_price' => $totalPrice]);

            // Add charge to reservation invoice
            $fnbType = ChargeType::where('code', 'fnb')->firstOrFail();
            Charge::create([
                'reservation_id' => $res->id,
                'charge_type_id' => $fnbType->id,
                'amount' => $totalPrice,
                'description' => "FnB Order #{$order->id}: " . ($validated['notes'] ?? 'Order makanan/minuman'),
                'created_by' => auth()->id(),
            ]);

            ActivityLogger::log('create', 'fnb_orders', "Membuat order FnB #{$order->id} untuk Kamar {$res->room->room_number}, total: " . CurrencyHelper::formatIDRWithPrefix($totalPrice));

            DB::commit();
            return back()->with('success', 'Order FnB berhasil dikirim ke dapur.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mencatat order FnB', ['exception' => $e]);
            return back()->with('error', 'Gagal mencatat order FnB. Silakan coba lagi.');
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

    public function storeCleaning(StoreCleaningRequest $request)
    {
        $validated = $request->validated();

        $res = Reservation::find($validated['reservation_id']);
        if ($res->status !== ReservationStatus::CheckedIn->value) {
            return back()->with('error', 'Stayover cleaning/linen replacement hanya untuk tamu aktif.');
        }

        DB::beginTransaction();
        try {
            HousekeepingRequest::create([
                'reservation_id' => $res->id,
                'room_id' => $res->room_id,
                'requested_by' => auth()->id(),
                'request_type' => $validated['request_type'],
                'priority' => $validated['priority'],
                'status' => HousekeepingRequestStatus::Pending->value,
                'request_time' => now(),
                'notes' => $validated['notes'],
            ]);

            // Update room status temporary to cleaning
            $res->room->update(['status' => RoomStatus::Cleaning->value]);

            ActivityLogger::log('create', 'housekeeping_requests', "Request housekeeping untuk Kamar {$res->room->room_number} (Tipe: {$validated['request_type']})");

            DB::commit();
            return back()->with('success', 'Permintaan Housekeeping berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengirim request Housekeeping', ['exception' => $e]);
            return back()->with('error', 'Gagal mengirim request Housekeeping. Silakan coba lagi.');
        }
    }
}
