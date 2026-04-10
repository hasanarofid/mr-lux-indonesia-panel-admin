<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $deliveryNote->number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #f0f0f0;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .no-print button {
            padding: 8px 16px;
            background: #000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Continuous Form Styling */
        .form-wrapper {
            width: 210mm;
            height: 148.5mm; /* Half A4 height */
            margin: 20px auto;
            position: relative;
            padding: 10mm 15mm;
            box-sizing: border-box;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* Perforated Edge Effect */
        .perforation {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
        }

        .perforation.left { left: 2mm; }
        .perforation.right { right: 2mm; }

        .hole {
            width: 4mm;
            height: 4mm;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            background: #f3f4f6;
        }

        /* Header Layout */
        .header-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .company-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .logo-box {
            background: #ff0000;
            color: #fff;
            border-radius: 50%;
            width: 80px;
            height: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 14px;
            text-align: center;
            line-height: 1.1;
        }

        .company-details p {
            margin: 0;
            font-size: 11px;
            line-height: 1.3;
        }

        .title-meta {
            text-align: right;
            width: 40%;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }

        .meta-box {
            border: 1px solid #000;
            display: inline-flex;
            text-align: left;
            width: 100%;
        }

        .meta-item {
            padding: 5px 10px;
            border-right: 1px solid #000;
            flex: 1;
        }

        .meta-item:last-child {
            border-right: none;
        }

        .meta-label {
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .meta-value {
            font-weight: bold;
            font-size: 12px;
        }

        /* Customer Section */
        .customer-section {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .kepada {
            margin-bottom: 5px;
        }

        .customer-details {
            font-weight: bold;
            line-height: 1.4;
        }

        /* Table Styling */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .content-table th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            text-transform: uppercase;
            font-size: 11px;
            background: #f8fafc;
        }

        .content-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 12px;
        }

        .content-table tr:last-child td {
            border-bottom: 1px solid #000;
        }

        /* Footer / Summary Box */
        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .note-box {
            width: 100%;
            height: 35px;
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
        }

        /* Signature */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            text-align: center;
            padding: 0 40px;
        }

        .sig-box {
            width: 150px;
        }

        .sig-name {
            margin-top: 40px;
            border-bottom: 1px solid #000;
            min-height: 20px;
        }

        .sig-date {
            font-size: 10px;
            margin-top: 5px;
            text-align: left;
        }

        /* Helper Classes */
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        @media print {
            @page { size: landscape; }
            .no-print { display: none; }
            body { background: none; margin: 0; padding: 0; }
            .form-wrapper { 
                margin: 0; 
                box-shadow: none; 
                width: 100%;
                height: 100vh;
            }
            .hole { border: 1px solid #eee; background: none; }
        }
    </style>
</head>
<body>
    @php
        $customer = $deliveryNote->sale?->customer ?? $deliveryNote->customer;
        $customerAddress = $deliveryNote->address;
        
        if (empty($customerAddress)) {
            // Fallback for Manual SJ which might have many-to-many customers
            if (!$customer && $deliveryNote->customers()->exists()) {
                $customer = $deliveryNote->customers()->first();
            }

            if ($customer) {
                $addressParts = array_filter([
                    $customer->billing_street,
                    $customer->billing_city,
                ]);
                $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Semarang';
            } else {
                $customerAddress = 'Semarang';
            }
        }
    @endphp

    <div class="no-print">
        <button onclick="window.print()">Cetak Surat Jalan</button>
    </div>

    <div class="form-wrapper">
        <!-- Decoration perforated dots -->
        <div class="perforation left">
            @for($i=0; $i<12; $i++) <div class="hole"></div> @endfor
        </div>
        <div class="perforation right">
            @for($i=0; $i<12; $i++) <div class="hole"></div> @endfor
        </div>

        <!-- Header -->
        <div class="header-section">
            <div class="company-info">
                <div class="logo-box">
                    MR<br>LUX
                </div>
                <div class="company-details">
                    <p style="font-weight: bold; font-size: 12px;">www.mrluxindonesia.com</p>
                    <p>Tlp: (024) 7624836</p>
                    <p>Semarang, Indonesia</p>
                </div>
            </div>
            <div class="title-meta">
                <h1 class="invoice-title">Surat Jalan</h1>
                <div class="meta-box">
                    <div class="meta-item">
                        <div class="meta-label">Tanggal</div>
                        <div class="meta-value">{{ \Carbon\Carbon::parse($deliveryNote->date)->format('d M Y') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Nomor</div>
                        <div class="meta-value">{{ $deliveryNote->number }}</div>
                    </div>
                    @if($deliveryNote->driver_name)
                    <div class="meta-item">
                        <div class="meta-label">Sopir</div>
                        <div class="meta-value">{{ $deliveryNote->driver_name }}</div>
                    </div>
                    @endif
                    @if($deliveryNote->vehicle_number)
                    <div class="meta-item">
                        <div class="meta-label">No. Kendaraan</div>
                        <div class="meta-value">{{ $deliveryNote->vehicle_number }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer -->
        <div class="customer-section">
            <div class="kepada">Kepada :</div>
            <div class="customer-details">
                {{ $customer?->name ?? 'Pelanggan Umum' }}<br>
                {{ $customerAddress }}
            </div>
        </div>

        <!-- Items Table -->
        <table class="content-table">
            <thead>
                <tr>
                    <th width="15%">Kode Barang</th>
                    <th width="55%">Nama Barang</th>
                    <th width="30%">Kuantitas (Satuan)</th>
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
                        $displayText = number_format($qty, 0, ',', '.') . ' ' . $uom;
                    }
                @endphp
                <tr>
                    <td>{{ $item->product?->sku ?? '-' }}</td>
                    <td>{{ $item->product?->name ?? $item->description ?? '-' }}</td>
                    <td class="text-right">{{ $displayText }}</td>
                </tr>
                @endforeach
                {{-- Fill empty rows to maintain box height --}}
                @for($i = count($deliveryNote->items); $i < 4; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Footer / Summary -->
        <div class="footer-grid">
            <div class="note-box">
                Catatan: Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="sig-box">
                <div style="font-weight: bold;">Penerima</div>
                <div class="sig-name"></div>
                <div class="sig-date">Tgl: ....................</div>
            </div>
            <div class="sig-box">
                <div style="font-weight: bold;">Pengirim</div>
                <div class="sig-name"></div>
                <div class="sig-date">Tgl: ....................</div>
            </div>
            <div class="sig-box">
                <div style="font-weight: bold;">Tgl.</div>
                <div class="sig-name"></div>
                <div class="sig-date">Jam: ....................</div>
            </div>
        </div>
    </div>
</body>
</html>
