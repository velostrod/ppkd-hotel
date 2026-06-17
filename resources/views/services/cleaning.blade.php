@extends('layouts.fo')

@section('header-title', 'Housekeeping Service Request')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
    <!-- Input Form -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Kirim Permintaan Baru</h3>
        
        <form method="POST" action="{{ route('services.cleaning.store') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="reservation_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Kamar Tamu Aktif</label>
                <select id="reservation_id" name="reservation_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                    <option value="">-- Pilih Kamar --</option>
                    @foreach($activeReservations as $res)
                        <option value="{{ $res->id }}" {{ old('reservation_id') == $res->id ? 'selected' : '' }}>
                            Kamar #{{ $res->room->room_number }} - {{ $res->guest->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="request_type" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Permintaan (Tipe)</label>
                <select id="request_type" name="request_type" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                    <option value="stayover_cleaning">Stayover Cleaning (Pembersihan Harian)</option>
                    <option value="linen_replacement">Linen Replacement (Ganti Sprei/Handuk)</option>
                    <option value="deep_cleaning">Deep Cleaning (Menyeluruh)</option>
                    <option value="maintenance">Maintenance (Perbaikan Fasilitas)</option>
                </select>
            </div>

            <div>
                <label for="priority" class="block text-sm font-semibold text-slate-700 mb-1.5">Tingkat Prioritas</label>
                <select id="priority" name="priority" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                    <option value="low">Low (Rendah)</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">High (Tinggi)</option>
                    <option value="urgent">Urgent (Mendesak)</option>
                </select>
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Detail Instruksi</label>
                <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Misal: Bersihkan kamar setelah jam 13:00, ganti bohlam lampu kamar mandi, dll.">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-xs">
                Kirim Permintaan HK
            </button>
        </form>
    </div>

    <!-- Active List -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Riwayat Pembersihan Kamar</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="text-slate-400 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Kamar</th>
                        <th class="py-3 px-4">Tipe Request</th>
                        <th class="py-3 px-4">Prioritas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Waktu Request</th>
                        <th class="py-3 px-4">Petugas HK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    @forelse($cleaningRequests as $req)
                        <tr>
                            <td class="py-4 px-4 font-bold">#{{ $req->room->room_number }}</td>
                            <td class="py-4 px-4 font-semibold text-xs capitalize">{{ str_replace('_', ' ', $req->request_type) }}</td>
                            <td class="py-4 px-4">
                                @php
                                    $pColor = 'bg-slate-100 text-slate-700';
                                    switch($req->priority) {
                                        case 'low': $pColor = 'bg-slate-50 text-slate-500'; break;
                                        case 'normal': $pColor = 'bg-blue-50 text-blue-700'; break;
                                        case 'high': $pColor = 'bg-amber-50 text-amber-700'; break;
                                        case 'urgent': $pColor = 'bg-rose-50 text-rose-700 font-bold'; break;
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $pColor }}">
                                    {{ strtoupper($req->priority) }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $sBadge = 'bg-slate-100 text-slate-600';
                                    switch($req->status) {
                                        case 'pending': $sBadge = 'bg-slate-100 text-slate-700'; break;
                                        case 'assigned': $sBadge = 'bg-indigo-50 text-indigo-700 border border-indigo-100'; break;
                                        case 'in_progress': $sBadge = 'bg-amber-50 text-amber-700 border border-amber-100'; break;
                                        case 'completed': $sBadge = 'bg-emerald-50 text-emerald-700 border border-emerald-100'; break;
                                        case 'cancelled': $sBadge = 'bg-red-50 text-red-600 border border-red-100'; break;
                                    }
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $sBadge }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-slate-400 text-xs">{{ $req->request_time->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-4 text-slate-500 font-medium text-xs">{{ $req->assignee ? $req->assignee->name : 'Unassigned' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada request pembersihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-50">
            {{ $cleaningRequests->links() }}
        </div>
    </div>
</div>
@endsection
