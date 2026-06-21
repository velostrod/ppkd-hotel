<?php

namespace App\Http\Controllers;

use App\Enums\FnbOrderStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use App\Models\LaundryRequest;
use App\Models\Charge;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {}

    private function getDates(Request $request): array
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));
        return [$startDate, $endDate];
    }

    public function reservations(Request $request)
    {
        [$start, $end] = $this->getDates($request);
        $roomTypeId = $request->input('room_type_id');

        $reservations = Reservation::with(['guest', 'room.roomType', 'invoice'])
            ->whereBetween('checkin_date', [$start, $end])
            ->when($roomTypeId, fn($q) => $q->whereHas('room', fn($r) => $r->where('room_type_id', $roomTypeId)))
            ->orderBy('checkin_date', 'asc')
            ->get();

        $roomTypes = RoomType::orderBy('name')->get();

        return view('reports.reservations', compact('reservations', 'roomTypes', 'roomTypeId', 'start', 'end'));
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

        // Standardize keys using RoomStatus enum
        $roomStatuses = [];
        foreach (RoomStatus::cases() as $status) {
            $roomStatuses[$status->value] = $statusCounts[$status->value] ?? 0;
        }

        // Top room types by booking count in period
        $topRoomTypes = RoomType::select('room_types.id', 'room_types.name', DB::raw('count(reservations.id) as booking_count'))
            ->join('rooms', 'rooms.room_type_id', '=', 'room_types.id')
            ->join('reservations', 'reservations.room_id', '=', 'rooms.id')
            ->whereBetween('reservations.checkin_date', [$start, $end])
            ->where('reservations.status', '!=', ReservationStatus::Cancelled->value)
            ->groupBy('room_types.id', 'room_types.name')
            ->orderByDesc('booking_count')
            ->get();

        // Calculate occupancy rate using BillingService
        $occupancy = $this->billingService->calculateOccupancyRate($start, $end, $roomsCount);

        return view('reports.occupancy', compact(
            'roomStatuses', 'roomsCount', 'topRoomTypes', 'start', 'end'
        ))->with($occupancy);
    }

    public function fnb(Request $request)
    {
        [$start, $end] = $this->getDates($request);
        $startOfDay = Carbon::parse($start)->startOfDay();
        $endOfDay = Carbon::parse($end)->endOfDay();

        $orders = FnbOrder::with(['reservation.room', 'guest', 'items.foodItem'])
            ->whereBetween('order_time', [$startOfDay, $endOfDay])
            ->orderBy('order_time', 'desc')
            ->get();

        $totalRevenue = FnbOrder::where('status', FnbOrderStatus::Delivered->value)
            ->whereBetween('order_time', [$startOfDay, $endOfDay])
            ->sum('total_price');

        // Top menu items
        $topItems = FnbOrderItem::select('food_item_id', DB::raw('sum(qty) as total_qty'))
            ->whereHas('fnbOrder', function($q) use ($startOfDay, $endOfDay) {
                $q->where('status', FnbOrderStatus::Delivered->value)
                  ->whereBetween('order_time', [$startOfDay, $endOfDay]);
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

        $invoices = Invoice::whereHas('reservation', function($q) use ($start, $end) {
                $q->whereBetween('checkout_date', [$start, $end])
                  ->where('status', ReservationStatus::CheckedOut->value);
            })
            ->with(['reservation.room.roomType', 'reservation.details', 'reservation.charges.chargeType'])
            ->get();

        $totalRevenue = 0;
        $roomRevenue = 0;
        $extraBedRevenue = 0;
        $laundryRevenue = 0;
        $fnbRevenue = 0;
        $damageRevenue = 0;

        foreach ($invoices as $inv) {
            $totalRevenue += $inv->total_amount;
            
            $res = $inv->reservation;
            $nights = $res->nights; // Using the new accessor

            $roomRevenue += $res->room->roomType->base_price * $nights;

            // Extra bed from eagerly loaded details
            $extraBedDetail = $res->details->firstWhere('type', 'extra_bed');
            if ($extraBedDetail) {
                $extraBedRevenue += $extraBedDetail->qty * $extraBedDetail->price;
            }

            // Charges breakdown from eagerly loaded charges
            foreach ($res->charges as $charge) {
                if ($charge->chargeType) {
                    match ($charge->chargeType->code) {
                        'laundry' => $laundryRevenue += $charge->amount,
                        'fnb'     => $fnbRevenue += $charge->amount,
                        'damage'  => $damageRevenue += $charge->amount,
                        default   => null,
                    };
                }
            }
        }

        // Room Type Revenue breakdown
        $roomTypes = RoomType::all();
        $roomTypeRevenue = [];
        foreach ($roomTypes as $type) {
            $typeRev = 0;
            foreach ($invoices as $inv) {
                if ($inv->reservation->room->roomType->id === $type->id) {
                    $typeRev += $type->base_price * $inv->reservation->nights;
                }
            }
            $roomTypeRevenue[$type->name] = $typeRev;
        }

        return view('reports.revenue', compact('totalRevenue', 'roomRevenue', 'extraBedRevenue', 'laundryRevenue', 'fnbRevenue', 'damageRevenue', 'roomTypeRevenue', 'start', 'end'));
    }

    public function summary(Request $request)
    {
        [$start, $end] = $this->getDates($request);
        $startOfDay = Carbon::parse($start)->startOfDay();
        $endOfDay = Carbon::parse($end)->endOfDay();

        $reservationsCount = Reservation::whereBetween('checkin_date', [$start, $end])->count();

        $checkinsCount = Reservation::checkedIn()
            ->whereBetween('checkin_date', [$start, $end])
            ->count();

        $checkoutsCount = Reservation::where('status', ReservationStatus::CheckedOut->value)
            ->whereBetween('checkin_date', [$start, $end])
            ->count();

        $cancelledCount = Reservation::where('status', ReservationStatus::Cancelled->value)
            ->whereBetween('checkin_date', [$start, $end])
            ->count();

        $totalEarnings = Payment::where('status', 'success')
            ->whereBetween('payment_date', [$startOfDay, $endOfDay])
            ->sum('amount');

        $fnbCount = FnbOrder::whereBetween('order_time', [$startOfDay, $endOfDay])->count();

        $laundryCount = LaundryRequest::whereBetween('request_date', [$startOfDay, $endOfDay])->count();

        $chargesCount = Charge::whereBetween('created_at', [$startOfDay, $endOfDay])->count();

        $invoicePaid = Invoice::where('status', 'paid')
            ->whereHas('reservation', fn($q) => $q->notCancelled())
            ->whereBetween('invoice_date', [$start, $end])
            ->count();

        $invoiceUnpaid = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->whereHas('reservation', fn($q) => $q->notCancelled())
            ->whereBetween('invoice_date', [$start, $end])
            ->count();

        // Occupancy rate — using shared calculation from BillingService
        $roomsCount = Room::count();
        $occupancy = $this->billingService->calculateOccupancyRate($start, $end, $roomsCount);
        $occupancyRate = $occupancy['occupancyRate'];

        return view('reports.summary', compact(
            'reservationsCount', 'checkinsCount', 'checkoutsCount', 'cancelledCount',
            'totalEarnings', 'fnbCount', 'laundryCount', 'chargesCount',
            'invoicePaid', 'invoiceUnpaid', 'occupancyRate', 'start', 'end'
        ));
    }
}
