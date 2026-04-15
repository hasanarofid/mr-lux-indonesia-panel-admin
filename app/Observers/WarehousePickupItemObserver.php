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
            // Put back any previous quantity if it was somehow set, then decrement new
            $product->decrement('stock', (float) $item->quantity);
            $product->increment('stock', (float) $item->returned_quantity);
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
                // Revert: add back taken, take back returned
                $oldProduct->increment('stock', (float) $item->getOriginal('quantity'));
                $oldProduct->decrement('stock', (float) $item->getOriginal('returned_quantity'));
            }

            // Apply to new product
            $newProduct = $item->product;
            if ($newProduct && $newProduct->is_track_stock) {
                $newProduct->decrement('stock', (float) $item->quantity);
                $newProduct->increment('stock', (float) $item->returned_quantity);
            }
        } else {
            $product = $item->product;
            if ($product && $product->is_track_stock) {
                // Calculate difference for quantity (taken)
                $qtyDiff = (float) $item->quantity - (float) $item->getOriginal('quantity');
                $product->decrement('stock', $qtyDiff);

                // Calculate difference for returned quantity
                $returnDiff = (float) $item->returned_quantity - (float) $item->getOriginal('returned_quantity');
                $product->increment('stock', $returnDiff);
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
            // Revert everything: add back what was taken, subtract what was returned
            $product->increment('stock', (float) $item->quantity);
            $product->decrement('stock', (float) $item->returned_quantity);
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
            $product->increment('stock', (float) $item->returned_quantity);
        }
    }
}
