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
                <div class="header-title">INVOICE</div>
                <div style="margin-top: 5px; font-weight: bold;">No: {{ $reservation->invoice ? $reservation->invoice->invoice_number : '-' }}</div>
                <div style="font-size: 12px; color: #666;">Tanggal: {{ $reservation->invoice ? $reservation->invoice->invoice_date->format('d F Y') : now()->format('d F Y') }}</div>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: bold; letter-spacing: 1px;">{{ $settings->name ?? 'Hotel Kejora' }}</div>
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

                <!-- Addons -->
                @foreach($reservation->details as $detail)
                    @if($detail->type !== 'special_request')
                        <tr>
                            <td>Addon: {{ $detail->type === 'extra_bed' ? 'Extra Bed' : 'Breakfast' }}</td>
                            <td style="text-align: center;">{{ $detail->qty }}</td>
                            <td style="text-align: right;">{{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach

                <!-- Charges (Fnb, laundry, damages) -->
                @foreach($reservation->charges as $charge)
                    <tr>
                        <td>Layanan: {{ $charge->description }}</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right;">{{ number_format($charge->amount, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($charge->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals box -->
        <div style="width: 100%;">
            <div class="total-box">
                @php
                    $subTotalInvoice = $reservation->subtotal + $reservation->charges()->sum('amount');
                    $discVal = $reservation->discount;
                    $taxVal = $reservation->invoice ? $reservation->invoice->tax : $reservation->tax;
                    $serviceVal = $reservation->invoice ? $reservation->invoice->service_charge : $reservation->service_charge;
                    $grandTotal = $reservation->invoice ? $reservation->invoice->total_amount : $reservation->total;
                    $paidAmount = $reservation->invoice ? $reservation->invoice->paid_amount : 0;
                    $balanceDue = $reservation->invoice ? $reservation->invoice->balance_due : $grandTotal;
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
                        <td>Telah Dibayar:</td>
                        <td style="text-align: right;">Rp {{ number_format($paidAmount, 0, ',', '.') }}</td>
                    </tr>
                    <tr style="color: red; font-weight: bold;">
                        <td>Sisa Tagihan:</td>
                        <td style="text-align: right;">Rp {{ number_format($balanceDue, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>

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
