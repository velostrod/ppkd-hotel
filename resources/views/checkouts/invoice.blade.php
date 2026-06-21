@extends('layouts.fo')

@section('header-title', 'Billing & Checkout Invoice')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    {{-- Inspection Warning / Status --}}
    @if(!$inspection)
        <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-800 rounded-r-lg flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Inspeksi Kamar belum diminta. Harap minta Housekeeping melakukan inspeksi sebelum checkout.</span>
            </div>
            <form action="{{ route('checkouts.request-inspection', $reservation->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs uppercase transition-colors">
                    Kirim Permintaan Inspeksi
                </button>
            </form>
        </div>
    @elseif($inspection->status === 'pending')
        <div class="p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-800 rounded-r-lg flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><strong>Sedang Berlangsung:</strong> Housekeeping sedang melakukan inspeksi kondisi kamar untuk memeriksa kerusakan/kehilangan barang.</span>
        </div>
    @else
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong>Inspeksi Selesai:</strong> Kondisi kamar dinyatakan <strong>{{ strtoupper($inspection->room_condition) }}</strong>.</span>
            </div>
            @if($inspection->damage_found)
                <p class="text-xs text-rose-700 font-bold mt-2 pl-8">Ditemukan kerusakan/kehilangan dengan total charge Rp {{ number_format($inspection->damage_cost, 0, ',', '.') }} (Telah ditambahkan otomatis ke billing di bawah). Catatan: {{ $inspection->notes }}</p>
            @endif
        </div>
    @endif

    {{-- Early Checkout Alert --}}
    @if($billing['isEarlyCheckout'])
        <div class="p-4 bg-purple-50 border-l-4 border-purple-500 text-purple-800 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong>Perhatian:</strong> {{ $billing['earlyCheckoutMsg'] }}</span>
            </div>
            <p class="text-xs text-purple-700 font-medium mt-2 pl-8">Invoice tagihan otomatis dihitung ulang sesuai dengan aturan Early Checkout.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Billing Details / Invoice Items --}}
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-800">Rincian Invoice Tamu</h3>
                    <a href="{{ route('reservations.show', $reservation->id) }}" class="text-slate-400 hover:text-slate-600 text-sm flex items-center font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali Ke Reservasi
                    </a>
                </div>

                {{-- Itemized Invoice Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 font-semibold border-b border-slate-100">
                                <th class="pb-2">Rincian Layanan / Kamar</th>
                                <th class="pb-2">Jumlah (Qty/Nights)</th>
                                <th class="pb-2">Harga Satuan (Rp)</th>
                                <th class="pb-2 text-right">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-slate-700">
                            {{-- Room base charge --}}
                            @php
                                $nights = $reservation->checkin_date->diffInDays($reservation->checkout_date);
                                $nights = $nights > 0 ? $nights : 1;
                                $roomBase = $reservation->room->roomType->base_price;
                            @endphp
                            <tr>
                                <td class="py-3">
                                    <span class="font-bold text-slate-800">Sewa Kamar #{{ $reservation->room->room_number }}</span>
                                    <span class="block text-xs text-slate-400">Tipe: {{ $reservation->room->roomType->name }}</span>
                                </td>
                                <td class="py-3">{{ $nights }} Malam</td>
                                <td class="py-3">Rp {{ number_format($roomBase, 0, ',', '.') }}</td>
                                <td class="py-3 text-right font-bold">Rp {{ number_format($roomBase * $nights, 0, ',', '.') }}</td>
                            </tr>

                            {{-- Add-on Charges: Extra Bed, Breakfast, FnB, Laundry --}}
                            @php
                                $addonCodes = ['fnb', 'laundry'];
                                $addonCharges = $reservation->charges->filter(fn($c) => in_array($c->chargeType->code, $addonCodes));
                                $damageCharges = $reservation->charges->filter(fn($c) => !in_array($c->chargeType->code, $addonCodes));
                                $detailAddons = $reservation->details->where('type', '!=', 'special_request');
                                $hasAddonSection = $detailAddons->count() > 0 || $addonCharges->count() > 0;
                            @endphp
                            @if($hasAddonSection)
                                <tr class="bg-slate-50">
                                    <td colspan="4" class="py-2 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Add-on Charges</td>
                                </tr>
                                @foreach($detailAddons as $detail)
                                    <tr>
                                        <td class="py-3 pl-6 font-semibold text-slate-800">
                                            {{ $detail->type === 'extra_bed' ? 'Addon Extra Bed' : 'Addon Breakfast' }}
                                            @if($detail->type === 'breakfast')
                                                <span class="block text-xs text-slate-400 font-normal">({{ $detail->qty / max(1, $reservation->adults) }} Hari &times; {{ $reservation->adults }} Pax)</span>
                                            @endif
                                        </td>
                                        <td class="py-3">{{ $detail->qty }}</td>
                                        <td class="py-3">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                        <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($addonCharges as $charge)
                                    <tr>
                                        <td class="py-3 pl-6">
                                            <span class="font-semibold text-slate-800">{{ $charge->description }}</span>
                                            <span class="block text-xs text-slate-400">{{ $charge->chargeType->name }}</span>
                                        </td>
                                        <td class="py-3">1</td>
                                        <td class="py-3">Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                                        <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif

                            {{-- Additional Charges: damage/loss only --}}
                            @if($damageCharges->count() > 0)
                                <tr class="bg-slate-50">
                                    <td colspan="4" class="py-2 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Additional Charges</td>
                                </tr>
                                @foreach($damageCharges as $charge)
                                    <tr>
                                        <td class="py-3 pl-6">
                                            <span class="font-semibold text-slate-800">{{ $charge->description }}</span>
                                            <span class="block text-xs text-slate-400">{{ $charge->chargeType->name }}</span>
                                        </td>
                                        <td class="py-3">1</td>
                                        <td class="py-3">Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                                        <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif

                            {{-- Early Checkout Penalty --}}
                            @if($billing['penaltyAmount'] > 0)
                                <tr>
                                    <td class="py-3">
                                        <span class="font-bold text-slate-800">{{ $billing['penaltyDesc'] }}</span>
                                    </td>
                                    <td class="py-3">1</td>
                                    <td class="py-3">Rp {{ number_format($billing['penaltyAmount'], 0, ',', '.') }}</td>
                                    <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format($billing['penaltyAmount'], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Invoice Totals breakdown --}}
                <div class="border-t border-slate-100 pt-6 flex justify-end">
                    <div class="w-64 space-y-3 text-sm text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($billing['itemsSubtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Diskon</span>
                            <span class="font-semibold text-slate-800">- Rp {{ number_format($billing['discount'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Service Charge (5%)</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($billing['serviceCharge'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pajak (10%)</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($billing['tax'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-slate-800 font-bold text-base">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format($billing['grandTotal'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Split Payments Input — only when guest still owes after deposit absorption --}}
            @if($reservation->invoice && $billing['balance'] > 0)
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                    <h3 class="text-base font-bold text-slate-800">Catat Pembayaran (Split / Full)</h3>
                    
                    <form method="POST" action="{{ route('checkouts.payment', $reservation->id) }}" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                        @csrf
                        <div>
                            <label for="payment_method_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Metode Pembayaran</label>
                            <select id="payment_method_id" name="payment_method_id" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Bayar (Rp)</label>
                            <input type="number" id="amount" name="amount" value="{{ old('amount', $billing['balance']) }}" max="{{ $billing['balance'] }}" min="1" required class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
                        </div>

                        <div>
                            <label for="reference_number" class="block text-sm font-semibold text-slate-700 mb-1.5">No Referensi / Transaksi</label>
                            <input type="text" id="reference_number" name="reference_number" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Opsional (Transfer ref, dll)" />
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Pembayaran</label>
                            <input type="text" id="notes" name="notes" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Opsional" />
                        </div>

                        <div>
                            <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold uppercase transition-colors">
                                Catat Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Checkout Action Panel (Right Side) --}}
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Konfirmasi Checkout</h3>

                <div class="space-y-4 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-slate-500">Total Tagihan Kamar</span>
                        <span class="font-bold text-slate-800 whitespace-nowrap shrink-0">Rp {{ number_format($billing['grandTotal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3 text-emerald-600">
                        <span>Telah Dibayar (Kamar)</span>
                        <span class="font-bold whitespace-nowrap shrink-0">Rp {{ number_format($billing['roomPaid'], 0, ',', '.') }}</span>
                    </div>
                    @if($billing['balance'] < 0)
                        <div class="flex items-start justify-between gap-3 text-blue-600 border-t border-slate-100 pt-3 text-base font-bold">
                            <span>Total Refund</span>
                            <span class="whitespace-nowrap shrink-0">Rp {{ number_format(abs($billing['balance']), 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="flex items-start justify-between gap-3 text-rose-500 border-t border-slate-100 pt-3 text-base font-bold">
                            <span>Sisa Tagihan</span>
                            <span class="whitespace-nowrap shrink-0">Rp {{ number_format($billing['balance'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Deposit Info --}}
                @if($billing['depositHeld'] > 0)
                    <div class="border-t border-slate-100 pt-4 space-y-3 text-sm">
                        <p class="text-xs font-bold text-amber-500 uppercase tracking-wider">Deposit Jaminan</p>
                        <div class="flex items-start justify-between gap-3">
                            <span class="font-bold text-amber-500">Deposit Ditahan</span>
                            <span class="font-bold text-amber-500 whitespace-nowrap shrink-0">Rp {{ number_format($billing['depositHeld'], 0, ',', '.') }}</span>
                        </div>
                        @if($billing['depositShortfall'] > 0)
                            <div class="flex items-start justify-between gap-3 font-bold text-rose-500 border-t border-slate-100 pt-2">
                                <span>Total Kurang (Tamu Bayar)</span>
                                <span class="whitespace-nowrap shrink-0">Rp {{ number_format($billing['depositShortfall'], 0, ',', '.') }}</span>
                            </div>
                        @elseif($billing['balance'] <= 0 && $reservation->charges->count() > 0)
                            <div class="flex items-start justify-between gap-3 font-bold text-emerald-600 border-t border-slate-100 pt-2">
                                <span>Dapat Dikembalikan</span>
                                <span class="whitespace-nowrap shrink-0">Rp {{ number_format($billing['depositToReturn'], 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Charge tambahan sudah terserap kelebihan bayar kamar.</p>
                        @else
                            <div class="flex items-start justify-between gap-3 font-bold text-emerald-600 border-t border-slate-100 pt-2">
                                <span>Dapat Dikembalikan</span>
                                <span class="whitespace-nowrap shrink-0">Rp {{ number_format($billing['depositToReturn'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($reservation->invoice)
                    <div class="border-t border-slate-100 pt-4 text-center">
                        <span class="text-xs text-slate-400 block mb-2 uppercase tracking-wider">Status Invoice</span>
                        @php
                            $statusBadge = 'bg-slate-100 text-slate-500';
                            switch($reservation->invoice->status) {
                                case 'unpaid': $statusBadge = 'bg-rose-100 text-rose-600'; break;
                                case 'partial': $statusBadge = 'bg-amber-100 text-amber-600'; break;
                                case 'paid': $statusBadge = 'bg-emerald-100 text-emerald-700'; break;
                                case 'refunded': $statusBadge = 'bg-purple-100 text-purple-600'; break;
                            }
                        @endphp
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $statusBadge }}">
                            {{ strtoupper($reservation->invoice->status) }}
                        </span>
                    </div>
                @endif

                {{-- Complete Checkout Submit Form --}}
                <div class="pt-4 border-t border-slate-100">
                    <form method="POST" action="{{ route('checkouts.store', $reservation->id) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="checkout_notes" class="block text-xs font-bold text-slate-500 uppercase mb-2">Catatan Checkout</label>
                            <textarea id="checkout_notes" name="notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-amber-500" placeholder="Review checkout..."></textarea>
                        </div>

                        @php
                            $isPaid = $reservation->invoice && $billing['balance'] <= 0;
                            $isInspected = $inspection && $inspection->status === 'completed';
                            $canCheckout = $isPaid && $isInspected;
                        @endphp

                        <button type="submit"
                                {{ !$canCheckout ? 'disabled' : '' }}
                                class="w-full py-3 text-center text-sm font-bold uppercase tracking-wider rounded-xl transition-all {{ $canCheckout ? 'bg-rose-500 hover:bg-rose-600 text-white shadow-lg active:scale-98 cursor-pointer' : 'bg-slate-300 text-slate-700 cursor-not-allowed border border-slate-400' }}">
                            Konfirmasi Checkout
                        </button>
                    </form>

                    @if(!$isPaid)
                        <p class="text-[10px] text-rose-400 text-center mt-3 font-semibold">Tagihan kamar harus dilunasi terlebih dahulu sebelum checkout.</p>
                    @endif
                    @if($inspection && !$isInspected)
                        <p class="text-[10px] text-amber-500 text-center mt-3 font-semibold">Menunggu pemeriksaan/inspeksi fisik kamar oleh Housekeeping.</p>
                    @endif
                </div>
            </div>

            {{-- Deposit Return Form --}}
            @if($reservation->invoice && $billing['depositHeld'] > 0)
                <div class="bg-white p-6 rounded-2xl border border-amber-100 shadow-sm space-y-4">
                    <h4 class="text-xs font-bold text-amber-600 uppercase tracking-widest border-b border-slate-100 pb-2">Kembalikan Deposit Jaminan</h4>
                    @if($reservation->invoice->deposit_status === 'returned')
                        <p class="text-xs text-emerald-600 font-semibold">Deposit telah dikembalikan penuh.</p>
                    @else
                        <form method="POST" action="{{ route('reservations.return-deposit', $reservation->id) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Pengembalian</label>
                                <select name="payment_method_id" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-amber-500">
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah Dikembalikan</label>
                                <input type="number" name="return_amount" value="{{ max(0, $billing['depositToReturn']) }}" min="0" max="{{ $billing['depositHeld'] - $billing['depositReturned'] }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-amber-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                                <input type="text" name="notes" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-amber-500" placeholder="Opsional" />
                            </div>
                            <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs uppercase transition-colors">
                                Catat Pengembalian Deposit
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Invoice Quick Print --}}
            @if($reservation->invoice)
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-center">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Invoice Print-Ready</h4>
                    <a href="{{ route('checkouts.print', $reservation->id) }}" target="_blank" class="block w-full py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg transition-colors uppercase tracking-wider">
                        Buka Print View
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
