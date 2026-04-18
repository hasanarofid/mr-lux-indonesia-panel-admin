<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;

class WarehousePickup extends Model
{
    use SoftDeletes, LogsActivity;
    
    protected static function booted()
    {
        static::deleting(function ($pickup) {
            foreach ($pickup->items as $item) {
                $product = Product::withTrashed()->find($item->product_id);
                if ($product && $product->is_track_stock) {
                    // Revert based on converted quantity
                    $product->increment('stock', $item->getConvertedQuantity());
                }
            }
        });

        static::restoring(function ($pickup) {
            foreach ($pickup->items as $item) {
                $product = Product::withTrashed()->find($item->product_id);
                if ($product && $product->is_track_stock) {
                    // Re-apply based on converted quantity
                    $product->decrement('stock', $item->getConvertedQuantity());
                }
            }
        });
    }

    protected $fillable = [
        'number',
        'date',
        'type',
        'sale_id',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logOnly(['number', 'driver_name', 'status', 'type', 'item_summary'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $type = $this->type === 'manual' ? 'Barang Dibawa' : 'Invoice';
                return "{$eventName} Pengambilan Gudang ({$type}) #{$this->number} oleh " . (Auth::user()?->name ?? 'System');
            });
    }

    public function getItemSummaryAttribute(): string
    {
        return $this->items->map(function ($item) {
            return $item->product ? $item->product->name : 'Unknown Product';
        })->filter()->unique()->implode(', ');
    }
}
