<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\StockEntryItem::observe(\App\Observers\StockEntryItemObserver::class);
        \App\Models\SaleItem::observe(\App\Observers\SaleItemObserver::class);
        \App\Models\PurchaseItem::observe(\App\Observers\PurchaseItemObserver::class);
    }
}
