<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Penjualan', 'Rp ' . number_format(\App\Models\Sale::where('status', 'Lunas')->sum('grand_total'), 0, ',', '.'))
                ->description('Total pendapatan dari semua penjualan (Lunas)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Invoice Belum Dibayar', \App\Models\Sale::where('status', 'Belum Lunas')->count())
                ->description('Total invoice yang belum dibayar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Invoice', \App\Models\Sale::count())
                ->description('Total jumlah transaksi penjualan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Surat Jalan', \App\Models\DeliveryNote::count())
                ->description('Total jumlah pengiriman')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Produk', \App\Models\Product::count())
                ->description('Total item dalam inventaris')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Pelanggan', \App\Models\Customer::count())
                ->description('Total pelanggan terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('indigo'),
        ];
    }
}
