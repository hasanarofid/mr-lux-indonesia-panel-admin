<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WarehousePickup extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'number',
        'date',
        'driver_name',
        'vehicle_number',
        'address',
        'note',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(WarehousePickupItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Pengambilan Gudang {$eventName} by " . (auth()->user()?->name ?? 'System'));
    }
}
