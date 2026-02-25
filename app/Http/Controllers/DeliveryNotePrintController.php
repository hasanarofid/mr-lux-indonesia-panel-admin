<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeliveryNotePrintController extends Controller
{
    public function show(\App\Models\DeliveryNote $deliveryNote)
    {
        return view('delivery-notes.print', compact('deliveryNote'));
    }
}
