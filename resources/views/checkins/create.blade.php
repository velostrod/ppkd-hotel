@extends('layouts.fo')

@section('header-title', 'Konfirmasi Check-In Tamu')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-800">Proses Check-In Tamu</h3>
        <a href="{{ route('reservations.show', $reservation->id) }}" class="text-slate-400 hover:text-slate-600 text-sm flex items-center font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Booking Summary Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-3">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest pb-2 border-b border-slate-50">Informasi Reservasi</h4>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase block">Kode Booking</span>
                <span class="font-bold text-slate-800">{{ $reservation->reservation_code }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase block">Nama Tamu</span>
                <span class="font-bold text-slate-800">{{ $reservation->guest->full_name }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase block">Nomor Kamar</span>
                <span class="font-bold text-slate-800">Kamar #{{ $reservation->room->room_number }} &mdash; {{ $reservation->room->roomType->name }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase block">Rencana Menginap</span>
                <span class="font-semibold text-slate-600 text-xs">{{ $reservation->checkin_date->format('d/m/Y') }} s/d {{ $reservation->checkout_date->format('d/m/Y') }} ({{ $reservation->nights }} Malam)</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('checkins.store', $reservation->id) }}" class="space-y-6">
        @csrf

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Section 1: Pembayaran Kamar -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                <h4 class="text-sm font-bold text-slate-700">Pembayaran Sisa Tagihan Kamar</h4>
                @if($roomBalance <= 0)
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-xs font-bold">Sudah Lunas ✓</span>
                @else
                    <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-100 rounded-full text-xs font-bold">Perlu Pelunasan</span>
                @endif
            </div>

            @if($reservation->invoice)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Total Tagihan Kamar</span>
                    <span class="font-semibold text-slate-700">Rp {{ number_format($reservation->invoice->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">DP / Uang Muka (saat booking)</span>
                    <span class="font-semibold text-emerald-600">- Rp {{ number_format($reservation->invoice->total_amount - $roomBalance, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold border-t border-slate-100 pt-3">
                    <span class="text-slate-700">Sisa yang Harus Dibayar</span>
                    <span class="{{ $roomBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($roomBalance, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($roomBalance > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="room_payment_method_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Metode Pembayaran Sisa Kamar <span class="text-rose-500">*</span></label>
                        <select id="room_payment_method_id" name="room_payment_method_id" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="">-- Pilih Metode --</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ old('room_payment_method_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <div class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 font-semibold">
                            Nominal: <span class="text-slate-800 font-bold">Rp {{ number_format($roomBalance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="room_payment_method_id" value="">
            @endif
        </div>

        <!-- Section 2: Deposit Jaminan -->
        <div class="bg-white p-6 rounded-2xl border border-amber-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                <h4 class="text-sm font-bold text-slate-700">Deposit Jaminan</h4>
                <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-100 rounded-full text-xs font-bold">Wajib</span>
            </div>
            <p class="text-xs text-slate-500">Deposit ini bersifat sebagai jaminan dan akan dikembalikan penuh saat checkout apabila tidak ada kerusakan atau charge tambahan.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="deposit_payment_method_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Metode Pembayaran Deposit <span class="text-rose-500">*</span></label>
                    <select id="deposit_payment_method_id" name="deposit_payment_method_id" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500">
                        <option value="">-- Pilih Metode --</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ old('deposit_payment_method_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="deposit_amount" class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Deposit (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', $depositRequired > 0 ? $depositRequired : '') }}" min="1" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Rp 0" />
                    @if($depositRequired > 0)
                        <p class="text-xs text-slate-400 mt-1">Nominal standar hotel: Rp {{ number_format($depositRequired, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Check-In (Opsional)</label>
            <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Catatan khusus tamu, kondisi kamar, dll.">{{ old('notes') }}</textarea>
        </div>

        <!-- Summary sebelum submit -->
        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-lg space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest pb-2 border-b border-slate-800">Ringkasan Pembayaran Check-In</h4>
            @if($roomBalance > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Pelunasan Sisa Kamar</span>
                    <span class="font-bold">Rp {{ number_format($roomBalance, 0, ',', '.') }}</span>
                </div>
            @else
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Tagihan Kamar</span>
                    <span class="font-bold text-emerald-400">Sudah Lunas ✓</span>
                </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Deposit Jaminan</span>
                <span class="font-bold text-amber-400" id="deposit-preview">Rp {{ $depositRequired > 0 ? number_format($depositRequired, 0, ',', '.') : '—' }}</span>
            </div>
            <div class="border-t border-slate-800 pt-3 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-xs">
                    Konfirmasi Check-In Tamu
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('deposit_amount').addEventListener('input', function() {
        const val = parseFloat(this.value) || 0;
        document.getElementById('deposit-preview').textContent = 'Rp ' + val.toLocaleString('id-ID');
    });
</script>
@endsection
