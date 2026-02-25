<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $fillable = [
        'sale_id',
        'number',
        'date',
        'driver_name',
        'vehicle_number',
        'status',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
