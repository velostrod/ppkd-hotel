<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\HousekeepingRequest;
use App\Models\FnbOrder;
use App\Models\LaundryRequest;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isManager()) {
            return $this->adminDashboard();
        } elseif ($user->isFrontOffice()) {
            return $this->foDashboard();
        } elseif ($user->isHousekeeping()) {
            return redirect()->route('housekeeping.dashboard');
        } elseif ($user->isFnb()) {
            return redirect()->route('fnb.index');
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki role yang valid.');
    }

    private function adminDashboard()
    {
        $roomsCount = Room::count();
        $availableRooms = Room::available()->count();
        $occupiedRooms = Room::where('status', RoomStatus::Occupied->value)->count();
        $dirtyRooms = Room::where('status', RoomStatus::Dirty->value)->count();

        $rooms = Room::with(['roomType', 'reservations' => function ($q) {
            $q->whereIn('status', [ReservationStatus::Confirmed->value, ReservationStatus::CheckedIn->value])->with('guest');
        }])->orderBy('room_number')->get();

        $activeGuestsCount = Reservation::checkedIn()->count();
        
        $todayCheckins = Reservation::checkedIn()
            ->whereDate('checkin_date', today())
            ->count();
            
        $todayRevenue = Payment::where('status', 'success')
            ->whereDate('payment_date', today())
            ->sum('amount');

        $recentLogs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('dashboard.admin', compact(
            'roomsCount', 'availableRooms', 'occupiedRooms', 'dirtyRooms',
            'activeGuestsCount', 'todayCheckins', 'todayRevenue', 'recentLogs',
            'rooms'
        ));
    }

    private function foDashboard()
    {
        $rooms = Room::with(['roomType', 'reservations' => function($q) {
            $q->whereIn('status', [ReservationStatus::Confirmed->value, ReservationStatus::CheckedIn->value])->with('guest');
        }])->orderBy('room_number')->get();

        $availableRoomsCount = Room::available()->count();
        $occupiedRoomsCount = Room::where('status', RoomStatus::Occupied->value)->count();
        $reservedRoomsCount = Room::where('status', RoomStatus::Reserved->value)->count();
        $dirtyRoomsCount = Room::where('status', RoomStatus::Dirty->value)->count();
        $otherRoomsCount = Room::whereIn('status', [
            RoomStatus::Cleaning->value,
            RoomStatus::Inspected->value,
            RoomStatus::Maintenance->value,
            RoomStatus::OutOfOrder->value,
        ])->count();

        $recentBookings = Reservation::with(['guest', 'room'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.fo', compact(
            'rooms', 'availableRoomsCount', 'occupiedRoomsCount', 
            'reservedRoomsCount', 'dirtyRoomsCount', 'otherRoomsCount',
            'recentBookings'
        ));
    }
}
