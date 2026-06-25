@extends('layouts.admin')

@section('header-title', 'Dashboard Administrator')

@section('content')
<div class="space-y-4 md:space-y-8">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Card 1: Available Rooms -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 md:gap-4 hover:scale-[1.02] transition-transform duration-300">
            <div class="p-2.5 md:p-3 bg-emerald-50 text-emerald-500 rounded-xl shrink-0">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kamar Tersedia</p>
                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mt-1 truncate">{{ $availableRooms }} <span class="text-xs text-slate-400 font-normal">/ {{ $roomsCount }} Kamar</span></h3>
            </div>
        </div>

        <!-- Card 2: Active Occupied -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 md:gap-4 hover:scale-[1.02] transition-transform duration-300">
            <div class="p-2.5 md:p-3 bg-amber-50 text-amber-500 rounded-xl shrink-0">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kamar Terisi (Occupied)</p>
                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mt-1 truncate">{{ $occupiedRooms }} <span class="text-xs text-slate-400 font-normal">aktif</span></h3>
            </div>
        </div>

        <!-- Card 3: Active Guests -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 md:gap-4 hover:scale-[1.02] transition-transform duration-300">
            <div class="p-2.5 md:p-3 bg-indigo-50 text-indigo-500 rounded-xl shrink-0">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tamu Menginap</p>
                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mt-1 truncate">{{ $activeGuestsCount }} <span class="text-xs text-slate-400 font-normal">orang</span></h3>
            </div>
        </div>

        <!-- Card 4: Daily Revenue -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 md:gap-4 hover:scale-[1.02] transition-transform duration-300">
            <div class="p-2.5 md:p-3 bg-rose-50 text-rose-500 rounded-xl shrink-0">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pendapatan Hari Ini</p>
                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mt-1 truncate">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Details Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Room Status Summary -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-1 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-4 md:mb-6">Status Kamar Real-time</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-500">Kamar Kotor (Dirty)</span>
                            <span class="font-semibold">{{ $dirtyRooms }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-red-500 h-full" style="width: {{ $roomsCount > 0 ? ($dirtyRooms / $roomsCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-500">Tersedia (Available)</span>
                            <span class="font-semibold">{{ $availableRooms }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full" style="width: {{ $roomsCount > 0 ? ($availableRooms / $roomsCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-500">Terisi (Occupied)</span>
                            <span class="font-semibold">{{ $occupiedRooms }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full" style="width: {{ $roomsCount > 0 ? ($occupiedRooms / $roomsCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 md:mt-8 border-t border-slate-100 pt-4 md:pt-6">
                <a href="{{ route('admin.rooms') }}" class="text-xs text-amber-500 hover:text-amber-600 font-bold uppercase tracking-wider flex items-center">
                    Lihat Semua Kamar
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Audit Trail / Recent Activity Logs -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-2">
            <h3 class="text-base font-bold text-slate-800 mb-4 md:mb-6">Log Aktivitas Staf Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-slate-400 font-medium border-b border-slate-100">
                            <th class="pb-3 pr-4">Waktu</th>
                            <th class="pb-3 pr-4">Staf</th>
                            <th class="pb-3 pr-4">Modul</th>
                            <th class="pb-3 pr-4">Aksi</th>
                            <th class="pb-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($recentLogs as $log)
                            <tr>
                                <td class="py-3 pr-4 text-slate-400 text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-4 font-semibold text-slate-700 whitespace-nowrap">{{ $log->user ? $log->user->name : 'System' }}</td>
                                <td class="py-3 pr-4"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs uppercase whitespace-nowrap">{{ $log->module }}</span></td>
                                <td class="py-3 pr-4"><span class="font-medium text-slate-700 uppercase text-xs whitespace-nowrap">{{ $log->action }}</span></td>
                                <td class="py-3 text-slate-500 text-xs truncate max-w-xs" title="{{ $log->description }}">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada aktivitas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Visual Peta Kamar -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-4 md:mb-6">Visual Peta Kamar</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
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
                            $cardClass = 'bg-amber-50/50 border-amber-200';
                            $badgeClass = 'bg-amber-500 text-white';
                            break;
                        case 'maintenance':
                        case 'out_of_order':
                            $cardClass = 'bg-red-50/50 border-red-200 opacity-75';
                            $badgeClass = 'bg-red-500 text-white';
                            break;
                    }
                @endphp

                <div class="border rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 {{ $cardClass }}">
                    <div>
                        <div class="flex justify-between items-start">
                            <span class="text-2xl font-bold tracking-tight text-slate-800">#{{ $room->room_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $badgeClass }}">
                                {{ strtoupper($room->status) }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wider">{{ $room->roomType->name }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Lantai {{ $room->floor }}</p>

                        {{-- Harga dasar --}}
                        <p class="text-[11px] font-semibold text-slate-600 mt-2">Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }} <span class="font-normal text-slate-400">/ malam</span></p>

                        {{-- Icon badges dengan CSS tooltip --}}
                        <div class="flex items-center gap-2 mt-2">
                            {{-- Kapasitas --}}
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Kapasitas: {{ $room->roomType->capacity }} orang
                                </span>
                            </div>

                            {{-- Breakfast --}}
                            @if($room->roomType->breakfast_included)
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Breakfast Included
                                </span>
                            </div>
                            @endif

                            {{-- Extra Bed --}}
                            @if($room->roomType->extra_bed_allowed)
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Extra Bed Tersedia
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Guest Info if Reserved/Occupied --}}
                        @if(($room->status === 'reserved' || $room->status === 'occupied') && $activeReservation)
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Tamu Aktif</p>
                                <p class="text-xs font-bold text-slate-700 truncate mt-0.5">{{ $activeReservation->guest->full_name }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Code: {{ $activeReservation->reservation_code }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        @if($room->status === 'available')
                            <a href="{{ route('reservations.create', ['room_id' => $room->id]) }}" class="block w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold text-center rounded-lg shadow-sm transition-colors uppercase tracking-wider">
                                Booking Kamar
                            </a>
                        @elseif($room->status === 'reserved' && $activeReservation)
                            @if(now()->startOfDay()->gte($activeReservation->checkin_date->startOfDay()))
                                <a href="{{ route('checkins.create', $activeReservation->id) }}" class="block w-full py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold text-center rounded-lg shadow-sm transition-colors uppercase tracking-wider">
                                    Check-In
                                </a>
                            @else
                                <button disabled class="w-full py-1.5 bg-blue-200 text-blue-400 text-xs font-semibold text-center rounded-lg uppercase tracking-wider cursor-not-allowed" title="Check-in mulai {{ $activeReservation->checkin_date->format('d/m/Y') }}">
                                    Check-In ({{ $activeReservation->checkin_date->format('d/m') }})
                                </button>
                            @endif
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
