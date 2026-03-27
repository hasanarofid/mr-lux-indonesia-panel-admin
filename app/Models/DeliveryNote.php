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
            if ($deliveryNote->status === 'DELIVERED') {
                throw new \Exception('Surat jalan yang sudah terkirim (DELIVERED) tidak bisa dihapus.');
            }
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
        'address',
    ];

    public function getNameAttribute()
    {
        return $this->number;
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'delivery_note_sale');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'delivery_note_customer');
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
            ->logOnly(['number', 'status', 'name', 'type', 'item_summary'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Surat Jalan {$eventName} by " . (auth()->user()?->name ?? 'System'));
    }

    public function getItemSummaryAttribute(): string
    {
        return $this->items->map(function ($item) {
            return $item->product ? $item->product->name : 'Unknown Product';
        })->filter()->unique()->implode(', ');
    }
}
