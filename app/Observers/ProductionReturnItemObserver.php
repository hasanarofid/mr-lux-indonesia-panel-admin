<?php

namespace App\Observers;

use App\Models\ProductionReturnItem;
use App\Models\Product;

class ProductionReturnItemObserver
{
    /**
     * Handle the ProductionReturnItem "created" event.
     */
    public function created(ProductionReturnItem $item): void
    {
        $product = Product::find($item->product_id);
        if ($product && $product->is_track_stock) {
            $product->increment('stock', (float) $item->quantity);
        }
    }

    /**
     * Handle the ProductionReturnItem "updated" event.
     */
    public function updated(ProductionReturnItem $item): void
    {
        $product = Product::find($item->product_id);
        if ($product && $product->is_track_stock) {
            $diff = (float) $item->quantity - (float) $item->getOriginal('quantity');
            if ($diff != 0) {
                $product->increment('stock', $diff);
            }
        }
    }

    /**
     * Handle the ProductionReturnItem "deleted" event.
     */
    public function deleted(ProductionReturnItem $item): void
    {
        $product = Product::find($item->product_id);
        if ($product && $product->is_track_stock) {
            $product->decrement('stock', (float) $item->quantity);
        }
    }
}
