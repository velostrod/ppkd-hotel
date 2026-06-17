@extends((auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.fo')

@section('header-title', 'Laporan Reservasi')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Date Filter Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div>
                <label for="start_date" class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Awal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $start }}" class="px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 bg-white" />
            </div>
            <div>
                <label for="end_date" class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" value="{{ $end }}" class="px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 bg-white" />
            </div>
            <div>
                <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold uppercase tracking-wider">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Report Table Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-700 uppercase">Periode: {{ date('d/m/Y', strtotime($start)) }} - {{ date('d/m/Y', strtotime($end)) }}</h3>
            <span class="text-xs text-slate-400 font-semibold uppercase">Total: {{ $reservations->count() }} Booking</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-slate-400 font-semibold border-b border-slate-100">
                        <th class="pb-2">Booking Code</th>
                        <th class="pb-2">Tamu</th>
                        <th class="pb-2">Kamar</th>
                        <th class="pb-2">Check-in</th>
                        <th class="pb-2">Check-out</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    @forelse($reservations as $res)
                        <tr>
                            <td class="py-3 font-bold text-slate-800">{{ $res->reservation_code }}</td>
                            <td class="py-3 font-semibold text-slate-700">{{ $res->guest->full_name }}</td>
                            <td class="py-3">Kamar #{{ $res->room->room_number }} ({{ $res->room->roomType->name }})</td>
                            <td class="py-3 text-slate-500 text-xs">{{ $res->checkin_date->format('d/m/Y') }}</td>
                            <td class="py-3 text-slate-500 text-xs">{{ $res->checkout_date->format('d/m/Y') }}</td>
                            <td class="py-3">
                                @php
                                    $rB = 'bg-slate-100 text-slate-600';
                                    switch($res->status) {
                                        case 'pending': $rB = 'bg-slate-100 text-slate-700'; break;
                                        case 'confirmed': $rB = 'bg-blue-50 text-blue-700'; break;
                                        case 'checked_in': $rB = 'bg-rose-50 text-rose-700'; break;
                                        case 'checked_out': $rB = 'bg-emerald-50 text-emerald-700'; break;
                                        case 'cancelled': $rB = 'bg-red-50 text-red-600'; break;
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase {{ $rB }}">
                                    {{ $res->status }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format($res->invoice ? $res->invoice->total_amount : $res->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Tidak ada reservasi pada periode terpilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
