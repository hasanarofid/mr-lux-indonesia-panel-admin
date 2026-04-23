<?php

namespace App\Observers;

use App\Models\StockEntryItem;

class StockEntryItemObserver
{
    /**
     * Handle the StockEntryItem "created" event.
     */
    public function created(StockEntryItem $stockEntryItem): void
    {
        $product = $stockEntryItem->product;
        if (!$product || !$product->is_track_stock) {
            return;
        }

        $type = $stockEntryItem->stockEntry->type;

        if (in_array($type, ['MASUK', 'PRODUCTION', 'ADJUSTMENT'])) {
            $product->increment('stock', (float) $stockEntryItem->quantity);
        } elseif ($type === 'KELUAR') {
            $product->decrement('stock', (float) $stockEntryItem->quantity);
        }
    }

    /**
     * Handle the StockEntryItem "updated" event.
     */
    public function updated(StockEntryItem $stockEntryItem): void
    {
        $type = $stockEntryItem->stockEntry->type;

        if ($stockEntryItem->isDirty('product_id')) {
            // Revert old product
            $oldProduct = \App\Models\Product::withTrashed()->find($stockEntryItem->getOriginal('product_id'));
            if ($oldProduct && $oldProduct->is_track_stock) {
                if (in_array($type, ['MASUK', 'PRODUCTION', 'ADJUSTMENT'])) {
                    $oldProduct->decrement('stock', (float) $stockEntryItem->getOriginal('quantity'));
                } elseif ($type === 'KELUAR') {
                    $oldProduct->increment('stock', (float) $stockEntryItem->getOriginal('quantity'));
                }
            }

            // Apply to new product
            $newProduct = $stockEntryItem->product;
            if ($newProduct && $newProduct->is_track_stock) {
                if (in_array($type, ['MASUK', 'PRODUCTION', 'ADJUSTMENT'])) {
                    $newProduct->increment('stock', (float) $stockEntryItem->quantity);
                } elseif ($type === 'KELUAR') {
                    $newProduct->decrement('stock', (float) $stockEntryItem->quantity);
                }
            }
        } else {
            $product = $stockEntryItem->product;
            if ($product && $product->is_track_stock) {
                $difference = (float) $stockEntryItem->quantity - (float) $stockEntryItem->getOriginal('quantity');
                if (in_array($type, ['MASUK', 'PRODUCTION', 'ADJUSTMENT'])) {
                    $product->increment('stock', $difference);
                } elseif ($type === 'KELUAR') {
                    $product->decrement('stock', $difference);
                }
            }
        }
    }

    /**
     * Handle the StockEntryItem "deleted" event.
     */
    public function deleted(StockEntryItem $stockEntryItem): void
    {
        $product = \App\Models\Product::withTrashed()->find($stockEntryItem->product_id);
        if ($product && $product->is_track_stock) {
            $type = $stockEntryItem->stockEntry->type;
            if (in_array($type, ['MASUK', 'PRODUCTION', 'ADJUSTMENT'])) {
                $product->decrement('stock', (float) $stockEntryItem->quantity);
            } elseif ($type === 'KELUAR') {
                $product->increment('stock', (float) $stockEntryItem->quantity);
            }
        }
    }

    /**
     * Handle the StockEntryItem "restored" event.
     */
    public function restored(StockEntryItem $stockEntryItem): void
    {
        //
    }

    /**
     * Handle the StockEntryItem "force deleted" event.
     */
    public function forceDeleted(StockEntryItem $stockEntryItem): void
    {
        //
    }
}
