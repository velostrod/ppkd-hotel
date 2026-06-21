<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $reservation->reservation_code }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #333;
            margin: 40px;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            vertical-align: top;
            padding: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            border-bottom: 1px solid #333;
            border-top: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px dashed #ddd;
        }
        .total-box {
            float: right;
            width: 300px;
            margin-bottom: 30px;
        }
        .total-box table {
            width: 100%;
        }
        .total-box td {
            padding: 4px;
        }
        .total-box .grand-total {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 8px;
        }
        .signature-area {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        .no-print button {
            padding: 10px 20px;
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak Halaman Ini</button>
    </div>

    <div class="invoice-box">
        <!-- Brand -->
        <div class="header">
            <div style="float: right; text-align: right;">
                @php
                    $hasInspection = $reservation->roomInspections()->exists();
                    $docTitle = 'INVOICE';
                    if ($reservation->invoice) {
                        if ($reservation->invoice->status === 'paid') $docTitle = 'INVOICE';
                        elseif ($hasInspection) $docTitle = 'INVOICE';
                        elseif ($reservation->invoice->status === 'partial') $docTitle = 'BUKTI PEMBAYARAN DP';
                        else $docTitle = 'TAGIHAN SEMENTARA';
                    }
                @endphp
                <div class="header-title">{{ $docTitle }}</div>
                <div style="margin-top: 5px; font-weight: bold;">No: {{ $reservation->invoice ? $reservation->invoice->invoice_number : '-' }}</div>
                <div style="font-size: 12px; color: #666;">Tanggal: {{ $reservation->invoice ? $reservation->invoice->invoice_date->format('d F Y') : now()->format('d F Y') }}</div>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: bold; letter-spacing: 1px;">{{ $settings->name ?? 'PPKD Hotel' }}</div>
                <div style="font-size: 12px; color: #555; max-width: 350px;">{{ $settings->address ?? 'Yogyakarta' }}</div>
                <div style="font-size: 12px; color: #555;">Telp: {{ $settings->phone ?? '-' }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Info details -->
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <strong>Tamu Terhormat:</strong><br>
                    Nama: {{ $reservation->guest->full_name }}<br>
                    ID: {{ $reservation->guest->id_number }}<br>
                    Negara: {{ $reservation->guest->nationality }}<br>
                    Telp: {{ $reservation->guest->phone }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Informasi Reservasi:</strong><br>
                    Booking Code: {{ $reservation->reservation_code }}<br>
                    Kamar: #{{ $reservation->room->room_number }} ({{ $reservation->room->roomType->name }})<br>
                    Periode: {{ $reservation->checkin_date->format('d/m/Y') }} - {{ $reservation->checkout_date->format('d/m/Y') }}<br>
                    Pax: {{ $reservation->adults }} Dewasa, {{ $reservation->children }} Anak
                </td>
            </tr>
        </table>

        <!-- Itemized List -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Layanan / Kamar</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Harga Satuan (Rp)</th>
                    <th style="text-align: right;">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $nights = $reservation->checkin_date->diffInDays($reservation->checkout_date);
                    $nights = $nights > 0 ? $nights : 1;
                    $roomBasePrice = $reservation->room->roomType->base_price;
                @endphp
                <!-- Room charge -->
                <tr>
                    <td>Sewa Kamar #{{ $reservation->room->room_number }} ({{ $reservation->room->roomType->name }})</td>
                    <td style="text-align: center;">{{ $nights }} Malam</td>
                    <td style="text-align: right;">{{ number_format($roomBasePrice, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($roomBasePrice * $nights, 0, ',', '.') }}</td>
                </tr>

                @php
                    $addonCodes = ['fnb', 'laundry'];
                    $addonCharges = $reservation->charges->filter(fn($c) => in_array($c->chargeType->code, $addonCodes));
                    $damageCharges = $reservation->charges->filter(fn($c) => !in_array($c->chargeType->code, $addonCodes));
                    $detailAddons = $reservation->details->where('type', '!=', 'special_request');
                    $hasAddonSection = $detailAddons->count() > 0 || $addonCharges->count() > 0;
                @endphp

                <!-- Add-on Charges: Extra Bed, Breakfast, FnB, Laundry -->
                @if($hasAddonSection)
                    <tr style="background: #f8fafc;">
                        <td colspan="4" style="padding: 6px 8px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">Add-on Charges</td>
                    </tr>
                    @foreach($detailAddons as $detail)
                        <tr>
                            <td style="padding-left: 20px;">
                                Addon: {{ $detail->type === 'extra_bed' ? 'Extra Bed' : 'Breakfast' }}
                                @if($detail->type === 'breakfast')
                                    <br><small>({{ $detail->qty / max(1, $reservation->adults) }} Hari &times; {{ $reservation->adults }} Pax)</small>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $detail->qty }}</td>
                            <td style="text-align: right;">{{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @foreach($addonCharges as $charge)
                        <tr>
                            <td style="padding-left: 20px;">{{ $charge->description }}<br><small>{{ $charge->chargeType->name }}</small></td>
                            <td style="text-align: center;">1</td>
                            <td style="text-align: right;">{{ number_format($charge->amount, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($charge->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif

                <!-- Additional Charges: damage/loss only -->
                @if($damageCharges->count() > 0)
                    <tr style="background: #f8fafc;">
                        <td colspan="4" style="padding: 6px 8px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">Additional Charges</td>
                    </tr>
                    @foreach($damageCharges as $charge)
                        <tr>
                            <td style="padding-left: 20px; font-weight: bold;">{{ $charge->description }}</td>
                            <td style="text-align: center;">1</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($charge->amount, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($charge->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif

                <!-- Pre-checkout Penalty (only shown when billing is computed dynamically) -->
                @if($billing && $billing['penaltyAmount'] > 0)
                    <tr>
                        <td style="font-weight: bold;">{{ $billing['penaltyDesc'] }}</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($billing['penaltyAmount'], 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($billing['penaltyAmount'], 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals box -->
        <div style="width: 100%;">
            <div class="total-box">
                @php
                    if ($billing) {
                        // Pre-checkout: use dynamic billing data
                        $subTotalInvoice = $billing['itemsSubtotal'];
                        $discVal = $billing['discount'];
                        $serviceVal = $billing['serviceCharge'];
                        $taxVal = $billing['tax'];
                        $grandTotal = $billing['grandTotal'];
                        $paidAmount = $billing['roomPaid'];
                        $balanceDue = $billing['balance'];
                        $depositHeld = $billing['depositHeld'];
                        $depositToReturn = $billing['depositToReturn'];
                    } else {
                        // Post-checkout: use persisted DB data
                        $subTotalInvoice = $reservation->invoice ? $reservation->invoice->subtotal : ($reservation->subtotal + $reservation->charges()->sum('amount'));
                        $discVal = $reservation->discount;
                        $serviceVal = $reservation->invoice ? $reservation->invoice->service_charge : $reservation->service_charge;
                        $taxVal = $reservation->invoice ? $reservation->invoice->tax : $reservation->tax;
                        $grandTotal = $reservation->invoice ? $reservation->invoice->total_amount : $reservation->total;
                        $paidAmount = $reservation->invoice ? $reservation->invoice->payments()->where('status','success')->where('type','room')->sum('amount') : 0;
                        $depositHeld = $reservation->invoice ? ($reservation->invoice->deposit_amount ?? 0) : 0;
                        $depositToReturn = $reservation->invoice ? (($reservation->invoice->deposit_amount ?? 0) - ($reservation->invoice->deposit_returned ?? 0)) : 0;
                        $balanceDue = $grandTotal - $paidAmount - $depositHeld;
                    }
                @endphp
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;">Rp {{ number_format($subTotalInvoice, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Diskon:</td>
                        <td style="text-align: right;">- Rp {{ number_format($discVal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Service Charge:</td>
                        <td style="text-align: right;">Rp {{ number_format($serviceVal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Pajak (10%):</td>
                        <td style="text-align: right;">Rp {{ number_format($taxVal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td>Total Akhir:</td>
                        <td style="text-align: right;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr style="color: green; font-weight: bold;">
                        <td>Telah Dibayar (Kamar):</td>
                        <td style="text-align: right;">Rp {{ number_format($paidAmount, 0, ',', '.') }}</td>
                    </tr>
                    @if($depositHeld > 0)
                        <tr style="color: #92400e; font-weight: bold;">
                            <td>Deposit Jaminan:</td>
                            <td style="text-align: right;">Rp {{ number_format($depositHeld, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($balanceDue < 0)
                        <tr style="color: blue; font-weight: bold; border-top: 1px dashed #ccc;">
                            <td style="padding-top: 6px;">Total Refund:</td>
                            <td style="text-align: right; padding-top: 6px;">Rp {{ number_format(abs($balanceDue), 0, ',', '.') }}</td>
                        </tr>
                    @else
                        <tr style="color: red; font-weight: bold; border-top: 1px dashed #ccc;">
                            <td style="padding-top: 6px;">Sisa Tagihan:</td>
                            <td style="text-align: right; padding-top: 6px;">Rp {{ number_format($balanceDue, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>

        @php
            $noteDetail = $reservation->details()->where('type', 'special_request')->first();
        @endphp
        @if($noteDetail)
        <div style="margin-top: 30px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc; font-size: 12px; line-height: 1.5; color: #475569;">
            <div style="font-weight: bold; margin-bottom: 5px; color: #1e293b; text-transform: uppercase;">Catatan Booking:</div>
            <div style="font-style: italic;">
                {{ $noteDetail->notes }}
            </div>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signature-area">
            <div class="signature-box">
                <div>Penerima / Staf</div>
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 12px;">{{ auth()->user()->name }}</div>
            </div>
            <div class="signature-box">
                <div>Tamu Terhormat</div>
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 12px;">{{ $reservation->guest->full_name }}</div>
            </div>
        </div>
    </div>

</body>
</html>
