<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;

class SalesReportPrintController extends Controller
{
    public function show(Request $request)
    {
        $query = Sale::query()->with('customer');

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
        
        $customer = null;
        if ($request->filled('customer')) {
            $customer = Customer::find($request->customer);
        }

        $totalFiltered = 0;
        if ($request->filled('customer') && $request->filled('from') && $request->filled('to')) {
            $totalFiltered = $sales->sum('grand_total');
        }

        return view('reports.sales-print', [
            'sales' => $sales,
            'from' => $request->from,
            'to' => $request->to,
            'customer' => $customer,
            'totalFiltered' => $totalFiltered,
        ]);
    }
}
