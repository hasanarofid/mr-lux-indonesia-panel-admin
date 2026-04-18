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

    /**
     * Get the actual quantity in base units (PCS) for stock reduction.
     */
    public function getConvertedQuantity(): float
    {
        $quantity = (float) $this->quantity;
        $parent = $this->warehousePickup;

        // Conversion logic only applies to 'manual' (Barang Dibawa) type
        if ($parent && $parent->type === 'manual') {
            $product = $this->product;
            if ($product) {
                if (strtoupper($this->unit) === 'DUS') {
                    $multiplier = (float) ($product->isi ?: 1);
                    return $quantity * $multiplier;
                }
                
                if (strtoupper($this->unit) === 'SET') {
                    $multiplier = (float) ($product->isi_set ?: 1);
                    return $quantity * $multiplier;
                }
            }
        }

        // For 'invoice' type or fallback, use 1-to-1 quantity
        return $quantity;
    }
}
