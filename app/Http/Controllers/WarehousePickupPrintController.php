<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehousePickup;

class WarehousePickupPrintController extends Controller
{
    public function show(WarehousePickup $warehousePickup)
    {
        return view('warehouse-pickups.print', compact('warehousePickup'));
    }
}
