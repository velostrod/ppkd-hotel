@extends('layouts.hk')

@section('header-title', 'Riwayat Pembersihan Kamar')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6 max-w-6xl mx-auto">
    <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Histori Pekerjaan Selesai</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Kamar</th>
                    <th class="py-3 px-4">Tipe Request</th>
                    <th class="py-3 px-4">Prioritas</th>
                    <th class="py-3 px-4">Waktu Mulai/Minta</th>
                    <th class="py-3 px-4">Waktu Selesai</th>
                    <th class="py-3 px-4">Petugas</th>
                    <th class="py-3 px-4">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @forelse($cleanings as $clean)
                    <tr>
                        <td class="py-4 px-4 font-bold">#{{ $clean->room->room_number }}</td>
                        <td class="py-4 px-4 text-xs font-semibold uppercase tracking-wider">{{ str_replace('_', ' ', $clean->request_type) }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $clean->priority === 'urgent' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $clean->priority }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-slate-400 text-xs">{{ $clean->request_time->format('d/m/Y H:i') }}</td>
                        <td class="py-4 px-4 text-slate-400 text-xs">{{ $clean->completed_time ? $clean->completed_time->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-4 px-4 font-semibold text-slate-800">{{ $clean->assignee ? $clean->assignee->name : '-' }}</td>
                        <td class="py-4 px-4 text-slate-500 text-xs truncate max-w-xs" title="{{ $clean->notes }}">{{ $clean->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat pembersihan kamar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pt-4 border-t border-slate-50">
        {{ $cleanings->links() }}
    </div>
</div>
@endsection
