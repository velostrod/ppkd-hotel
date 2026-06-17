@extends('layouts.fo')

@section('header-title', 'Konfirmasi Check-In Tamu')

@section('content')
<div class="max-w-xl mx-auto bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
        <h3 class="text-base font-bold text-slate-800">Proses Masuk (Check-In)</h3>
        <a href="{{ route('reservations.show', $reservation->id) }}" class="text-slate-400 hover:text-slate-600 text-sm flex items-center font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Booking Summary Card -->
    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 mb-6 space-y-3">
        <div class="flex justify-between">
            <span class="text-xs text-slate-400 font-semibold uppercase">Kode Booking</span>
            <span class="text-sm font-bold text-slate-800">{{ $reservation->reservation_code }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-xs text-slate-400 font-semibold uppercase">Nama Tamu</span>
            <span class="text-sm font-bold text-slate-800">{{ $reservation->guest->full_name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-xs text-slate-400 font-semibold uppercase">Nomor Kamar</span>
            <span class="text-sm font-bold text-slate-800">Kamar #{{ $reservation->room->room_number }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-xs text-slate-400 font-semibold uppercase">Rencana Menginap</span>
            <span class="text-xs font-semibold text-slate-600">{{ $reservation->checkin_date->format('d/m/Y') }} s/d {{ $reservation->checkout_date->format('d/m/Y') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('checkins.store', $reservation->id) }}" class="space-y-6">
        @csrf

        <!-- Checkin Notes -->
        <div>
            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Khusus Check-In</label>
            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Misal: Deposit Rp 100.000, barang bawaan khusus, dll.">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-xs">
                Konfirmasi Check-In Tamu
            </button>
        </div>
    </form>
</div>
@endsection
