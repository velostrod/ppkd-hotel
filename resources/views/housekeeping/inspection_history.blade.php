@extends('layouts.hk')

@section('header-title', 'Riwayat Inspeksi Kamar')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6 max-w-6xl mx-auto">
    <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Histori Pemeriksaan Checkout</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Kamar</th>
                    <th class="py-3 px-4">Tanggal Inspeksi</th>
                    <th class="py-3 px-4">Petugas Inspeksi</th>
                    <th class="py-3 px-4">Kondisi Kamar</th>
                    <th class="py-3 px-4">Kerusakan/Kehilangan?</th>
                    <th class="py-3 px-4 text-right">Biaya Kerusakan</th>
                    <th class="py-3 px-4">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @forelse($inspections as $ins)
                    <tr>
                        <td class="py-4 px-4 font-bold">#{{ $ins->room->room_number }}</td>
                        <td class="py-4 px-4 text-slate-400 text-xs">{{ $ins->inspection_date->format('d/m/Y H:i') }}</td>
                        <td class="py-4 px-4 font-semibold text-slate-800">{{ $ins->inspector->name }}</td>
                        <td class="py-4 px-4">
                            @php
                                $cBadge = 'bg-slate-100 text-slate-700';
                                if($ins->room_condition === 'good') $cBadge = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                                elseif($ins->room_condition === 'damaged') $cBadge = 'bg-rose-50 text-rose-700 border border-rose-100';
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $cBadge }}">
                                {{ strtoupper($ins->room_condition) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $ins->damage_found ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $ins->damage_found ? 'YA' : 'TIDAK' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right font-bold text-slate-800">Rp {{ number_format($ins->damage_cost, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-slate-500 text-xs truncate max-w-xs" title="{{ $ins->notes }}">{{ $ins->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat inspeksi kamar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pt-4 border-t border-slate-50">
        {{ $inspections->links() }}
    </div>
</div>
@endsection
