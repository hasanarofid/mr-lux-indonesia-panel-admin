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
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
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

        /* Accurate Style Header */
        .accurate-header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .logo-section {
            width: 30%;
        }

        .logo-section img {
            height: 60px;
            width: auto;
        }

        .title-section {
            width: 40%;
            text-align: center;
        }

        .title-section h1 {
            font-size: 24px;
            margin: 0;
            display: inline-block;
            border-bottom: 3px double #000;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .customer-info {
            width: 60%;
        }

        .date-info {
            width: 30%;
            text-align: right;
        }

        .customer-info table, .date-info table {
            border-collapse: collapse;
        }

        .customer-info td, .date-info td {
            vertical-align: top;
            padding: 1px 0;
        }

        /* Accurate Style Table */
        .accurate-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .accurate-table th {
            border-bottom: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
        }

        .accurate-table td {
            padding: 8px 5px;
            vertical-align: top;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* Accurate Style Footer */
        .accurate-footer {
            margin-top: 20px;
            width: 100%;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .signature-col {
            width: 30%;
            text-align: left;
        }

        .signature-line {
            margin-top: 50px;
            border-bottom: 1px solid #000;
            width: 80%;
            display: inline-block;
        }

        .date-line {
            margin-top: 50px;
            border-bottom: 1px solid #000;
            width: 60%;
            display: inline-block;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak Dokumen</button>
    </div>

    @php
        $customerAddress = $deliveryNote->address;
        $customer = $deliveryNote->sale?->customer ?? $deliveryNote->customer;
        
        if (empty($customerAddress)) {
            // Fallback for Manual SJ which might have many-to-many customers
            if (!$customer && $deliveryNote->customers()->exists()) {
                $customer = $deliveryNote->customers()->first();
            }

            if ($customer) {
                $addressParts = array_filter([
                    $customer->billing_street,
                    $customer->billing_city,
                    $customer->billing_province,
                    $customer->billing_postcode,
                    $customer->billing_country,
                ]);
                $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Alamat tidak tersedia';
            } else {
                $customerAddress = 'Alamat tidak tersedia';
            }
        }
    @endphp

    <div class="accurate-header">
        <div class="header-top">
            <div class="logo-section">
                <img src="{{ asset('images/logokopsurat.png') }}" alt="Logo">
            </div>
            <div class="title-section">
                <h1>SURAT JALAN</h1>
            </div>
            <div class="date-info">
                <!-- Empty for alignment -->
            </div>
        </div>

        <div class="info-section">
            <div class="customer-info">
                <table>
                    <tr>
                        <td width="60">Nama</td>
                        <td>: {{ $customer->name ?? 'Pelanggan Umum' }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $customerAddress }}</td>
                    </tr>
                </table>
            </div>
            <div class="date-info">
                <table>
                    <tr>
                        <td>Tanggal:</td>
                        <td style="padding-left: 5px;">{{ \Carbon\Carbon::parse($deliveryNote->date)->translatedFormat('d M Y') }}</td>
                    </tr>
                    @if($deliveryNote->driver_name)
                    <tr>
                        <td>Sopir:</td>
                        <td style="padding-left: 5px;">{{ $deliveryNote->driver_name }}</td>
                    </tr>
                    @endif
                    @if($deliveryNote->vehicle_number)
                    <tr>
                        <td>No. Kendaraan:</td>
                        <td style="padding-left: 5px;">{{ $deliveryNote->vehicle_number }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <table class="accurate-table">
        <thead>
            <tr>
                <th width="20%">Kode Barang</th>
                <th width="50%">Nama Barang</th>
                <th width="30%" class="text-right">Kuantitas (Satuan)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryNote->items as $item)
            @php
                $qty = $item->quantity;
                $isi = $item->product?->isi ?? 0;
                $uom = $item->unit ?? $item->product?->uom ?? 'PCS';
                
                $displayText = '';
                if ($item->product && $isi > 0 && $qty >= $isi) {
                    $dus = floor($qty / $isi);
                    $sisa = $qty % $isi;
                    
                    $displayText = $dus . ' DUS';
                    if ($sisa > 0) {
                        $displayText .= ' ' . $sisa . ' ' . $uom;
                    }
                } else {
                    $displayText = $qty . ' ' . $uom;
                }
            @endphp
            <tr>
                <td>{{ $item->product?->sku ?? '-' }}</td>
                <td>{{ $item->product?->name ?? $item->description ?? '-' }}</td>
                <td class="text-right">{{ $displayText }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="accurate-footer">
        <div class="footer-signatures">
            <div class="signature-col">
                <p>Penerima,</p>
                <div class="signature-line"></div>
            </div>
            <div class="signature-col" style="text-align: center;">
                <p>Pengirim</p>
                <div class="signature-line" style="width: 70%;"></div>
            </div>
            <div class="signature-col" style="text-align: right;">
                <p style="margin-right: 40%;">Tgl.</p>
                <div class="date-line"></div>
            </div>
        </div>
    </div>
</body>
</html>
