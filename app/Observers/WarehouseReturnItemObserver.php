<?php

namespace App\Observers;

use App\Models\WarehouseReturnItem;

class WarehouseReturnItemObserver
{
    /**
     * Handle the WarehouseReturnItem "created" event.
     */
    public function created(WarehouseReturnItem $item): void
    {
        $product = $item->product;

        if ($product && $product->is_track_stock) {
            $product->decrement('stock', $item->getConvertedQuantity());
        }
    }

    /**
     * Handle the WarehouseReturnItem "updated" event.
     */
    public function updated(WarehouseReturnItem $item): void
    {
        if ($item->isDirty('product_id')) {
            $oldProduct = \App\Models\Product::withTrashed()->find($item->getOriginal('product_id'));
            $originalItem = new WarehouseReturnItem([
                'product_id' => $item->getOriginal('product_id'),
                'quantity' => $item->getOriginal('quantity'),
                'unit' => $item->getOriginal('unit'),
            ]);

            // Revert old product stock based on original values (which was decremented, so we increment)
            if ($oldProduct && $oldProduct->is_track_stock) {
                $oldProduct->increment('stock', $originalItem->getConvertedQuantity());
            }

            $newProduct = $item->product;
            if ($newProduct && $newProduct->is_track_stock) {
                $newProduct->decrement('stock', $item->getConvertedQuantity());
            }
        } elseif ($item->isDirty('quantity') || $item->isDirty('unit')) {
            $product = $item->product;

            if ($product && $product->is_track_stock) {
                $originalItem = new WarehouseReturnItem([
                    'product_id' => $item->product_id,
                    'quantity' => $item->getOriginal('quantity'),
                    'unit' => $item->getOriginal('unit'),
                ]);
                $difference = $item->getConvertedQuantity() - $originalItem->getConvertedQuantity();
                $product->decrement('stock', $difference);
            }
        }
    }

    /**
     * Handle the WarehouseReturnItem "deleted" event.
     */
    public function deleted(WarehouseReturnItem $item): void
    {
        $product = \App\Models\Product::withTrashed()->find($item->product_id);

        if ($product && $product->is_track_stock) {
            $product->increment('stock', $item->getConvertedQuantity());
        }
    }

    /**
     * Handle the WarehouseReturnItem "restored" event.
     */
    public function restored(WarehouseReturnItem $item): void
    {
        //
    }

    /**
     * Handle the WarehouseReturnItem "force deleted" event.
     */
    public function forceDeleted(WarehouseReturnItem $item): void
    {
        //
    }
}
