@extends('layouts.fnb')

@section('header-title', 'Riwayat Pesanan Food & Beverage')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6 max-w-5xl mx-auto">
    <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Histori Pesanan Selesai / Batal</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Order ID</th>
                    <th class="py-3 px-4">Kamar</th>
                    <th class="py-3 px-4">Tamu</th>
                    <th class="py-3 px-4">Pesanan Items</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Total Harga</th>
                    <th class="py-3 px-4">Waktu Selesai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @forelse($orders as $ord)
                    <tr>
                        <td class="py-4 px-4 font-bold">#{{ $ord->id }}</td>
                        <td class="py-4 px-4 font-bold">Kamar #{{ $ord->reservation->room->room_number }}</td>
                        <td class="py-4 px-4 text-slate-500 font-semibold">{{ $ord->guest->full_name }}</td>
                        <td class="py-4 px-4">
                            <div class="space-y-0.5">
                                @foreach($ord->items as $it)
                                    <span class="block text-xs text-slate-600 font-medium">{{ $it->qty }}x {{ $it->foodItem->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $ord->status === 'delivered' ? '!bg-emerald-500 !text-white' : '!bg-red-500 !text-white' }}">
                                {{ $ord->status }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right font-bold">Rp {{ number_format($ord->total_price, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-slate-400 text-xs">{{ $ord->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pt-4 border-t border-slate-50">
        {{ $orders->links() }}
    </div>
</div>
@endsection
