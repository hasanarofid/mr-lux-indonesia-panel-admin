<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehousePickupResource\Pages;
use App\Models\WarehousePickup;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\RawJs;

class WarehousePickupResource extends Resource
{
    protected static ?string $model = WarehousePickup::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Pengambilan Gudang';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 35;
    protected static ?string $slug = 'pengambilan-gudang';
    protected static ?string $modelLabel = 'Pengambilan Gudang';
    protected static ?string $pluralModelLabel = 'Pengambilan Gudang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengambilan')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor Dokumen')
                            ->default('WHP/' . date('Ym') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'invoice' => 'Invoice',
                                'manual' => 'Barang Dibawa',
                            ])
                            ->default('manual')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'manual') {
                                    $set('sale_id', null);
                                }
                            }),
                        Forms\Components\Select::make('sale_id')
                            ->label('Cari Invoice')
                            ->relationship('sale', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'invoice')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $sale = \App\Models\Sale::with('items.product')->find($state);
                                    if ($sale) {
                                        $items = $sale->items->map(function ($item) {
                                            return [
                                                'product_id' => $item->product_id,
                                                'unit' => $item->unit,
                                                'quantity' => $item->quantity,
                                                'returned_quantity' => 0,
                                            ];
                                        })->toArray();
                                        $set('items', $items);
                                        
                                        // Also set driver name from customer if applicable or leave blank
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('driver_name')
                            ->label('Nama Sales / Sopir')
                            ->required(),
                        Forms\Components\TextInput::make('vehicle_number')
                            ->label('Plat Nomor'),
                        Forms\Components\Textarea::make('note')
                            ->label('Alamat / Catatan')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item Barang')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name', fn (Builder $query) => $query->where('is_track_stock', true))
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $stock = number_format($record->stock, 0, ',', '.');
                                        return "{$record->sku} - {$record->name} (Stok: {$stock})";
                                    })
                                    ->searchable(['name', 'sku'])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit', strtoupper(Product::find($state)?->uom ?? 'PCS'))),
                                Forms\Components\Select::make('unit')
                                    ->label('Satuan')
                                    ->options([
                                        'PCS' => 'PCS',
                                        'SET' => 'SET',
                                        'DUS' => 'DUS',
                                    ])
                                    ->required()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah Diambil')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1),
                            ])->columns(3)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'invoice' => 'info',
                        'manual' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'invoice' => 'Invoice',
                        'manual' => 'Barang Dibawa',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->label('Invoice')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver_name')
                    ->label('Sopir/Sales')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Plat No')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'invoice' => 'Invoice',
                        'manual' => 'Barang Dibawa',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehousePickups::route('/'),
            'create' => Pages\CreateWarehousePickup::route('/create'),
            'edit' => Pages\EditWarehousePickup::route('/{record}/edit'),
        ];
    }
}
