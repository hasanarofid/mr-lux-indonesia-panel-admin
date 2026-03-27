<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use SoftDeletes, LogsActivity;
    protected $fillable = [
        'name',
        'category',
        'sku',
        'uom',
        'isi',
        'isi_set',
        'price',
        'price_per_carton',
        'price_per_set',
        'stock',
        'description',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockEntryItems()
    {
        return $this->hasMany(StockEntryItem::class);
    }

    public function getStockAttribute($value)
    {
        return max(0, $value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
