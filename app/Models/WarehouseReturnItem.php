<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseReturnItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'warehouse_return_id',
        'product_id',
        'unit',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function warehouseReturn()
    {
        return $this->belongsTo(WarehouseReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
    
    public function getConvertedQuantity(): float
    {
        $product = $this->product;
        if (!$product) {
            return (float) $this->quantity;
        }

        $qty = (float) $this->quantity;
        return match (strtoupper($this->unit)) {
            'DUS' => $qty * ($product->isi > 0 ? $product->isi : 1),
            'SET' => $product->uom === 'SET' ? $qty : $qty * ($product->isi > 0 ? $product->isi : 1), // Assuming SET follows same logic if not native unit
            default => $qty,
        };
    }
}
