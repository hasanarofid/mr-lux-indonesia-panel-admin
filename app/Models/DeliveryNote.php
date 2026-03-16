<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DeliveryNote extends Model
{
    use LogsActivity;

    protected static function booted()
    {
        static::deleting(function ($deliveryNote) {
            $deliveryNote->items()->each(fn($item) => $item->delete());
        });
    }

    protected $fillable = [
        'sale_id',
        'customer_id',
        'type',
        'number',
        'date',
        'driver_name',
        'vehicle_number',
        'status',
    ];

    public function getNameAttribute()
    {
        return $this->number;
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logOnly(['number', 'status', 'name', 'type'])
            ->dontSubmitEmptyLogs();
    }
}
