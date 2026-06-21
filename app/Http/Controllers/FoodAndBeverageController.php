<?php

namespace App\Http\Controllers;

use App\Enums\FnbOrderStatus;
use App\Models\FnbOrder;
use App\Models\Charge;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;

class FoodAndBeverageController extends Controller
{
    public function index()
    {
        $activeOrders = FnbOrder::whereIn('status', FnbOrderStatus::activeStatuses())
            ->with(['reservation.room', 'guest', 'requester', 'items.foodItem'])
            ->orderBy('order_time', 'asc')
            ->get();

        return view('fnb.index', compact('activeOrders'));
    }

    public function processOrder(Request $request, FnbOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'handled_by' => auth()->id(),
        ]);

        // If cancelled, zero out the corresponding charge
        if ($validated['status'] === FnbOrderStatus::Cancelled->value && $oldStatus !== FnbOrderStatus::Cancelled->value) {
            $chargeDescPattern = "FnB Order #{$order->id}:%";
            $charge = Charge::where('reservation_id', $order->reservation_id)
                ->where('description', 'like', $chargeDescPattern)
                ->first();

            if ($charge) {
                $charge->update([
                    'amount' => 0.00,
                    'description' => $charge->description . " (CANCELLED)",
                ]);
            }
        }

        ActivityLogger::log('update', 'fnb_orders', "Mengubah status order FnB #{$order->id} (Kamar {$order->reservation->room->room_number}) dari {$oldStatus} menjadi {$validated['status']}");

        return redirect()->route('fnb.index')->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function orderHistory()
    {
        $orders = FnbOrder::whereIn('status', FnbOrderStatus::completedStatuses())
            ->with(['reservation.room', 'guest', 'items.foodItem'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('fnb.history', compact('orders'));
    }
}
