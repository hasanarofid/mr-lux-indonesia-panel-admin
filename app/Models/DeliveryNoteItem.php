<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'product_id',
        'unit',
        'quantity',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
