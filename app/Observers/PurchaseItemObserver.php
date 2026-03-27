<?php

namespace App\Observers;

use App\Models\PurchaseItem;

class PurchaseItemObserver
{
    /**
     * Handle the PurchaseItem "created" event.
     */
    public function created(PurchaseItem $purchaseItem): void
    {
        $product = $purchaseItem->product;
        if ($product && $product->is_track_stock) {
            $product->increment('stock', (float) $purchaseItem->quantity);
        }
    }

    /**
     * Handle the PurchaseItem "updated" event.
     */
    public function updated(PurchaseItem $purchaseItem): void
    {
        $product = $purchaseItem->product;
        if ($product && $product->is_track_stock) {
            $difference = (float) $purchaseItem->quantity - (float) $purchaseItem->getOriginal('quantity');
            $product->increment('stock', $difference);
        }
    }

    /**
     * Handle the PurchaseItem "deleted" event.
     */
    public function deleted(PurchaseItem $purchaseItem): void
    {
        $product = $purchaseItem->product;
        if ($product && $product->is_track_stock) {
            $product->decrement('stock', (float) $purchaseItem->quantity);
        }
    }

    /**
     * Handle the PurchaseItem "restored" event.
     */
    public function restored(PurchaseItem $purchaseItem): void
    {
        //
    }

    /**
     * Handle the PurchaseItem "force deleted" event.
     */
    public function forceDeleted(PurchaseItem $purchaseItem): void
    {
        //
    }
}
