@extends((auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.fo')

@section('header-title', 'Laporan Occupancy Kamar')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Date Filter -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <form method="GET" class="filter-form">
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

    <!-- Occupancy stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Rate Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm md:col-span-1 flex flex-col justify-between">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Occupancy Rate</h4>
            
            <div class="text-center py-6">
                <span class="text-5xl font-black text-amber-500">{{ $occupancyRate }}%</span>
                <p class="text-xs text-slate-400 mt-2">Tingkat aktivitas hunian kamar hotel</p>
            </div>
            
            <div class="border-t border-slate-100 pt-4 text-xs text-slate-500 space-y-2">
                <div class="flex justify-between">
                    <span>Occupied Room Nights:</span>
                    <span class="font-semibold text-slate-700">{{ $occupiedRoomNights }} malam</span>
                </div>
                <div class="flex justify-between">
                    <span>Kapasitas Total Unit:</span>
                    <span class="font-semibold text-slate-700">{{ $totalRoomNights }} malam</span>
                </div>
            </div>
        </div>

        <!-- Room Status list -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm md:col-span-2">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Status Kamar Saat Ini (Total Unit: {{ $roomsCount }})</h4>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($roomStatuses as $status => $count)
                    @php
                        $colorClass = 'bg-slate-100 text-slate-700';
                        if($status === 'available') $colorClass = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                        elseif($status === 'occupied') $colorClass = 'bg-rose-50 text-rose-700 border border-rose-100';
                        elseif($status === 'reserved') $colorClass = 'bg-blue-50 text-blue-700 border border-blue-100';
                        elseif($status === 'dirty') $colorClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                    @endphp
                    <div class="p-4 rounded-xl border flex flex-col justify-between items-center text-center {{ $colorClass }}">
                        <span class="text-xs font-semibold uppercase tracking-wider">{{ str_replace('_', ' ', $status) }}</span>
                        <span class="text-2xl font-bold mt-2">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Room Types by Booking -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Tipe Kamar Favorit (Periode: {{ date('d/m/Y', strtotime($start)) }} - {{ date('d/m/Y', strtotime($end)) }})</h4>

        @if($topRoomTypes->isEmpty())
            <p class="text-sm text-slate-400 text-center py-4">Tidak ada data booking pada periode ini.</p>
        @else
            <div class="space-y-3">
                @php $maxBooking = $topRoomTypes->first()->booking_count; @endphp
                @foreach($topRoomTypes as $i => $type)
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-bold text-slate-400 w-5 text-right">{{ $i + 1 }}</span>
                        <div class="flex-1">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-semibold text-slate-700">{{ $type->name }}</span>
                                <span class="text-xs font-bold text-amber-600">{{ $type->booking_count }} booking</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-amber-400 h-2 rounded-full" style="width: {{ $maxBooking > 0 ? round($type->booking_count / $maxBooking * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
