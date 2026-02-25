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
        $product->decrement('stock', $saleItem->quantity);
    }

    /**
     * Handle the SaleItem "updated" event.
     */
    public function updated(SaleItem $saleItem): void
    {
        $difference = $saleItem->quantity - $saleItem->getOriginal('quantity');
        $product = $saleItem->product;
        $product->decrement('stock', $difference);
    }

    /**
     * Handle the SaleItem "deleted" event.
     */
    public function deleted(SaleItem $saleItem): void
    {
        $product = $saleItem->product;
        $product->increment('stock', $saleItem->quantity);
    }

    /**
     * Handle the SaleItem "restored" event.
     */
    public function restored(SaleItem $saleItem): void
    {
        //
    }

    /**
     * Handle the SaleItem "force deleted" event.
     */
    public function forceDeleted(SaleItem $saleItem): void
    {
        //
    }
}
