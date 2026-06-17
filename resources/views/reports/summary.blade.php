@extends((auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.fo')

@section('header-title', 'Laporan Summary Operasional')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">
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

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- 1. Total Reservasi -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Reservasi Dibuat</span>
                <span class="text-3xl font-black text-slate-800 mt-2 block">{{ $reservationsCount }}</span>
            </div>
            <div class="p-3 bg-blue-50 text-blue-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <!-- 2. Total Checkins -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Check-In Aktif</span>
                <span class="text-3xl font-black text-slate-800 mt-2 block">{{ $checkinsCount }}</span>
            </div>
            <div class="p-3 bg-rose-50 text-rose-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- 3. Total Checkouts -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Checkout Selesai</span>
                <span class="text-3xl font-black text-slate-800 mt-2 block">{{ $checkoutsCount }}</span>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>

        <!-- 4. Occupancy Rate -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Occupancy Rate Hunian</span>
                <span class="text-3xl font-black text-amber-500 mt-2 block">{{ $occupancyRate }}%</span>
            </div>
            <div class="p-3 bg-amber-50 text-amber-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>

        <!-- 5. Total earnings -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Uang Diterima</span>
                <span class="text-2xl font-black text-emerald-600 mt-2 block">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- 6. Total Fnb orders -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Transaksi F&B</span>
                <span class="text-3xl font-black text-slate-800 mt-2 block">{{ $fnbCount }} Order</span>
            </div>
            <div class="p-3 bg-indigo-50 text-indigo-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
            </div>
        </div>

        <!-- 7. Total Laundry requests -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Order Laundry</span>
                <span class="text-3xl font-black text-slate-800 mt-2 block">{{ $laundryCount }} Request</span>
            </div>
            <div class="p-3 bg-violet-50 text-violet-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </div>
        </div>

        <!-- 8. Paid Invoice Count -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Invoice Paid (Lunas)</span>
                <span class="text-3xl font-black text-emerald-600 mt-2 block">{{ $invoicePaid }}</span>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- 9. Unpaid Invoice Count -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Invoice Belum Lunas</span>
                <span class="text-3xl font-black text-rose-500 mt-2 block">{{ $invoiceUnpaid }}</span>
            </div>
            <div class="p-3 bg-rose-50 text-rose-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <!-- 10. Cancelled Reservation Count -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Reservasi Batal</span>
                <span class="text-3xl font-black text-rose-600 mt-2 block">{{ $cancelledCount }}</span>
            </div>
            <div class="p-3 bg-red-50 text-red-500 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>
@endsection
