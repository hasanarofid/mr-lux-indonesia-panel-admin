<?php

namespace App\Observers;

use App\Models\SaleItem;

class SaleItemObserver
{
    /**
     * Handle the SaleItem "created" event.
     */
    public function created(SaleItem $saleItem): void
    {
        $product = $saleItem->product;
        if ($product && $product->is_track_stock) {
            $product->decrement('stock', (float) $saleItem->quantity);
        }
    }

    /**
     * Handle the SaleItem "updated" event.
     */
    public function updated(SaleItem $saleItem): void
    {
        if ($saleItem->isDirty('product_id')) {
            // Return stock to old product
            $oldProduct = \App\Models\Product::withTrashed()->find($saleItem->getOriginal('product_id'));
            if ($oldProduct && $oldProduct->is_track_stock) {
                $oldProduct->increment('stock', (float) $saleItem->getOriginal('quantity'));
            }

            // Deduct from new product
            $newProduct = $saleItem->product;
            if ($newProduct && $newProduct->is_track_stock) {
                $newProduct->decrement('stock', (float) $saleItem->quantity);
            }
        } else {
            $product = $saleItem->product;
            if ($product && $product->is_track_stock) {
                $difference = (float) $saleItem->quantity - (float) $saleItem->getOriginal('quantity');
                $product->decrement('stock', $difference);
            }
        }
    }

    /**
     * Handle the SaleItem "deleted" event.
     */
    public function deleted(SaleItem $saleItem): void
    {
        // Use withTrashed in relationship or manually find
        $product = \App\Models\Product::withTrashed()->find($saleItem->product_id);
        if ($product && $product->is_track_stock) {
            $product->increment('stock', (float) $saleItem->quantity);
        }
    }

    /**
     * Handle the SaleItem "restored" event.
     */
    public function restored(SaleItem $saleItem): void
    {
        $product = \App\Models\Product::withTrashed()->find($saleItem->product_id);
        if ($product && $product->is_track_stock) {
            $product->decrement('stock', (float) $saleItem->quantity);
        }
    }

    /**
     * Handle the SaleItem "force deleted" event.
     */
    public function forceDeleted(SaleItem $saleItem): void
    {
        //
    }
}
