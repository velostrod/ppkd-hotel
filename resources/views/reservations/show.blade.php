@extends('layouts.fo')

@section('header-title', 'Detail Reservasi')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Top Summary Banner -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <!-- Info row -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
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
            <a href="{{ route('reservations.index') }}" class="shrink-0 px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 rounded-xl text-sm font-bold transition-colors uppercase">
                Kembali
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-wrap items-center gap-2">
            @if($reservation->status === 'confirmed')
                @if(now()->startOfDay()->gte($reservation->checkin_date->startOfDay()))
                    <a href="{{ route('checkins.create', $reservation->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                        Check-In Tamu
                    </a>
                @else
                    <button disabled class="px-4 py-2 bg-blue-200 text-blue-400 rounded-xl text-sm font-bold cursor-not-allowed uppercase" title="Check-in tersedia mulai {{ $reservation->checkin_date->format('d/m/Y') }}">
                        Check-In Tamu
                    </button>
                @endif
                
                <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 rounded-xl text-sm font-bold transition-colors uppercase">
                        Batalkan Reservasi
                    </button>
                </form>
            @endif

            @if($reservation->status === 'checked_in')
                <a href="{{ route('services.laundry') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-bold uppercase border border-slate-200 transition-colors">
                    + Laundry
                </a>
                <a href="{{ route('services.fnb') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-bold uppercase border border-slate-200 transition-colors">
                    + Order F&B
                </a>

                @if($latestInspection)
                    @if($latestInspection->status === 'pending')
                        <button type="button" disabled class="px-4 py-2 bg-slate-200 text-slate-400 rounded-xl text-sm font-bold uppercase cursor-not-allowed border border-slate-200">
                            Inspeksi Kamar (Pending)
                        </button>
                    @else
                        <button type="button" disabled class="px-4 py-2 bg-slate-200 text-slate-400 rounded-xl text-sm font-bold uppercase cursor-not-allowed border border-slate-200">
                            Inspeksi Kamar (Selesai)
                        </button>
                    @endif
                @else
                    <form action="{{ route('checkouts.request-inspection', $reservation->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-sm font-bold uppercase shadow-sm transition-colors">
                            Minta Room Inspection
                        </button>
                    </form>
                @endif

                @if($latestInspection)
                    <a href="{{ route('checkouts.invoice', $reservation->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                        Proses Checkout
                    </a>
                @else
                    <button type="button" disabled class="px-4 py-2 bg-slate-200 text-slate-400 rounded-xl text-sm font-bold shadow-sm uppercase cursor-not-allowed border border-slate-200" title="Minta Room Inspection Terlebih Dahulu">
                        Proses Checkout
                    </button>
                @endif
            @endif

            @if($reservation->invoice)
                <a href="{{ route('checkouts.print', $reservation->id) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                    @if($reservation->invoice->status === 'paid')
                        Cetak Invoice
                    @elseif($latestInspection)
                        Cetak Invoice
                    @elseif($reservation->invoice->status === 'partial')
                        Cetak Bukti Pembayaran DP
                    @else
                        Cetak Tagihan Sementara
                    @endif
                </a>
            @endif
            
            @if(in_array($reservation->status, ['confirmed', 'checked_in']))
                <button type="button" onclick="openExtendModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-sm transition-colors uppercase">
                    Perpanjang Hari
                </button>
            @endif
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
                                <th class="pb-2">Catatan</th>
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
                                        <td class="py-3 text-slate-500 italic text-xs whitespace-pre-wrap">{{ $payment->notes ?? '-' }}</td>
                                        <td class="py-3 text-slate-500">{{ $payment->creator->name }}</td>
                                        <td class="py-3 text-right font-bold text-emerald-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-400">Belum ada pembayaran yang tercatat.</td>
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
            <div class="bg-white text-slate-700 p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-100 pb-2">Ringkasan Tagihan (Billing)</h4>
                
                <div class="space-y-4 text-sm">
                    @php
                        // $billing is passed from controller for checked_in status
                        $isPreCheckout = $reservation->status === 'checked_in' && $billing;

                        if ($isPreCheckout) {
                            $invSubtotal = $billing['itemsSubtotal'];
                            $invService = $billing['serviceCharge'];
                            $invTax = $billing['tax'];
                            $invTotal = $billing['grandTotal'];
                            $invPaid = $billing['roomPaid'];
                            $invDeposit = 0;
                            $invBalance = $billing['balance'];
                        } else {
                            $hasInvoice = $reservation->invoice !== null;
                            if ($hasInvoice) {
                                $invSubtotal = $reservation->invoice->subtotal;
                                $invService = $reservation->invoice->service_charge;
                                $invTax = $reservation->invoice->tax;
                                $invTotal = $reservation->invoice->total_amount;
                                $invPaid = $reservation->invoice->payments()->where('status','success')->where('type','room')->sum('amount');
                                $invDeposit = $reservation->invoice->deposit_amount ?? 0;
                                $invBalance = $invTotal - $invPaid - $invDeposit;
                            } else {
                                $invSubtotal = $reservation->subtotal + $reservation->charges()->sum('amount');
                                $invService = $reservation->service_charge;
                                $invTax = $reservation->tax;
                                $invTotal = $reservation->total;
                                $invPaid = 0;
                                $invDeposit = 0;
                                $invBalance = $invTotal;
                            }
                        }

                        // Display quantities
                        $nights = $reservation->checkin_date->diffInDays($reservation->checkout_date);
                        $nights = $nights > 0 ? $nights : 1;
                        if ($isPreCheckout && $billing['isEarlyCheckout']) {
                            $nights = $billing['effectiveNights'];
                        }

                        $roomPortion = $reservation->room->roomType->base_price * $nights;
                    @endphp
                    
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-slate-400">Sewa Kamar ({{ $nights }} malam)</span>
                        <span class="font-medium whitespace-nowrap shrink-0">Rp {{ number_format($roomPortion, 0, ',', '.') }}</span>
                    </div>

                    @foreach($reservation->details as $detail)
                        @if($detail->type !== 'special_request')
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-slate-400">
                                    {{ $detail->type === 'extra_bed' ? 'Extra Bed' : 'Breakfast' }}
                                    @if($detail->type === 'breakfast')
                                        ({{ $detail->qty / max(1, $reservation->adults) }} Hari &times; {{ $reservation->adults }} Pax)
                                    @else
                                        ({{ $detail->qty }}x)
                                    @endif
                                </span>
                                <span class="font-medium whitespace-nowrap shrink-0">Rp {{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach

                    @php
                        // Separate penalty charges from regular charges for display
                        $regularCharges = $reservation->charges()->whereDoesntHave('chargeType', function($q) {
                            $q->whereIn('name', ['Penalti Early Checkout', 'Penalti Deposit']);
                        })->sum('amount');

                        $penaltyCharges = $reservation->charges()->whereHas('chargeType', function($q) {
                            $q->whereIn('name', ['Penalti Early Checkout', 'Penalti Deposit']);
                        })->get();
                    @endphp
                    @if($regularCharges > 0)
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-400">Charge Tambahan</span>
                            <span class="font-medium whitespace-nowrap shrink-0">Rp {{ number_format($regularCharges, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @foreach($penaltyCharges as $pc)
                        <div class="flex items-start justify-between gap-3 font-bold">
                            <span class="text-amber-400">{{ $pc->description }}</span>
                            <span class="text-amber-400 whitespace-nowrap shrink-0">Rp {{ number_format($pc->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    @if($isPreCheckout && $billing['penaltyAmount'] > 0)
                        <div class="flex items-start justify-between gap-3 font-bold">
                            <span class="text-amber-400">{{ $billing['penaltyDesc'] }}</span>
                            <span class="text-amber-400 whitespace-nowrap shrink-0">Rp {{ number_format($billing['penaltyAmount'], 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-3 text-slate-400">
                        <span>Diskon</span>
                        <span class="whitespace-nowrap shrink-0">- Rp {{ number_format($reservation->discount, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t border-slate-100 my-2 pt-2 flex items-start justify-between gap-3 text-slate-400 text-xs">
                        <span>Service Charge</span>
                        <span class="whitespace-nowrap shrink-0">Rp {{ number_format($invService, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3 text-slate-400 text-xs">
                        <span>Pajak</span>
                        <span class="whitespace-nowrap shrink-0">Rp {{ number_format($invTax, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t border-slate-100 pt-4 flex items-start justify-between gap-3 font-bold text-amber-500 text-base">
                        <span>Grand Total</span>
                        <span class="whitespace-nowrap shrink-0">Rp {{ number_format($invTotal, 0, ',', '.') }}</span>
                    </div>

                    @if($reservation->invoice)
                        <div class="border-t border-slate-100 pt-4 space-y-2 text-xs">
                            <div class="flex items-start justify-between gap-3 text-emerald-400 font-semibold">
                                <span>Sudah Dibayar</span>
                                <span class="whitespace-nowrap shrink-0">Rp {{ number_format($invPaid, 0, ',', '.') }}</span>
                            </div>

                            @if(!$isPreCheckout && ($invDeposit ?? 0) > 0)
                                <div class="flex items-start justify-between gap-3 text-amber-400 font-semibold">
                                    <span>Deposit Jaminan</span>
                                    <span class="whitespace-nowrap shrink-0">Rp {{ number_format($invDeposit, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if($invBalance < 0)
                                <div class="flex items-start justify-between gap-3 text-blue-400 font-bold">
                                    <span>Total Refund</span>
                                    <span class="whitespace-nowrap shrink-0">Rp {{ number_format(abs($invBalance), 0, ',', '.') }}</span>
                                </div>
                            @else
                                <div class="flex items-start justify-between gap-3 text-rose-400 font-bold">
                                    <span>Sisa Tagihan</span>
                                    <span class="whitespace-nowrap shrink-0">Rp {{ number_format($invBalance, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @if($reservation->invoice)
                    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                        <span class="text-xs uppercase tracking-widest text-slate-400 block mb-2">Status Pembayaran</span>
                        @php
                            $pBadge = 'bg-slate-100 text-slate-500';
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
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 border-b border-slate-50 pb-2">Catatan Booking</h4>
                    @php
                        $noteDetail = $reservation->details()->where('type', 'special_request')->first();
                    @endphp
                    <p class="text-xs text-slate-600 italic">
                        {{ $noteDetail ? $noteDetail->notes : 'Tidak ada catatan khusus.' }}
                    </p>
                </div>

                @if($reservation->checkin && $reservation->checkin->notes)
                <div>
                    <h4 class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-2 border-b border-slate-50 pb-2">Catatan Check-In (Internal FO)</h4>
                    <p class="text-xs text-slate-600 italic bg-blue-50 p-3 rounded-lg border border-blue-100">
                        {{ $reservation->checkin->notes }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Extend Stay Modal -->
<div id="extendModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" onclick="closeExtendModal()"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
            <form action="{{ route('reservations.extend', $reservation->id) }}" method="POST">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full text-left">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3" id="modal-title">
                                Perpanjang Masa Menginap
                            </h3>
                            
                            <div class="mt-4 space-y-4">
                                <div>
                                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Kamar & Tipe</span>
                                    <span class="text-sm font-semibold text-slate-700">Kamar #{{ $reservation->room->room_number }} - {{ $reservation->room->roomType->name }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Check-in</span>
                                        <span class="text-sm font-semibold text-slate-700">{{ $reservation->checkin_date->format('d/m/Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Check-out Saat Ini</span>
                                        <span class="text-sm font-semibold text-slate-700">{{ $reservation->checkout_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label for="extend_nights" class="block text-sm font-semibold text-slate-700 mb-1.5">Tambahan Durasi (Malam) <span class="text-rose-500">*</span></label>
                                    <input type="number" id="extend_nights" name="extend_nights" value="1" min="1" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500" />
                                    <p class="text-xs text-slate-400 mt-1">Tarif kamar per malam: Rp {{ number_format($reservation->room->roomType->base_price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-500 text-sm font-bold text-slate-950 hover:bg-amber-400 focus:outline-none sm:ml-3 sm:w-auto uppercase tracking-wider">
                        Simpan Perpanjangan
                    </button>
                    <button type="button" onclick="closeExtendModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-200 shadow-sm px-4 py-2 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openExtendModal() {
        document.getElementById('extendModal').classList.remove('hidden');
    }
    function closeExtendModal() {
        document.getElementById('extendModal').classList.add('hidden');
    }
</script>
@endsection
