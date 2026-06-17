@extends('layouts.fo')

@section('header-title', 'Rooms Status Board')

@section('content')
<div class="space-y-8">
    <!-- Status Stats Block -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl shadow-sm text-center">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-widest block">Available</span>
            <span class="text-2xl font-bold text-emerald-800 block mt-1">{{ $availableRoomsCount }}</span>
        </div>
        
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl shadow-sm text-center">
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-widest block">Reserved</span>
            <span class="text-2xl font-bold text-blue-800 block mt-1">{{ $reservedRoomsCount }}</span>
        </div>
        
        <div class="bg-rose-50 border border-rose-100 p-4 rounded-xl shadow-sm text-center">
            <span class="text-xs font-semibold text-rose-600 uppercase tracking-widest block">Occupied</span>
            <span class="text-2xl font-bold text-rose-800 block mt-1">{{ $occupiedRoomsCount }}</span>
        </div>
        
        <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl shadow-sm text-center">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-widest block">Dirty</span>
            <span class="text-2xl font-bold text-amber-800 block mt-1">{{ $dirtyRoomsCount }}</span>
        </div>
        
        <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl shadow-sm text-center">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest block">Lainnya</span>
            <span class="text-2xl font-bold text-slate-800 block mt-1">{{ $otherRoomsCount }}</span>
        </div>
    </div>

    <!-- Rooms Board Grid -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-6">Visual Peta Kamar</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($rooms as $room)
                @php
                    $activeReservation = $room->reservations->first();
                    $cardClass = 'bg-slate-50 border-slate-200';
                    $badgeClass = 'bg-slate-100 text-slate-700';
                    
                    switch($room->status) {
                        case 'available':
                            $cardClass = 'bg-emerald-50/50 border-emerald-200 hover:shadow-md hover:scale-[1.01]';
                            $badgeClass = 'bg-emerald-500 text-white';
                            break;
                        case 'reserved':
                            $cardClass = 'bg-blue-50/50 border-blue-200 hover:shadow-md hover:scale-[1.01]';
                            $badgeClass = 'bg-blue-500 text-white';
                            break;
                        case 'occupied':
                            $cardClass = 'bg-rose-50/50 border-rose-200 hover:shadow-md hover:scale-[1.01]';
                            $badgeClass = 'bg-rose-500 text-white';
                            break;
                        case 'dirty':
                            $cardClass = 'bg-amber-50/30 border-amber-200';
                            $badgeClass = 'bg-amber-600 text-white';
                            break;
                        case 'cleaning':
                            $cardClass = 'bg-amber-50/10 border-amber-200';
                            $badgeClass = 'bg-amber-500 text-slate-950';
                            break;
                        case 'inspected':
                            $cardClass = 'bg-teal-50/30 border-teal-200';
                            $badgeClass = 'bg-teal-500 text-white';
                            break;
                        case 'maintenance':
                            $cardClass = 'bg-indigo-50/30 border-indigo-200';
                            $badgeClass = 'bg-indigo-500 text-white';
                            break;
                        case 'out_of_order':
                            $cardClass = 'bg-red-50/30 border-red-200';
                            $badgeClass = 'bg-red-500 text-white';
                            break;
                    }
                @endphp
                
                <div class="border rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 {{ $cardClass }}">
                    <!-- Room Header info -->
                    <div>
                        <div class="flex justify-between items-start">
                            <span class="text-2xl font-bold tracking-tight text-slate-800">#{{ $room->room_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $badgeClass }}">
                                {{ strtoupper($room->status) }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wider">{{ $room->roomType->name }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Lantai {{ $room->floor }}</p>

                        <!-- Guest Info if Reserved/Occupied -->
                        @if(($room->status === 'reserved' || $room->status === 'occupied') && $activeReservation)
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Tamu Aktif</p>
                                <p class="text-xs font-bold text-slate-700 truncate mt-0.5">{{ $activeReservation->guest->full_name }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Code: <a href="{{ route('reservations.show', $activeReservation->id) }}" class="text-amber-600 font-semibold hover:underline">{{ $activeReservation->reservation_code }}</a></p>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="mt-6">
                        @if($room->status === 'available')
                            <a href="{{ route('reservations.create', ['room_id' => $room->id]) }}" class="block w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold text-center rounded-lg shadow-sm transition-colors uppercase tracking-wider">
                                Booking Kamar
                            </a>
                        @elseif($room->status === 'reserved' && $activeReservation)
                            <a href="{{ route('checkins.create', $activeReservation->id) }}" class="block w-full py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold text-center rounded-lg shadow-sm transition-colors uppercase tracking-wider">
                                Check-In
                            </a>
                        @elseif($room->status === 'occupied' && $activeReservation)
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('reservations.show', $activeReservation->id) }}" class="py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-bold text-center rounded-lg transition-colors uppercase">
                                    Detail
                                </a>
                                <a href="{{ route('checkouts.invoice', $activeReservation->id) }}" class="py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-[10px] font-bold text-center rounded-lg transition-colors uppercase">
                                    Checkout
                                </a>
                            </div>
                        @else
                            <button disabled class="w-full py-1.5 bg-slate-200 text-slate-400 text-xs font-semibold text-center rounded-lg uppercase tracking-wider cursor-not-allowed">
                                Operasional HK
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
