@extends('layouts.admin')

@section('header-title', 'Dashboard Administrator')

@section('content')
<div class="space-y-8">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Available Rooms -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center hover:scale-[1.02] transition-transform duration-300">
            <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kamar Tersedia</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $availableRooms }} <span class="text-xs text-slate-400 font-normal">/ {{ $roomsCount }} Kamar</span></h3>
            </div>
        </div>

        <!-- Card 2: Active Occupied -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center hover:scale-[1.02] transition-transform duration-300">
            <div class="p-3 bg-amber-50 text-amber-500 rounded-xl mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kamar Terisi (Occupied)</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $occupiedRooms }} <span class="text-xs text-slate-400 font-normal">aktif</span></h3>
            </div>
        </div>

        <!-- Card 3: Active Guests -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center hover:scale-[1.02] transition-transform duration-300">
            <div class="p-3 bg-indigo-50 text-indigo-500 rounded-xl mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tamu Menginap</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $activeGuestsCount }} <span class="text-xs text-slate-400 font-normal">orang</span></h3>
            </div>
        </div>

        <!-- Card 4: Daily Revenue -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center hover:scale-[1.02] transition-transform duration-300">
            <div class="p-3 bg-rose-50 text-rose-500 rounded-xl mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pendapatan Hari Ini</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Details Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Room Status Summary -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-1">
            <h3 class="text-base font-bold text-slate-800 mb-6">Status Kamar Real-time</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Kamar Kotor (Dirty)</span>
                        <span class="font-semibold">{{ $dirtyRooms }}</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-red-500 h-full" style="width: {{ $roomsCount > 0 ? ($dirtyRooms / $roomsCount) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Tersedia (Available)</span>
                        <span class="font-semibold">{{ $availableRooms }}</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full" style="width: {{ $roomsCount > 0 ? ($availableRooms / $roomsCount) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Terisi (Occupied)</span>
                        <span class="font-semibold">{{ $occupiedRooms }}</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-amber-500 h-full" style="width: {{ $roomsCount > 0 ? ($occupiedRooms / $roomsCount) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 border-t border-slate-100 pt-6">
                <a href="{{ route('admin.rooms') }}" class="text-xs text-amber-500 hover:text-amber-600 font-bold uppercase tracking-wider flex items-center">
                    Lihat Semua Kamar
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Audit Trail / Recent Activity Logs -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-2">
            <h3 class="text-base font-bold text-slate-800 mb-6">Log Aktivitas Staf Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-slate-400 font-medium border-b border-slate-100">
                            <th class="pb-3">Waktu</th>
                            <th class="pb-3">Staf</th>
                            <th class="pb-3">Modul</th>
                            <th class="pb-3">Aksi</th>
                            <th class="pb-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($recentLogs as $log)
                            <tr>
                                <td class="py-3 text-slate-400 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 font-semibold text-slate-700">{{ $log->user ? $log->user->name : 'System' }}</td>
                                <td class="py-3"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs uppercase">{{ $log->module }}</span></td>
                                <td class="py-3"><span class="font-medium text-slate-700 uppercase text-xs">{{ $log->action }}</span></td>
                                <td class="py-3 text-slate-500 text-xs truncate max-w-xs" title="{{ $log->description }}">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada aktivitas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
