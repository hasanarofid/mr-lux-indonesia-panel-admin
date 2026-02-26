<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Sales', 'Rp ' . number_format(\App\Models\Sale::sum('grand_total'), 0, ',', '.'))
                ->description('Total revenue from all sales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Invoices', \App\Models\Sale::count())
                ->description('Total number of sales transactions')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total PPN', 'Rp ' . number_format(\App\Models\Sale::sum('ppn_amount'), 0, ',', '.'))
                ->description('Total tax collected')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('warning'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Products', \App\Models\Product::count())
                ->description('Total items in inventory')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Customers', \App\Models\Customer::count())
                ->description('Total registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('indigo'),
        ];
    }
}
