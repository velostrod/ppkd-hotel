@extends((auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.fo')

@section('header-title', 'Laporan Penjualan F&B')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Date Filter -->
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue & Top selling menu (Left 1 Col) -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Revenue card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Total Pendapatan FnB</span>
                <span class="text-3xl font-black text-amber-500">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                <p class="text-[10px] text-slate-400 mt-2">Dihitung dari order berstatus DELIVERED saja</p>
            </div>

            <!-- Top Menu Items card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">5 Menu Terlaris</h4>
                <div class="space-y-4">
                    @forelse($topItems as $top)
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-slate-700 block">{{ $top->foodItem->name }}</span>
                                <span class="text-[10px] text-slate-400 block uppercase">{{ $top->foodItem->foodCategory->name }}</span>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg">{{ $top->total_qty }} Qty</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Belum ada menu terjual.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Orders log (Right 2 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Riwayat Pembelian Detail</h4>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100">
                            <th class="pb-2">Order ID</th>
                            <th class="pb-2">Kamar</th>
                            <th class="pb-2">Menu Items</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-slate-700">
                        @forelse($orders as $ord)
                            <tr>
                                <td class="py-3 font-bold">#{{ $ord->id }}</td>
                                <td class="py-3 font-semibold">Kamar #{{ $ord->reservation->room->room_number }}</td>
                                <td class="py-3 text-xs text-slate-500 max-w-xs truncate">
                                    @foreach($ord->items as $it)
                                        <span class="block">{{ $it->qty }}x {{ $it->foodItem->name }}</span>
                                    @endforeach
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ord->status === 'delivered' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $ord->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format($ord->total_price, 0, ',', '.') }}</td>
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
