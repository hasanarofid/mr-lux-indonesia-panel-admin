<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

class SalesReportExcelController extends Controller
{
    public function show(Request $request)
    {
        $query = Sale::query()
            ->with(['customer:id,name'])
            ->select(['id', 'customer_id', 'invoice_number', 'date', 'grand_total', 'status']);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }

        $sales = $query->orderBy('date', 'asc')->get();

        $writer = SimpleExcelWriter::streamDownload('laporan-penjualan.xlsx');

        foreach ($sales as $sale) {
            $writer->addRow([
                'Tanggal' => $sale->date,
                'Nomor Invoice' => $sale->invoice_number,
                'Pelanggan' => $sale->customer?->name ?? 'N/A',
                'Total' => $sale->grand_total,
                'Status' => $sale->status,
            ]);
        }

        return $writer->toBrowser();
    }
}
