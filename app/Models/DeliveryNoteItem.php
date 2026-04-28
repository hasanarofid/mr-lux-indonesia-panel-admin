<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNoteItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'delivery_note_id',
        'sale_id',
        'product_id',
        'description',
        'note',
        'unit',
        'quantity',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
