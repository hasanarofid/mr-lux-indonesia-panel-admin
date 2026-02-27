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
        $product = $stockEntryItem->product;
        $type = $stockEntryItem->stockEntry->type;
        $difference = $stockEntryItem->quantity - $stockEntryItem->getOriginal('quantity');

        if (in_array($type, ['MASUK', 'PRODUCTION'])) {
            $product->increment('stock', $difference);
        } elseif ($type === 'KELUAR') {
            $product->decrement('stock', $difference);
        }
    }

    /**
     * Handle the StockEntryItem "deleted" event.
     */
    public function deleted(StockEntryItem $stockEntryItem): void
    {
        $product = $stockEntryItem->product;
        $type = $stockEntryItem->stockEntry->type;

        if (in_array($type, ['MASUK', 'PRODUCTION'])) {
            $product->decrement('stock', $stockEntryItem->quantity);
        } elseif ($type === 'KELUAR') {
            $product->increment('stock', $stockEntryItem->quantity);
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
