@extends((auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.fo')

@section('header-title', 'Laporan Pendapatan Keuangan')

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

    <!-- Revenue Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Grand Total Card -->
        <div class="bg-slate-900 text-white p-6 rounded-2xl border border-slate-800 shadow-lg flex flex-col justify-between md:col-span-1">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Total Finansial Revenue</h4>
            <div class="py-4">
                <span class="text-4xl font-black text-amber-500">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                <p class="text-xs text-slate-400 mt-2">Total pendapatan bersih dari billing tamu checkout.</p>
            </div>
        </div>

        <!-- Sources Breakdown -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm md:col-span-2 space-y-4">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Rincian Sumber Pendapatan</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-500 font-medium">Sewa Kamar:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-500 font-medium">Extra Bed:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($extraBedRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-500 font-medium">Laundry:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($laundryRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-500 font-medium">F&B Service:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($fnbRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 md:col-span-2">
                    <span class="text-slate-500 font-medium">Denda Kerusakan Kamar:</span>
                    <span class="font-bold text-rose-600">Rp {{ number_format($damageRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Type Revenue Breakdown -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Pendapatan Berdasarkan Kategori Kamar</h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($roomTypeRevenue as $typeName => $amount)
                <div class="p-5 bg-slate-50/50 border border-slate-100 rounded-xl flex flex-col justify-between items-center text-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ $typeName }}</span>
                    <span class="text-lg font-black text-slate-800 mt-3">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
