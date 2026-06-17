<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use App\Models\LaundryRequest;
use App\Models\Charge;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function getDates(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));
        return [$startDate, $endDate];
    }

    public function reservations(Request $request)
    {
        [$start, $end] = $this->getDates($request);
        
        $reservations = Reservation::with(['guest', 'room.roomType', 'invoice'])
            ->whereBetween('checkin_date', [$start, $end])
            ->orderBy('checkin_date', 'asc')
            ->get();

        return view('reports.reservations', compact('reservations', 'start', 'end'));
    }

    public function occupancy(Request $request)
    {
        [$start, $end] = $this->getDates($request);

        // Fetch counts per room status
        $roomsCount = Room::count();
        $statusCounts = Room::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Standardize keys
        $statuses = ['available', 'reserved', 'occupied', 'dirty', 'cleaning', 'inspected', 'maintenance', 'out_of_order'];
        $roomStatuses = [];
        foreach ($statuses as $st) {
            $roomStatuses[$st] = $statusCounts[$st] ?? 0;
        }

        // Calculate occupancy rate over the selected period
        // Occupancy Rate = (Occupied Room Nights / Total Room Nights) * 100
        $totalDays = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $totalRoomNights = $roomsCount * $totalDays;

        $occupiedRoomNights = 0;
        
        // Find reservations that were active in the period
        $activeReservations = Reservation::where('status', '!=', 'cancelled')
            ->where('checkin_date', '<=', $end)
            ->where('checkout_date', '>=', $start)
            ->get();

        foreach ($activeReservations as $res) {
            $resStart = Carbon::parse(max($res->checkin_date->format('Y-m-d'), $start));
            $resEnd = Carbon::parse(min($res->checkout_date->format('Y-m-d'), $end));
            $nights = $resStart->diffInDays($resEnd);
            $occupiedRoomNights += max(0, $nights);
        }

        $occupancyRate = $totalRoomNights > 0 ? round(($occupiedRoomNights / $totalRoomNights) * 100, 2) : 0;

        return view('reports.occupancy', compact('roomStatuses', 'roomsCount', 'occupancyRate', 'occupiedRoomNights', 'totalRoomNights', 'start', 'end'));
    }

    public function fnb(Request $request)
    {
        [$start, $end] = $this->getDates($request);

        $orders = FnbOrder::with(['reservation.room', 'guest', 'items.foodItem'])
            ->whereBetween('order_time', [
                Carbon::parse($start)->startOfDay(), 
                Carbon::parse($end)->endOfDay()
            ])
            ->orderBy('order_time', 'desc')
            ->get();

        $totalRevenue = FnbOrder::where('status', 'delivered')
            ->whereBetween('order_time', [
                Carbon::parse($start)->startOfDay(), 
                Carbon::parse($end)->endOfDay()
            ])
            ->sum('total_price');

        // Top menu items count
        $topItems = FnbOrderItem::select('food_item_id', DB::raw('sum(qty) as total_qty'))
            ->whereHas('fnbOrder', function($q) use ($start, $end) {
                $q->where('status', 'delivered')
                  ->whereBetween('order_time', [
                      Carbon::parse($start)->startOfDay(), 
                      Carbon::parse($end)->endOfDay()
                  ]);
            })
            ->with('foodItem')
            ->groupBy('food_item_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        return view('reports.fnb', compact('orders', 'totalRevenue', 'topItems', 'start', 'end'));
    }

    public function revenue(Request $request)
    {
        [$start, $end] = $this->getDates($request);

        // Revenue source query
        // Query checkouts completed in the period to represent finalized billing
        $invoices = Invoice::whereHas('reservation', function($q) use ($start, $end) {
                $q->whereBetween('checkout_date', [$start, $end])
                  ->where('status', 'checked_out');
            })
            ->with('reservation.room.roomType')
            ->get();

        $totalRevenue = 0;
        $roomRevenue = 0;
        $extraBedRevenue = 0;
        $laundryRevenue = 0;
        $fnbRevenue = 0;
        $damageRevenue = 0;

        foreach ($invoices as $inv) {
            $totalRevenue += $inv->total_amount;
            
            // Calculate base room charge from details
            $res = $inv->reservation;
            $nights = $res->checkin_date->diffInDays($res->checkout_date);
            $nights = $nights > 0 ? $nights : 1;
            
            $roomRevenue += $res->room->roomType->base_price * $nights;

            // Extra bed from reservation details
            $extraBedRevenue += $res->details()->where('type', 'extra_bed')->sum(DB::raw('qty * price'));

            // Other charges breakdown
            $laundryRevenue += $res->charges()->whereHas('chargeType', function($q) {
                $q->where('code', 'laundry');
            })->sum('amount');

            $fnbRevenue += $res->charges()->whereHas('chargeType', function($q) {
                $q->where('code', 'fnb');
            })->sum('amount');

            $damageRevenue += $res->charges()->whereHas('chargeType', function($q) {
                $q->where('code', 'damage');
            })->sum('amount');
        }

        // Room Type Revenue breakdown
        $roomTypes = RoomType::all();
        $roomTypeRevenue = [];
        foreach ($roomTypes as $type) {
            $typeRev = 0;
            foreach ($invoices as $inv) {
                if ($inv->reservation->room->roomType->id === $type->id) {
                    // Approximate room portion
                    $res = $inv->reservation;
                    $nights = $res->checkin_date->diffInDays($res->checkout_date);
                    $nights = $nights > 0 ? $nights : 1;
                    $typeRev += $type->base_price * $nights;
                }
            }
            $roomTypeRevenue[$type->name] = $typeRev;
        }

        return view('reports.revenue', compact('totalRevenue', 'roomRevenue', 'extraBedRevenue', 'laundryRevenue', 'fnbRevenue', 'damageRevenue', 'roomTypeRevenue', 'start', 'end'));
    }

    public function summary(Request $request)
    {
        [$start, $end] = $this->getDates($request);

        $reservationsCount = Reservation::whereBetween('checkin_date', [$start, $end])->count();

        $checkinsCount = Reservation::where('status', 'checked_in')
            ->whereBetween('checkin_date', [$start, $end])
            ->count();

        $checkoutsCount = Reservation::where('status', 'checked_out')
            ->whereBetween('checkin_date', [$start, $end])
            ->count();

        $cancelledCount = Reservation::where('status', 'cancelled')
            ->whereBetween('checkin_date', [$start, $end])
            ->count();

        // Total earnings
        $totalEarnings = Payment::where('status', 'success')
            ->whereBetween('payment_date', [
                Carbon::parse($start)->startOfDay(), 
                Carbon::parse($end)->endOfDay()
            ])
            ->sum('amount');

        $fnbCount = FnbOrder::whereBetween('order_time', [
            Carbon::parse($start)->startOfDay(), 
            Carbon::parse($end)->endOfDay()
        ])->count();

        $laundryCount = LaundryRequest::whereBetween('request_date', [
            Carbon::parse($start)->startOfDay(), 
            Carbon::parse($end)->endOfDay()
        ])->count();

        $chargesCount = Charge::whereBetween('created_at', [
            Carbon::parse($start)->startOfDay(), 
            Carbon::parse($end)->endOfDay()
        ])->count();

        $invoicePaid = Invoice::where('status', 'paid')
            ->whereHas('reservation', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->whereBetween('invoice_date', [$start, $end])
            ->count();

        $invoiceUnpaid = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->whereHas('reservation', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->whereBetween('invoice_date', [$start, $end])
            ->count();

        // Calculate occupancy rate (same as above)
        $roomsCount = Room::count();
        $totalDays = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $totalRoomNights = $roomsCount * $totalDays;
        $occupiedRoomNights = 0;
        $activeReservations = Reservation::where('status', '!=', 'cancelled')
            ->where('checkin_date', '<=', $end)
            ->where('checkout_date', '>=', $start)
            ->get();
        foreach ($activeReservations as $res) {
            $resStart = Carbon::parse(max($res->checkin_date->format('Y-m-d'), $start));
            $resEnd = Carbon::parse(min($res->checkout_date->format('Y-m-d'), $end));
            $occupiedRoomNights += max(0, $resStart->diffInDays($resEnd));
        }
        $occupancyRate = $totalRoomNights > 0 ? round(($occupiedRoomNights / $totalRoomNights) * 100, 2) : 0;

        return view('reports.summary', compact(
            'reservationsCount', 'checkinsCount', 'checkoutsCount', 'cancelledCount',
            'totalEarnings', 'fnbCount', 'laundryCount', 'chargesCount',
            'invoicePaid', 'invoiceUnpaid', 'occupancyRate', 'start', 'end'
        ));
    }
}
