<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .footer-total {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            font-weight: bold;
            font-size: 14px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Sekarang</button>
    </div>

    <div class="header">
        <h1>Laporan Penjualan</h1>
        <p>MR LUX INDONESIA</p>
    </div>

    <div class="info">
        @if($from && $to)
            <p>Periode: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</p>
        @endif
        @if($customer)
            <p>Pelanggan: {{ $customer->name }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Pelanggan</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    <td>{{ $sale->note ?? '-' }}</td>
                    <td>{{ $sale->status }}</td>
                    <td class="text-right">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($totalFiltered > 0)
        <div class="footer-total text-right">
            TOTAL PEMBELIAN: Rp {{ number_format($totalFiltered, 0, ',', '.') }}
        </div>
    @endif

    <div style="margin-top: 50px; text-align: right;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
