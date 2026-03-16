<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Purchase extends Model
{
    use LogsActivity;

    protected static function booted()
    {
        static::deleting(function ($purchase) {
            $purchase->items()->each(fn($item) => $item->delete());
        });
    }

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
