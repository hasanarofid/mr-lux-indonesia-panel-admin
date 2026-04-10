<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Faktur Penjualan - {{ $sale->invoice_number }}</title>
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
            width: 55%;
            height: 35px;
            border: 1px solid #000;
            padding: 5px;
        }

        .total-container {
            width: 40%;
        }

        .total-box {
            border: 2px solid #000;
            display: flex;
            align-items: stretch;
            font-weight: bold;
        }

        .total-label {
            background: #000;
            color: #fff;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .total-value {
            flex: 1;
            padding: 8px 12px;
            text-align: right;
            font-size: 16px;
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
    <div class="no-print">
        <button onclick="window.print()">Cetak Faktur</button>
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
                <h1 class="invoice-title">Faktur Penjualan</h1>
                <div class="meta-box">
                    <div class="meta-item">
                        <div class="meta-label">Tanggal</div>
                        <div class="meta-value">{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Nomor</div>
                        <div class="meta-value">{{ $sale->invoice_number }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer -->
        <div class="customer-section">
            <div class="kepada">Kepada :</div>
            <div class="customer-details">
                {{ $sale->customer->name }}<br>
                @php
                    $customerAddress = $sale->customer->address;
                    if (empty($customerAddress)) {
                        $addressParts = array_filter([
                            $sale->customer->billing_street,
                            $sale->customer->billing_city,
                        ]);
                        $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Semarang';
                    }
                @endphp
                {{ $customerAddress }}
            </div>
        </div>

        <!-- Items Table -->
        <table class="content-table">
            <thead>
                <tr>
                    <th width="8%">Banyak</th>
                    <th width="8%">Satuan</th>
                    <th width="44%">Nama Barang</th>
                    <th width="15%">Harga</th>
                    <th width="10%">Diskon</th>
                    <th width="15%">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->unit ?? ($item->product?->uom ?? 'Set') }}</td>
                    <td>{{ $item->product ? $item->product->name : ($item->description ?? '-') }}</td>
                    <td class="text-right">{{ number_format((float)($item->price ?? 0), 0, ',', '.') }}</td>
                    <td class="text-center">0</td>
                    <td class="text-right">{{ number_format((float)($item->subtotal ?? 0), 0, ',', '.') }}</td>
                </tr>
                @endforeach
                {{-- Fill empty rows to maintain box height --}}
                @for($i = count($sale->items); $i < 4; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
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
                {{-- Placeholder for notes if any --}}
            </div>
            <div class="total-container">
                <div class="total-box">
                    <div class="total-label">Total</div>
                    <div class="total-value">{{ number_format((float)($sale->grand_total ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="sig-box">
                <div style="font-weight: bold;">Pengirim</div>
                <div class="sig-name"></div>
                <div class="sig-date">Tgl: ....................</div>
            </div>
            <div class="sig-box">
                <div style="font-weight: bold;">Penerima</div>
                <div class="sig-name"></div>
                <div class="sig-date">Tgl: ....................</div>
            </div>
        </div>
    </div>
</body>
</html>
