<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class StockReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?string $navigationLabel = 'Laporan Stok';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 42;
    protected static ?string $slug = 'laporan-stok';
    protected static string $view = 'filament.pages.stock-report';
    protected static ?string $title = 'Laporan Stok';
}
