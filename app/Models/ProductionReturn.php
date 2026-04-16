<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductionReturn extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'number',
        'date',
        'warehouse_pickup_id',
        'is_represented_by_warehouse',
        'driver_name',
        'vehicle_number',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'is_represented_by_warehouse' => 'boolean',
    ];

    public function warehousePickup()
    {
        return $this->belongsTo(WarehousePickup::class);
    }

    public function items()
    {
        return $this->hasMany(ProductionReturnItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Retur Produksi {$eventName} by " . (Auth::user()?->name ?? 'System'));
    }
}
