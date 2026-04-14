<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehousePickupItem extends Model
{
    protected $fillable = [
        'warehouse_pickup_id',
        'product_id',
        'quantity',
        'returned_quantity',
        'unit',
    ];

    public function warehousePickup()
    {
        return $this->belongsTo(WarehousePickup::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
