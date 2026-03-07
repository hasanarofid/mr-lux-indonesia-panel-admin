<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $deliveryNote->number }}</title>
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

        .intro-text {
            margin: 20px 0 10px 0;
            font-weight: 600;
            color: #555;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        .delivery-info {
            margin-top: 20px;
            display: flex;
            gap: 40px;
            font-size: 12px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid var(--primary-color, #d32f2f);
        }

        .info-item span:first-child {
            color: #888;
            margin-right: 10px;
        }

        .info-item span:last-child {
            font-weight: 700;
        }

        .footer-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 180px;
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
        $customer = $deliveryNote->sale?->customer ?? $deliveryNote->customer;
        $customerAddress = $customer?->address;
        
        if (empty($customerAddress) && $customer) {
            $addressParts = array_filter([
                $customer->billing_street,
                $customer->billing_city,
                $customer->billing_province,
                $customer->billing_postcode,
                $customer->billing_country,
            ]);
            $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Alamat tidak tersedia';
        }
    @endphp

    @include('partials.kop', [
        'title' => 'SURAT JALAN',
        'number' => $deliveryNote->number,
        'date' => $deliveryNote->date,
        'ref' => $deliveryNote->sale?->invoice_number ?? '-',
        'customerName' => $customer->name ?? 'Pelanggan Umum',
        'customerAddress' => $customerAddress ?? 'Alamat tidak tersedia'
    ])

    <p class="intro-text">Mohon diterima barang-barang tersebut di bawah ini:</p>

    <table class="content-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="65%">Deskripsi Produk</th>
                <th width="15%" class="text-center">Kuantitas</th>
                <th width="15%" class="text-center">UOM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryNote->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $item->product->name }}</div>
                    <div style="font-size: 9px; color: #777;">Code: {{ $item->product->sku }}</div>
                </td>
                <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->unit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="delivery-info">
        <div class="info-item">
            <span>Supir:</span>
            <span>{{ $deliveryNote->driver_name }}</span>
        </div>
        <div class="info-item">
            <span>No. Kendaraan:</span>
            <span>{{ $deliveryNote->vehicle_number }}</span>
        </div>
    </div>

    <div class="footer-section">
        <div class="signature-box">
            <p>Diterima Oleh,</p>
            <div class="signature-space"></div>
            <p class="signature-name">( ............................ )</p>
        </div>
        <div class="signature-box">
            <p>Pengirim / Driver,</p>
            <div class="signature-space"></div>
            <p class="signature-name">( {{ $deliveryNote->driver_name }} )</p>
        </div>
        <div class="signature-box">
            <p>Hormat Kami,</p>
            <div class="signature-space"></div>
            <p class="signature-name">( MR LUX INDONESIA )</p>
        </div>
    </div>
</body>
</html>
