@extends('layouts.fo')

@section('header-title', 'Request Laundry Tamu')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
    <!-- Input Form (Left 1 Col) -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Input Laundry Baru</h3>
        
        <form method="POST" action="{{ route('services.laundry.store') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="reservation_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Kamar Tamu Aktif</label>
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
                <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Rincian Pakaian / Catatan</label>
                <textarea id="notes" name="notes" rows="4" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Contoh: 3 Kemeja (Cuci Setrika), 2 Celana Jeans (Dry Clean)">{{ old('notes') }}</textarea>
            </div>

            <div>
                <label for="total_charge" class="block text-sm font-semibold text-slate-700 mb-1.5">Total Biaya Laundry (Rp)</label>
                <input type="number" id="total_charge" name="total_charge" value="{{ old('total_charge', 0) }}" min="0" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-xs">
                Simpan Request Laundry
            </button>
        </form>
    </div>

    <!-- Active List (Right 2 Cols) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Daftar & Status Laundry Tamu</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="text-slate-400 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Kamar</th>
                        <th class="py-3 px-4">Tamu</th>
                        <th class="py-3 px-4">Rincian Pakaian</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Biaya</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    @forelse($laundryRequests as $req)
                        <tr>
                            <td class="py-4 px-4 font-bold">#{{ $req->reservation->room->room_number }}</td>
                            <td class="py-4 px-4 font-semibold">{{ $req->guest->full_name }}</td>
                            <td class="py-4 px-4 text-xs text-slate-500 max-w-xs truncate" title="{{ $req->notes }}">{{ $req->notes }}</td>
                            <td class="py-4 px-4">
                                @php
                                    $lBadge = 'bg-slate-100 text-slate-600';
                                    switch($req->status) {
                                        case 'requested': $lBadge = 'bg-slate-100 text-slate-700'; break;
                                        case 'picked_up': $lBadge = 'bg-indigo-50 text-indigo-700 border border-indigo-100'; break;
                                        case 'processing': $lBadge = 'bg-amber-50 text-amber-700 border border-amber-100'; break;
                                        case 'ready': $lBadge = 'bg-blue-50 text-blue-700 border border-blue-100'; break;
                                        case 'delivered': $lBadge = 'bg-emerald-50 text-emerald-700 border border-emerald-100'; break;
                                        case 'cancelled': $lBadge = 'bg-red-50 text-red-600 border border-red-100'; break;
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider {{ $lBadge }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-bold">Rp {{ number_format($req->total_charge, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-slate-500 text-xs">
                                <span class="block">FO: {{ $req->requester->name }}</span>
                                <span class="block text-slate-400">HK: {{ $req->handler ? $req->handler->name : '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada request laundry.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-50">
            {{ $laundryRequests->links() }}
        </div>
    </div>
</div>
@endsection
