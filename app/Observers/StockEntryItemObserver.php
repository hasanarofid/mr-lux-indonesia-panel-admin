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
        $product->increment('stock', $stockEntryItem->quantity);
    }

    /**
     * Handle the StockEntryItem "updated" event.
     */
    public function updated(StockEntryItem $stockEntryItem): void
    {
        $difference = $stockEntryItem->quantity - $stockEntryItem->getOriginal('quantity');
        $product = $stockEntryItem->product;
        $product->increment('stock', $difference);
    }

    /**
     * Handle the StockEntryItem "deleted" event.
     */
    public function deleted(StockEntryItem $stockEntryItem): void
    {
        $product = $stockEntryItem->product;
        $product->decrement('stock', $stockEntryItem->quantity);
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
