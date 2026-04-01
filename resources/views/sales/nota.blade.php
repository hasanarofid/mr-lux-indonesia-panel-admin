<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .no-print button {
            padding: 8px 16px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .content-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 10px 8px;
            text-align: left;
        }

        .content-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        .summary-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .summary-table {
            width: 300px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 5px 8px;
            font-size: 12px;
        }

        .summary-label {
            color: #666;
            text-align: right;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
            width: 120px;
        }

        .grand-total-row td {
            border-top: 2px solid #d32f2f;
            color: #d32f2f;
            font-size: 14px !important;
            font-weight: 800 !important;
            padding-top: 10px !important;
        }

        .footer-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-space {
            height: 60px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 30px; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak Dokumen</button>
    </div>

    @php
        $customerAddress = $sale->customer->address;
        if (empty($customerAddress)) {
            $addressParts = array_filter([
                $sale->customer->billing_street,
                $sale->customer->billing_city,
                $sale->customer->billing_province,
                $sale->customer->billing_postcode,
                $sale->customer->billing_country,
            ]);
            $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Alamat tidak tersedia';
        }
    @endphp

    @include('partials.kop', [
        'title' => 'FAKTUR PENJUALAN',
        'number' => $sale->invoice_number,
        'date' => $sale->date,
        'customerName' => $sale->customer->name,
        'customerAddress' => $customerAddress
    ])

    <table class="content-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="45%">Deskripsi Produk</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="10%" class="text-center">UOM</th>
                <th width="15%" class="text-right">Harga Satuan</th>
                <th width="15%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: 600;">
                        {{ $item->product ? $item->product->name : ($item->description ?? '-') }}
                    </div>
                </td>
                <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->unit ?? ($item->product?->uom ?? '-') }}</td>
                <td class="text-right">Rp {{ number_format((float)($item->price ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format((float)($item->subtotal ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal</td>
                <td class="summary-value">Rp {{ number_format((float)($sale->subtotal ?? 0), 0, ',', '.') }}</td>
            </tr>
            @if($sale->discount_invoice > 0)
            <tr>
                <td class="summary-label">Potongan Nota</td>
                <td class="summary-value">- Rp {{ number_format((float)($sale->discount_invoice ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($sale->is_ppn)
            <tr>
                <td class="summary-label">PPN (11%)</td>
                <td class="summary-value">Rp {{ number_format((float)($sale->ppn_amount ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($sale->shipping_cost > 0)
            <tr>
                <td class="summary-label">Biaya Pengiriman</td>
                <td class="summary-value">Rp {{ number_format((float)($sale->shipping_cost ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total-row">
                <td class="summary-label">TOTAL AKHIR</td>
                <td class="summary-value" style="color: #d32f2f;">Rp {{ number_format((float)($sale->grand_total ?? 0), 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer-section">
        <div class="signature-box">
            <p>Hormat Kami,</p>
            <div class="signature-space"></div>
            <p class="signature-name">( MR LUX INDONESIA )</p>
        </div>
        <div class="signature-box">
            <p>Penerima,</p>
            <div class="signature-space"></div>
            <p class="signature-name">( {{ $sale->customer->name }} )</p>
        </div>
    </div>
</body>
</html>
