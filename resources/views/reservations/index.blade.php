@extends('layouts.fo')

@section('header-title', 'Reservasi Kamar')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h3 class="text-base font-bold text-slate-800">Riwayat & Daftar Booking</h3>
        
        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('reservations.index') }}" class="flex">
                <select name="status" onchange="this.form.submit()" class="mr-2 px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ request('status') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari Booking / Tamu..." 
                       class="px-4 py-2 border border-slate-200 rounded-l-xl text-sm focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500" />
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-r-xl text-sm font-semibold hover:bg-slate-700 transition-colors">
                    Cari
                </button>
            </form>
            
            <a href="{{ route('reservations.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-sm font-bold shadow-sm transition-colors uppercase tracking-wider">
                Buat Booking Baru
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Booking Number</th>
                    <th class="py-3 px-4">Tamu</th>
                    <th class="py-3 px-4">Kamar</th>
                    <th class="py-3 px-4">Check-In</th>
                    <th class="py-3 px-4">Check-Out</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Total</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($reservations as $res)
                    <tr>
                        <td class="py-4 px-4 font-bold text-slate-800">
                            <a href="{{ route('reservations.show', $res->id) }}" class="text-amber-600 hover:underline">
                                {{ $res->reservation_code }}
                            </a>
                        </td>
                        <td class="py-4 px-4 font-semibold text-slate-700">{{ $res->guest->full_name }}</td>
                        <td class="py-4 px-4 text-slate-500">
                            <span class="font-bold text-slate-700">#{{ $res->room->room_number }}</span>
                            <span class="text-xs text-slate-400">({{ $res->room->roomType->name }})</span>
                        </td>
                        <td class="py-4 px-4 text-slate-500">{{ $res->checkin_date->format('d/m/Y') }}</td>
                        <td class="py-4 px-4 text-slate-500">{{ $res->checkout_date->format('d/m/Y') }}</td>
                        <td class="py-4 px-4">
                            @php
                                $badge = 'bg-slate-100 text-slate-600';
                                switch($res->status) {
                                    case 'pending': $badge = 'bg-slate-100 text-slate-700'; break;
                                    case 'confirmed': $badge = 'bg-blue-50 text-blue-700 border border-blue-100'; break;
                                    case 'checked_in': $badge = 'bg-rose-50 text-rose-700 border border-rose-100'; break;
                                    case 'checked_out': $badge = 'bg-emerald-50 text-emerald-700 border border-emerald-100'; break;
                                    case 'cancelled': $badge = 'bg-red-50 text-red-600 border border-red-100'; break;
                                }
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                {{ strtoupper($res->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 font-bold text-slate-700">
                            @php
                                $displayTotal = $res->invoice ? $res->invoice->total_amount : $res->total;
                            @endphp
                            Rp {{ number_format($displayTotal, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right space-x-2">
                            <a href="{{ route('reservations.show', $res->id) }}" class="text-slate-800 hover:underline text-xs font-bold">Detail</a>
                            @if($res->status === 'confirmed')
                                @if(now()->startOfDay()->gte($res->checkin_date->startOfDay()))
                                    <a href="{{ route('checkins.create', $res->id) }}" class="text-blue-600 hover:underline text-xs font-bold">Check-In</a>
                                @else
                                    <span class="text-slate-400 text-xs font-bold cursor-not-allowed" title="Check-in tersedia mulai {{ $res->checkin_date->format('d/m/Y') }}">Check-In</span>
                                @endif
                            @elseif($res->status === 'checked_in')
                                <a href="{{ route('checkouts.invoice', $res->id) }}" class="text-rose-500 hover:underline text-xs font-bold">Checkout</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data reservasi ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pt-4 border-t border-slate-50">
        {{ $reservations->appends(request()->query())->links() }}
    </div>
</div>
@endsection
