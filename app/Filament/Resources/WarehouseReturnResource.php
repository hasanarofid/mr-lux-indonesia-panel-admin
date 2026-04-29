<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseReturnResource\Pages;
use App\Models\WarehouseReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;

class WarehouseReturnResource extends Resource
{
    protected static ?string $model = WarehouseReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Retur Gudang';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 37;
    protected static ?string $slug = 'retur-gudang';
    protected static ?string $modelLabel = 'Retur Gudang';
    protected static ?string $pluralModelLabel = 'Retur Gudang';
    protected static ?string $recordTitleAttribute = 'return_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Retur')
                    ->schema([
                        Forms\Components\TextInput::make('return_number')
                            ->label('Nomor Retur')
                            ->default('RG-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Retur')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item Retur')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->allowHtml()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $stock = number_format($record->stock, 0, ',', '.');
                                        $dus = $record->isi > 0 ? floor($record->stock / $record->isi) : 0;
                                        $stockInfo = "Stok: {$dus} Dus ({$stock} {$record->uom})";
                                        
                                        return "
                                            <div>
                                                <div class='font-medium text-sm'>{$record->sku} - {$record->name}</div>
                                                <div class='text-xs opacity-70'>{$stockInfo}</div>
                                            </div>
                                        ";
                                    })
                                    ->searchable(['name', 'sku'])
                                    ->required()
                                    ->live()
                                    ->columnSpan(4)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $unit = $product->uom ?? 'PCS';
                                            $set('unit', $unit);
                                            $set('quantity', 1);
                                        }
                                    }),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->datalist(['PCS', 'DUS', 'SET', 'KG'])
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->default(1)
                                    ->numeric()
                                    ->columnSpan(2),
                            ])
                            ->columns(8)
                            ->defaultItems(1)
                            ->extraAttributes([
                                'onkeydown' => "if (event.key === 'Enter') { event.preventDefault(); return false; }",
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('return_number')
                    ->label('Nomor Retur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Total Item')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseReturns::route('/'),
            'create' => Pages\CreateWarehouseReturn::route('/create'),
            'view' => Pages\ViewWarehouseReturn::route('/{record}'),
            'edit' => Pages\EditWarehouseReturn::route('/{record}/edit'),
        ];
    }
}
