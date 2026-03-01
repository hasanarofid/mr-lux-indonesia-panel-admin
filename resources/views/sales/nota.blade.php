<!DOCTYPE html>
<html>
<head>
    <title>Nota Penjualan - {{ $sale->invoice_number }}</title>
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
        .totals {
            margin-top: 10px;
            float: right;
            width: 300px;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
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
            <h2>NOTA PENJUALAN</h2>
            <p>No: {{ $sale->invoice_number }}</p>
            <p>Tgl: {{ $sale->date }}</p>
            <p>Cust: {{ $sale->customer->name }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product</th>
                <th>Qty</th>
                <th>UOM</th>
                <th>Price</th>
                <th>Disc</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ number_format($item->quantity) }}</td>
                <td>{{ $item->product->uom }}</td>
                <td>{{ number_format($item->price) }}</td>
                <td>{{ number_format($item->discount_item) }}</td>
                <td>{{ number_format($item->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal:</span> <span>{{ number_format($sale->subtotal) }}</span></div>
        @if($sale->discount_invoice > 0)
        <div><span>Disc. Nota:</span> <span>{{ number_format($sale->discount_invoice) }}</span></div>
        @endif
        @if($sale->is_ppn)
        <div><span>PPN (11%):</span> <span>{{ number_format($sale->ppn_amount) }}</span></div>
        @endif
        @if($sale->shipping_cost > 0)
        <div><span>Ongkir:</span> <span>{{ number_format($sale->shipping_cost) }}</span></div>
        @endif
        <div style="font-weight: bold; border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
            <span>TOTAL:</span> <span>{{ number_format($sale->grand_total) }}</span>
        </div>
    </div>

    <div class="footer">
        <div>
            <p>Tanda Terima,</p>
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
