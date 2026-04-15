<?php

namespace App\Observers;

use App\Models\WarehousePickupItem;
use App\Models\Product;

class WarehousePickupItemObserver
{
    /**
     * Handle the WarehousePickupItem "created" event.
     */
    public function created(WarehousePickupItem $item): void
    {
        $product = $item->product;
        if ($product && $product->is_track_stock) {
            $product->decrement('stock', (float) $item->quantity);
            // returned_quantity doesn't add back to stock (goes to production)
        }
    }

    /**
     * Handle the WarehousePickupItem "updated" event.
     */
    public function updated(WarehousePickupItem $item): void
    {
        if ($item->isDirty('product_id')) {
            // Revert old product stock
            $oldProduct = Product::withTrashed()->find($item->getOriginal('product_id'));
            if ($oldProduct && $oldProduct->is_track_stock) {
                $oldProduct->increment('stock', (float) $item->getOriginal('quantity'));
            }

            // Apply to new product
            $newProduct = $item->product;
            if ($newProduct && $newProduct->is_track_stock) {
                $newProduct->decrement('stock', (float) $item->quantity);
            }
        } else {
            $product = $item->product;
            if ($product && $product->is_track_stock) {
                // Calculate difference for quantity (taken)
                $qtyDiff = (float) $item->quantity - (float) $item->getOriginal('quantity');
                $product->decrement('stock', $qtyDiff);
                
                // returned_quantity changes don't affect stock
            }
        }
    }

    /**
     * Handle the WarehousePickupItem "deleted" event.
     */
    public function deleted(WarehousePickupItem $item): void
    {
        $product = Product::withTrashed()->find($item->product_id);
        if ($product && $product->is_track_stock) {
            // Revert taken quantity only
            $product->increment('stock', (float) $item->quantity);
        }
    }

    /**
     * Handle the WarehousePickupItem "restored" event.
     */
    public function restored(WarehousePickupItem $item): void
    {
        $product = Product::withTrashed()->find($item->product_id);
        if ($product && $product->is_track_stock) {
            // Deduct again
            $product->decrement('stock', (float) $item->quantity);
        }
    }
}
