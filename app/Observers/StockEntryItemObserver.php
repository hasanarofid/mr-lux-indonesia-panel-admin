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
        $type = $stockEntryItem->stockEntry->type;

        if (in_array($type, ['MASUK', 'PRODUCTION'])) {
            $product->increment('stock', $stockEntryItem->quantity);
        } elseif ($type === 'KELUAR') {
            $product->decrement('stock', $stockEntryItem->quantity);
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
            if ($oldProduct) {
                if (in_array($type, ['MASUK', 'PRODUCTION'])) {
                    $oldProduct->decrement('stock', $stockEntryItem->getOriginal('quantity'));
                } elseif ($type === 'KELUAR') {
                    $oldProduct->increment('stock', $stockEntryItem->getOriginal('quantity'));
                }
            }

            // Apply to new product
            $newProduct = $stockEntryItem->product;
            if ($newProduct) {
                if (in_array($type, ['MASUK', 'PRODUCTION'])) {
                    $newProduct->increment('stock', $stockEntryItem->quantity);
                } elseif ($type === 'KELUAR') {
                    $newProduct->decrement('stock', $stockEntryItem->quantity);
                }
            }
        } else {
            $product = $stockEntryItem->product;
            $difference = $stockEntryItem->quantity - $stockEntryItem->getOriginal('quantity');

            if ($product) {
                if (in_array($type, ['MASUK', 'PRODUCTION'])) {
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
        $type = $stockEntryItem->stockEntry->type;

        if ($product) {
            if (in_array($type, ['MASUK', 'PRODUCTION'])) {
                $product->decrement('stock', $stockEntryItem->quantity);
            } elseif ($type === 'KELUAR') {
                $product->increment('stock', $stockEntryItem->quantity);
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
