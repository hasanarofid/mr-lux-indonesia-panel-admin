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

    protected static ?string $navigationIcon = 'heroicon-o-truck';
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
                        Forms\Components\TextInput::make('driver_name')
                            ->label('Nama Sales / Sopir')
                            ->required(),
                        Forms\Components\TextInput::make('vehicle_number')
                            ->label('Plat Nomor'),
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat / Tujuan'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'picked_up' => 'Barang Dibawa',
                                'returned' => 'Ada Pengembalian',
                                'completed' => 'Selesai / Terjual Semua',
                            ])
                            ->default('picked_up')
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('note')
                            ->label('Alasan / Catatan')
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
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit', Product::find($state)?->uom ?? 'PCS')),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->readOnly()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah Diambil')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1),
                                Forms\Components\TextInput::make('returned_quantity')
                                    ->label('Jumlah Kembali')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Forms\Get $get) => $get('../../status') !== 'picked_up')
                                    ->hint('Jumlah barang yang dikembalikan ke gudang'),
                            ])->columns(4)
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
                Tables\Columns\TextColumn::make('driver_name')
                    ->label('Sopir/Sales')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Plat No')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'picked_up' => 'warning',
                        'returned' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'picked_up' => 'Barang Dibawa',
                        'returned' => 'Ada Pengembalian',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'picked_up' => 'Barang Dibawa',
                        'returned' => 'Ada Pengembalian',
                        'completed' => 'Selesai',
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
