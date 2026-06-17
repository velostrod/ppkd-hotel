<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\HousekeepingRequest;
use App\Models\FnbOrder;
use App\Models\LaundryRequest;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
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
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $dirtyRooms = Room::where('status', 'dirty')->count();
        
        $activeGuestsCount = Reservation::where('status', 'checked_in')->count();
        
        $todayCheckins = Reservation::where('status', 'checked_in')
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
            'activeGuestsCount', 'todayCheckins', 'todayRevenue', 'recentLogs'
        ));
    }

    private function foDashboard()
    {
        // Front Office wants to see rooms grid
        $rooms = Room::with(['roomType', 'reservations' => function($q) {
            $q->whereIn('status', ['confirmed', 'checked_in'])->with('guest');
        }])->orderBy('room_number')->get();
        $availableRoomsCount = Room::where('status', 'available')->count();
        $occupiedRoomsCount = Room::where('status', 'occupied')->count();
        $reservedRoomsCount = Room::where('status', 'reserved')->count();
        $dirtyRoomsCount = Room::where('status', 'dirty')->count();
        $otherRoomsCount = Room::whereIn('status', ['cleaning', 'inspected', 'maintenance', 'out_of_order'])->count();

        // Get reservations for visual indicators
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
