<!DOCTYPE html>
<html>
<head>
    <title>Surat Jalan - {{ $deliveryNote->number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 18px;
        }
        .invoice-info {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="header">
        <div class="company-info">
            <h1>MR LUX INDONESIA</h1>
            <p>Jln. Wr Supratman NO.31 Gisikdrono Semarang Barat</p>
            <p>Telp. (024) 7624836 / +62818452014</p>
        </div>
        <div class="invoice-info">
            <h2>SURAT JALAN</h2>
            <p>No: {{ $deliveryNote->number }}</p>
            <p>Tgl: {{ $deliveryNote->date }}</p>
            <p>Ref: {{ $deliveryNote->sale->invoice_number }}</p>
            <p>Cust: {{ $deliveryNote->sale->customer->name }}</p>
        </div>
    </div>

    <p>Mohon diterima barang-barang tersebut di bawah ini:</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product</th>
                <th>Qty</th>
                <th>UOM</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryNote->sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ number_format($item->quantity) }}</td>
                <td>{{ $item->product->uom }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p>Driver: {{ $deliveryNote->driver_name }}</p>
        <p>No. Kendaraan: {{ $deliveryNote->vehicle_number }}</p>
    </div>

    <div class="footer">
        <div>
            <p>Diterima Oleh,</p>
            <br><br>
            <p>( ............ )</p>
        </div>
        <div>
            <p>Pengirim/Driver,</p>
            <br><br>
            <p>( ............ )</p>
        </div>
        <div>
            <p>Hormat Kami,</p>
            <br><br>
            <p>( ............ )</p>
        </div>
    </div>
</body>
</html>
