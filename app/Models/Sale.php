<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sale extends Model
{
    use LogsActivity;
    
    protected static function booted()
    {
        static::deleting(function ($sale) {
            $sale->items()->each(fn($item) => $item->delete());
            $sale->deliveryNotes()->each(fn($dn) => $dn->delete());
        });
    }

    public function getNameAttribute()
    {
        return $this->invoice_number;
    }

    protected $fillable = [
        'customer_id',
        'invoice_type',
        'invoice_number',
        'date',
        'due_date',
        'subtotal',
        'discount_item_total',
        'discount_invoice',
        'discount_invoice_percent',
        'is_ppn',
        'ppn_amount',
        'grand_total',
        'shipping_cost',
        'status',
        'note',
    ];
    
    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'is_ppn' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_item_total' => 'decimal:2',
        'discount_invoice' => 'decimal:2',
        'discount_invoice_percent' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function manualDeliveryNotes()
    {
        return $this->belongsToMany(DeliveryNote::class, 'delivery_note_sale');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logOnly(['invoice_number', 'status', 'grand_total', 'name'])
            ->dontSubmitEmptyLogs();
    }
}
