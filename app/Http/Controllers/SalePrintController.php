<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalePrintController extends Controller
{
    public function show(\App\Models\Sale $sale)
    {
        return view('sales.nota', compact('sale'));
    }
}
