<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WarehousePickup extends Model
{
    use SoftDeletes, LogsActivity;
    
    protected static function booted()
    {
        static::deleting(function ($pickup) {
            foreach ($pickup->items as $item) {
                $product = Product::withTrashed()->find($item->product_id);
                if ($product && $product->is_track_stock) {
                    // Revert: return taken stock, take back returned stock
                    $product->increment('stock', (float) $item->quantity);
                    $product->decrement('stock', (float) $item->returned_quantity);
                }
            }
        });

        static::restoring(function ($pickup) {
            foreach ($pickup->items as $item) {
                $product = Product::withTrashed()->find($item->product_id);
                if ($product && $product->is_track_stock) {
                    // Re-apply: take stock, return "returned" stock
                    $product->decrement('stock', (float) $item->quantity);
                    $product->increment('stock', (float) $item->returned_quantity);
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
            ->logOnly(['number', 'driver_name', 'status', 'item_summary'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Pengambilan Gudang {$eventName} by " . (auth()->user()?->name ?? 'System'));
    }

    public function getItemSummaryAttribute(): string
    {
        return $this->items->map(function ($item) {
            return $item->product ? $item->product->name : 'Unknown Product';
        })->filter()->unique()->implode(', ');
    }
}
