<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_name',
        'date',
        'total',
        'note',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
