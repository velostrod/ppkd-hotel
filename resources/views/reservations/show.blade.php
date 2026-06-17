@extends('layouts.fo')

@section('header-title', 'Detail Reservasi')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Top Summary Banner -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="flex items-center space-x-3">
                <h3 class="text-xl font-bold text-slate-800">{{ $reservation->reservation_code }}</h3>
                @php
                    $badge = 'bg-slate-100 text-slate-600';
                    switch($reservation->status) {
                        case 'pending': $badge = 'bg-slate-100 text-slate-700'; break;
                        case 'confirmed': $badge = 'bg-blue-50 text-blue-700 border border-blue-100'; break;
                        case 'checked_in': $badge = 'bg-rose-50 text-rose-700 border border-rose-100'; break;
                        case 'checked_out': $badge = 'bg-emerald-50 text-emerald-700 border border-emerald-100'; break;
                        case 'cancelled': $badge = 'bg-red-50 text-red-600 border border-red-100'; break;
                    }
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                    {{ strtoupper($reservation->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest">Dibuat Oleh: {{ $reservation->creator->name }} pada {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-wrap items-center gap-3">
            @if($reservation->status === 'confirmed')
                <a href="{{ route('checkins.create', $reservation->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                    Check-In Tamu
                </a>
                
                <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 rounded-xl text-sm font-bold transition-colors uppercase">
                        Batalkan Reservasi
                    </button>
                </form>
            @endif

            @if($reservation->status === 'checked_in')
                <a href="{{ route('services.laundry') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold uppercase border border-slate-200">
                    + Laundry
                </a>
                <a href="{{ route('services.fnb') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold uppercase border border-slate-200">
                    + Order F&B
                </a>
                
                @if($latestInspection)
                    @if($latestInspection->status === 'pending')
                        <button type="button" disabled class="px-3.5 py-2 bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-xl text-xs font-bold uppercase cursor-not-allowed border border-slate-200 dark:border-slate-700">
                            Inspeksi Kamar (Pending)
                        </button>
                    @else
                        <button type="button" disabled class="px-3.5 py-2 bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-xl text-xs font-bold uppercase cursor-not-allowed border border-slate-200 dark:border-slate-700">
                            Inspeksi Kamar (Selesai)
                        </button>
                    @endif
                @else
                    <form action="{{ route('checkouts.request-inspection', $reservation->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-xs font-bold uppercase shadow-sm transition-colors">
                            Minta Room Inspection
                        </button>
                    </form>
                @endif

                <a href="{{ route('checkouts.invoice', $reservation->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                    Proses Checkout
                </a>
            @endif

            @if($reservation->status === 'checked_out' && $reservation->invoice)
                <a href="{{ route('checkouts.print', $reservation->id) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                    Cetak Invoice
                </a>
            @endif
            
            <a href="{{ route('reservations.index') }}" class="px-4 py-2 border border-slate-200 text-slate-500 rounded-xl text-sm font-bold transition-colors uppercase">
                Kembali
            </a>
        </div>
    </div>

    <!-- Main Content Details Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Col 1 & 2: General info & Bill statement -->
        <div class="lg:col-span-2 space-y-8">
            <!-- 1. Guest & Room Profiles -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Guest profile -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-50 pb-2">Profil Tamu</h4>
                    <p class="text-base font-bold text-slate-800">{{ $reservation->guest->full_name }}</p>
                    <p class="text-sm text-slate-500 mt-1">No. Identitas: {{ $reservation->guest->id_number }}</p>
                    <p class="text-sm text-slate-500">Telepon: {{ $reservation->guest->phone }}</p>
                    <p class="text-sm text-slate-500">Email: {{ $reservation->guest->email ?? '-' }}</p>
                    <p class="text-sm text-slate-500">Negara: {{ $reservation->guest->nationality }}</p>
                </div>

                <!-- Room / Dates -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-50 pb-2">Detail Kamar & Jadwal</h4>
                    <p class="text-base font-bold text-slate-800">Kamar #{{ $reservation->room->room_number }}</p>
                    <p class="text-sm text-slate-500 mt-1">Tipe: {{ $reservation->room->roomType->name }}</p>
                    <p class="text-sm text-slate-500">Check-In: <span class="font-semibold text-slate-700">{{ $reservation->checkin_date->format('d/m/Y') }}</span></p>
                    <p class="text-sm text-slate-500">Check-Out: <span class="font-semibold text-slate-700">{{ $reservation->checkout_date->format('d/m/Y') }}</span></p>
                    <p class="text-sm text-slate-500">Tamu: {{ $reservation->adults }} Dewasa, {{ $reservation->children }} Anak</p>
                </div>
            </div>

            <!-- 2. Additional Charges Statement -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-50 pb-2">Billing Charge Tambahan (Laundry, FnB, Kerusakan)</h4>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 font-semibold border-b border-slate-50">
                                <th class="pb-2">Deskripsi Layanan</th>
                                <th class="pb-2">Jenis Charge</th>
                                <th class="pb-2">Tanggal</th>
                                <th class="pb-2 text-right">Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($reservation->charges as $charge)
                                <tr>
                                    <td class="py-3 font-semibold text-slate-700">{{ $charge->description }}</td>
                                    <td class="py-3"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs uppercase">{{ $charge->chargeType->name }}</span></td>
                                    <td class="py-3 text-slate-400 text-xs">{{ $charge->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 text-right font-bold text-slate-700">Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada charge tambahan untuk reservasi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Payments Audit -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-50 pb-2">Riwayat Transaksi Pembayaran</h4>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 font-semibold border-b border-slate-50">
                                <th class="pb-2">Tanggal</th>
                                <th class="pb-2">Metode Pembayaran</th>
                                <th class="pb-2">Nomor Referensi</th>
                                <th class="pb-2">Petugas</th>
                                <th class="pb-2 text-right">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @if($reservation->invoice)
                                @forelse($reservation->invoice->payments as $payment)
                                    <tr>
                                        <td class="py-3 text-slate-400 text-xs">{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
                                        <td class="py-3 font-semibold text-slate-700">{{ $payment->paymentMethod->name }}</td>
                                        <td class="py-3 text-slate-500">{{ $payment->reference_number ?? '-' }}</td>
                                        <td class="py-3 text-slate-500">{{ $payment->creator->name }}</td>
                                        <td class="py-3 text-right font-bold text-emerald-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada pembayaran yang tercatat.</td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">Invoice belum diterbitkan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Col 3: Billing / Invoice Breakdown -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-slate-900 text-white p-6 rounded-2xl border border-slate-800 shadow-lg">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-800 pb-2">Ringkasan Tagihan (Billing)</h4>
                
                <div class="space-y-4 text-sm">
                    @php
                        $nights = $reservation->checkin_date->diffInDays($reservation->checkout_date);
                        $nights = $nights > 0 ? $nights : 1;
                        $roomPortion = $reservation->room->roomType->base_price * $nights;
                    @endphp
                    
                    <div class="flex justify-between">
                        <span class="text-slate-400">Sewa Kamar ({{ $nights }} malam)</span>
                        <span class="font-medium">Rp {{ number_format($roomPortion, 0, ',', '.') }}</span>
                    </div>

                    @foreach($reservation->details as $detail)
                        @if($detail->type !== 'special_request')
                            <div class="flex justify-between">
                                <span class="text-slate-400">{{ $detail->type === 'extra_bed' ? 'Extra Bed' : 'Breakfast' }}</span>
                                <span class="font-medium">Rp {{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach

                    @php
                        $additionalCharges = $reservation->charges()->sum('amount');
                    @endphp
                    @if($additionalCharges > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Charge Tambahan</span>
                            <span class="font-medium">Rp {{ number_format($additionalCharges, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-slate-400">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($reservation->discount, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t border-slate-800 my-2 pt-2 flex justify-between text-slate-400 text-xs">
                        <span>Service Charge</span>
                        <span>Rp {{ $reservation->invoice ? number_format($reservation->invoice->service_charge, 0, ',', '.') : number_format($reservation->service_charge, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-400 text-xs">
                        <span>Pajak</span>
                        <span>Rp {{ $reservation->invoice ? number_format($reservation->invoice->tax, 0, ',', '.') : number_format($reservation->tax, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t border-slate-800 pt-4 flex justify-between font-bold text-amber-500 text-base">
                        <span>Grand Total</span>
                        <span>Rp {{ $reservation->invoice ? number_format($reservation->invoice->total_amount, 0, ',', '.') : number_format($reservation->total, 0, ',', '.') }}</span>
                    </div>

                    @if($reservation->invoice)
                        <div class="border-t border-slate-800 pt-4 space-y-2 text-xs">
                            <div class="flex justify-between text-emerald-400 font-semibold">
                                <span>Sudah Dibayar</span>
                                <span>Rp {{ number_format($reservation->invoice->paid_amount, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between text-rose-400 font-bold">
                                <span>Sisa Tagihan</span>
                                <span>Rp {{ number_format($reservation->invoice->balance_due, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if($reservation->invoice)
                    <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                        <span class="text-xs uppercase tracking-widest text-slate-400 block mb-2">Status Pembayaran</span>
                        @php
                            $pBadge = 'bg-slate-800 text-slate-400';
                            switch($reservation->invoice->status) {
                                case 'unpaid': $pBadge = 'bg-rose-500/20 text-rose-400'; break;
                                case 'partial': $pBadge = 'bg-amber-500/20 text-amber-400'; break;
                                case 'paid': $pBadge = 'bg-emerald-500/20 text-emerald-400'; break;
                                case 'refunded': $pBadge = 'bg-purple-500/20 text-purple-400'; break;
                            }
                        @endphp
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $pBadge }}">
                            {{ $reservation->invoice->status }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Notes summary -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-50 pb-2">Catatan Booking</h4>
                @php
                    $noteDetail = $reservation->details()->where('type', 'special_request')->first();
                @endphp
                <p class="text-xs text-slate-600 italic">
                    {{ $noteDetail ? $noteDetail->notes : 'Tidak ada catatan khusus.' }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
