@extends((auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.fo')

@section('header-title', 'Laporan Penjualan F&B')

@section('content')
<div class="space-y-4 md:space-y-6 max-w-6xl mx-auto">
    <!-- Date Filter -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-3 items-end">
            <div>
                <label for="start_date" class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Awal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $start }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 bg-white" />
            </div>
            <div>
                <label for="end_date" class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" value="{{ $end }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 bg-white" />
            </div>
            <div>
                <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold uppercase tracking-wider">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        <!-- Revenue & Top selling menu (Left 1 Col) -->
        <div class="md:col-span-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-4 md:gap-6">
            <!-- Revenue card -->
            <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm text-center flex flex-col justify-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Total Pendapatan FnB</span>
                <span class="text-xl sm:text-2xl lg:text-3xl font-black text-amber-500 break-words">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                <p class="text-[10px] text-slate-400 mt-2">Dihitung dari order berstatus DELIVERED saja</p>
            </div>

            <!-- Top Menu Items card -->
            <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">5 Menu Terlaris</h4>
                <div class="space-y-4">
                    @forelse($topItems as $top)
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-slate-700 block truncate">{{ $top->foodItem->name }}</span>
                                <span class="text-[10px] text-slate-400 block uppercase truncate">{{ $top->foodItem->foodCategory->name }}</span>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg shrink-0">{{ $top->total_qty }} Qty</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Belum ada menu terjual.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Orders log (Right 2 Cols) -->
        <div class="md:col-span-2 bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4 md:space-y-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Riwayat Pembelian Detail</h4>

            <!-- Mobile order cards -->
            <div class="block md:hidden space-y-3">
                @forelse($orders as $ord)
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">#{{ $ord->id }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ord->status === 'delivered' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $ord->status }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 font-semibold">Kamar #{{ $ord->reservation->room->room_number }}</div>
                        <div class="text-xs text-slate-500">
                            @foreach($ord->items as $it)
                                <span class="block">{{ $it->qty }}x {{ $it->foodItem->name }}</span>
                            @endforeach
                        </div>
                        <div class="text-right font-bold text-slate-800 pt-1 border-t border-slate-100">Rp {{ number_format($ord->total_price, 0, ',', '.') }}</div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic text-center py-4">Belum ada order FnB pada periode terpilih.</p>
                @endforelse
            </div>
            
            <!-- Desktop table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100">
                            <th class="pb-2 pr-4">Order ID</th>
                            <th class="pb-2 pr-4">Kamar</th>
                            <th class="pb-2 pr-4">Menu Items</th>
                            <th class="pb-2 pr-4">Status</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-slate-700">
                        @forelse($orders as $ord)
                            <tr>
                                <td class="py-3 pr-4 font-bold">#{{ $ord->id }}</td>
                                <td class="py-3 pr-4 font-semibold whitespace-nowrap">Kamar #{{ $ord->reservation->room->room_number }}</td>
                                <td class="py-3 pr-4 text-xs text-slate-500 max-w-xs truncate">
                                    @foreach($ord->items as $it)
                                        <span class="block">{{ $it->qty }}x {{ $it->foodItem->name }}</span>
                                    @endforeach
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ord->status === 'delivered' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $ord->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($ord->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada order FnB pada periode terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
