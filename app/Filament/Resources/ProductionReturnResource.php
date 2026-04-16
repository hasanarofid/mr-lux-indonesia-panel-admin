<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionReturnResource\Pages;
use App\Models\ProductionReturn;
use App\Models\WarehousePickup;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductionReturnResource extends Resource
{
    protected static ?string $model = ProductionReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Retur Produksi';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 36;
    protected static ?string $slug = 'retur-produksi';
    protected static ?string $modelLabel = 'Retur Produksi';
    protected static ?string $pluralModelLabel = 'Retur Produksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Retur')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor Retur')
                            ->default(function () {
                                $prefix = 'RE-PRD/' . date('Ym') . '/';
                                $last = ProductionReturn::where('number', 'like', $prefix . '%')
                                    ->orderBy('number', 'desc')
                                    ->first();
                                
                                $nextNumber = $last ? (int) substr($last->number, -3) + 1 : 1;
                                return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                            })
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Retur')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\Select::make('warehouse_pickup_id')
                            ->label('Nomor Pengambilan')
                            ->relationship('warehousePickup', 'number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $pickup = WarehousePickup::find($state);
                                    if ($pickup) {
                                        $set('driver_name', $pickup->driver_name);
                                        $set('vehicle_number', $pickup->vehicle_number);
                                    }
                                }
                            }),
                        Forms\Components\Checkbox::make('is_represented_by_warehouse')
                            ->label('Diwakilkan Gudang')
                            ->columnSpan('full'),
                        Forms\Components\TextInput::make('driver_name')
                            ->label('Nama Sales / Sopir')
                            ->readOnly()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('vehicle_number')
                            ->label('Plat Nomor')
                            ->readOnly()
                            ->dehydrated(),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item Barang Retur')
                    ->description('Isi catatan barang yang kembali untuk masing-masing kategori.')
                    ->schema([
                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\Textarea::make('epoxy')
                                    ->label('Epoxy')
                                    ->rows(10)
                                    ->placeholder('Input manual...'),
                                Forms\Components\Textarea::make('pu')
                                    ->label('PU')
                                    ->rows(10)
                                    ->placeholder('Input manual...'),
                                Forms\Components\Textarea::make('non_sag_alifatik')
                                    ->label('Non Sag / Alifatik')
                                    ->rows(10)
                                    ->placeholder('Input manual...'),
                                Forms\Components\Textarea::make('lem_putih')
                                    ->label('Lem Putih')
                                    ->rows(10)
                                    ->placeholder('Input manual...'),
                                Forms\Components\Textarea::make('alteco')
                                    ->label('Alteco')
                                    ->rows(10)
                                    ->placeholder('Input manual...'),
                            ]),
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
                    ->label('Tanggal Retur')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehousePickup.number')
                    ->label('Ref. Pengambilan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver_name')
                    ->label('Sopir/Sales')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Plat No')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_represented_by_warehouse')
                    ->label('Gudang')
                    ->boolean(),
                Tables\Columns\TextColumn::make('epoxy')
                    ->label('Epoxy')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pu')
                    ->label('PU')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('is_represented_by_warehouse')
                    ->label('Diwakilkan Gudang'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionReturns::route('/'),
            'create' => Pages\CreateProductionReturn::route('/create'),
            'view' => Pages\ViewProductionReturn::route('/{record}'),
            'edit' => Pages\EditProductionReturn::route('/{record}/edit'),
        ];
    }
}
