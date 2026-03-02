<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReportResource\Pages;
use App\Filament\Resources\SalesReportResource\RelationManagers;
use App\Models\SalesReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesReportResource extends Resource
{
    protected static ?string $model = \App\Models\Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 41;
    protected static ?string $slug = 'laporan-penjualan';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'warning',
                        'Dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('from_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                        );
                    }),
                Tables\Filters\Filter::make('to_date')
                    ->form([
                        Forms\Components\DatePicker::make('to')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['to'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                        );
                    }),
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($livewire) => route('sales.report.print', [
                        'from' => $livewire->tableFilters['date']['from'] ?? null,
                        'to' => $livewire->tableFilters['date']['to'] ?? null,
                        'customer' => $livewire->tableFilters['customer']['value'] ?? null,
                    ]))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSalesReports::route('/'),
        ];
    }
}
