<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    protected $fillable = [
        'type',
        'date',
        'note',
    ];

    public function items()
    {
        return $this->hasMany(StockEntryItem::class);
    }
}
