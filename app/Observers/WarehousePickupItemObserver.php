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
            $product->decrement('stock', $item->getConvertedQuantity());
        }
    }

    /**
     * Handle the WarehousePickupItem "updated" event.
     */
    public function updated(WarehousePickupItem $item): void
    {
        if ($item->isDirty(['product_id', 'quantity', 'unit'])) {
            // Revert old product stock based on original values
            $oldProductId = $item->getOriginal('product_id');
            $oldProduct = Product::withTrashed()->find($oldProductId);
            
            if ($oldProduct && $oldProduct->is_track_stock) {
                // Create temp item to calculate original converted quantity
                $originalItem = clone $item;
                $originalItem->product_id = $oldProductId;
                $originalItem->quantity = $item->getOriginal('quantity');
                $originalItem->unit = $item->getOriginal('unit');
                
                $oldProduct->increment('stock', $originalItem->getConvertedQuantity());
            }

            // Apply to new product/quantity/unit
            $newProduct = $item->product;
            if ($newProduct && $newProduct->is_track_stock) {
                $newProduct->decrement('stock', $item->getConvertedQuantity());
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
            $product->increment('stock', $item->getConvertedQuantity());
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
