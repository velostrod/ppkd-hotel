@extends('layouts.hk')

@section('header-title', 'Papan Pengelolaan Tugas Housekeeping')

@section('content')
<div class="space-y-10 max-w-7xl mx-auto">
    <!-- Tasks split -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Cleaning & Maintenance Tickets (Left 7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Antrean Tugas Cleaning / Perbaikan</h3>
                
                <div class="space-y-4">
                    @forelse($pendingRequests as $req)
                        <div class="border border-slate-100 p-5 rounded-xl bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-base font-bold text-slate-800">Kamar #{{ $req->room->room_number }}</span>
                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-300 rounded text-[10px] font-bold uppercase tracking-wider">
                                        {{ str_replace('_', ' ', $req->request_type) }}
                                    </span>
                                    
                                    @php
                                        $pBadge = 'bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400';
                                        if($req->priority === 'high') $pBadge = 'bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400';
                                        elseif($req->priority === 'urgent') $pBadge = 'bg-rose-100 text-rose-800 font-bold dark:bg-rose-950/30 dark:text-rose-400';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $pBadge }}">
                                        {{ $req->priority }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2"><strong>Catatan:</strong> {{ $req->notes ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">Diminta pada: {{ $req->request_time->format('d/m H:i') }} | FO: {{ $req->requester->name }}</p>
                                
                                @if($req->assignee)
                                    <p class="text-xs text-indigo-600 font-semibold mt-2">Petugas: {{ $req->assignee->name }} ({{ strtoupper($req->status) }})</p>
                                @else
                                    <p class="text-xs text-rose-500 font-semibold mt-2">Belum ditugaskan</p>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-3">
                                @if($req->status === 'pending')
                                    <form action="{{ route('housekeeping.assign', $req->id) }}" method="POST" class="flex items-center space-x-2">
                                        @csrf
                                        <select name="assigned_to" required class="px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-amber-500 bg-white">
                                            <option value="">Tugaskan ke...</option>
                                            @foreach($hkStaff as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase">
                                            OK
                                        </button>
                                    </form>
                                @elseif($req->status === 'assigned')
                                    <form action="{{ route('housekeeping.start', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm">
                                            Mulai Kerja
                                        </button>
                                    </form>
                                @elseif($req->status === 'in_progress')
                                    <form action="{{ route('housekeeping.complete', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm">
                                            Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic text-center py-6">Tidak ada antrean tugas cleaning / pemeliharaan aktif.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Room Inspections & Laundry Queue (Right 5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Room Inspections Queue -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Antrean Inspeksi Kamar (Checkout)</h3>
                <div class="space-y-4">
                    @forelse($pendingInspections as $ins)
                        <div class="border border-slate-100 p-4 rounded-xl bg-slate-50/50 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800 text-sm block">Kamar #{{ $ins->room->room_number }}</span>
                                <span class="text-xs text-slate-400 block mt-0.5">Tamu: {{ $ins->reservation->guest->full_name }}</span>
                                <span class="text-[10px] text-slate-400 block">Diterima: {{ $ins->created_at->format('d/m H:i') }}</span>
                            </div>
                            <a href="{{ route('housekeeping.inspect-form', $ins->id) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider">
                                Inspeksi Kamar
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic text-center py-4">Tidak ada antrean inspeksi kamar.</p>
                    @endforelse
                </div>
            </div>

            <!-- Laundry requests HK queue -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Pekerjaan Laundry Tamu</h3>
                <div class="space-y-4">
                    @forelse($pendingLaundry as $laun)
                        <div class="border border-slate-100 p-4 rounded-xl bg-slate-50/50 flex flex-col justify-between gap-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-bold text-slate-800 text-sm block">Kamar #{{ $laun->reservation->room->room_number }}</span>
                                    <span class="text-xs text-slate-500 font-semibold">{{ $laun->guest->full_name }}</span>
                                </div>
                                @php
                                    $lBadge = 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-300';
                                    if(in_array($laun->status, ['picked_up', 'processing'])) $lBadge = 'bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400';
                                    elseif($laun->status === 'ready') $lBadge = 'bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400';
                                    elseif($laun->status === 'delivered') $lBadge = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400';
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $lBadge }}">
                                    {{ $laun->status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 italic">"{{ $laun->notes }}"</p>
                            
                            <!-- Action button depending on status -->
                            <form action="{{ route('housekeeping.laundry-update', $laun->id) }}" method="POST" class="flex justify-end space-x-2">
                                @csrf
                                @if($laun->status === 'requested')
                                    <input type="hidden" name="status" value="picked_up" />
                                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-lg uppercase">Ambil Laundry</button>
                                @elseif($laun->status === 'picked_up')
                                    <input type="hidden" name="status" value="processing" />
                                    <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase">Mulai Cuci</button>
                                @elseif($laun->status === 'processing')
                                    <input type="hidden" name="status" value="ready" />
                                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-lg uppercase">Selesai Cuci (Ready)</button>
                                @elseif($laun->status === 'ready')
                                    <input type="hidden" name="status" value="delivered" />
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs rounded-lg uppercase">Antar Ke Tamu</button>
                                @endif
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic text-center py-4">Tidak ada laundry yang perlu diproses.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Room Status Update Grid -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Update Cepat Status Kamar</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($rooms as $room)
                <div class="border border-slate-100 p-4 rounded-xl bg-slate-50/50 flex flex-col justify-between gap-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-800 text-sm">#{{ $room->room_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-200 text-slate-700">
                            {{ $room->status }}
                        </span>
                    </div>
                    <form action="{{ route('housekeeping.room-status', $room->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-amber-500 bg-white">
                            <option value="">Ubah Status...</option>
                            <option value="available" {{ $room->status === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="dirty" {{ $room->status === 'dirty' ? 'selected' : '' }}>Dirty</option>
                            <option value="cleaning" {{ $room->status === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                            <option value="inspected" {{ $room->status === 'inspected' ? 'selected' : '' }}>Inspected</option>
                            <option value="maintenance" {{ $room->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="out_of_order" {{ $room->status === 'out_of_order' ? 'selected' : '' }}>Out of Order</option>
                        </select>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
