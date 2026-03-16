<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockEntryResource\Pages;
use App\Filament\Resources\StockEntryResource\RelationManagers;
use App\Models\StockEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockEntryResource extends Resource
{
    protected static ?string $model = StockEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Mutasi Stok';
    protected static ?string $navigationGroup = 'Produksi';
    protected static ?int $navigationSort = 61;
    protected static ?string $slug = 'mutasi-stok';
    protected static ?string $modelLabel = 'Mutasi Stok';
    protected static ?string $pluralModelLabel = 'Mutasi Stok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Mutasi Stok')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'MASUK' => 'Masuk (Stock In)',
                                'KELUAR' => 'Keluar (Stock Out)',
                                'PRODUCTION' => 'Production',
                                'ADJUSTMENT' => 'Adjustment',
                            ])
                            ->required()
                            ->default('MASUK'),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('isi', $product->isi ?? 1);
                                        }
                                    }),
                                Forms\Components\TextInput::make('isi')
                                    ->label(fn (Forms\Get $get) => 'Isi per ' . (\App\Models\Product::find($get('product_id'))?->uom ?? 'PCS') . ' / Dus')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $product = \App\Models\Product::find($get('product_id'));
                                        if ($product) {
                                            $set('isi', $product->isi ?? 1);
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity_carton')
                                    ->label('Jumlah Dus')
                                    ->required()
                                    ->default(0)
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(onBlur: true)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => SaleResource::parseNumber($state))
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotalQuantity($get, $set)),
                                Forms\Components\TextInput::make('quantity')
                                    ->label(fn (Forms\Get $get) => 'Total ' . (\App\Models\Product::find($get('product_id'))?->uom ?? 'Unit'))
                                    ->required()
                                    ->readOnly()
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => SaleResource::parseNumber($state))
                                    ->extraAttributes(['class' => 'bg-gray-100']),
                            ])
                            ->columns(5)
                            ->itemLabel(fn (array $state): ?string => (\App\Models\Product::find($state['product_id'])?->name ?? 'Item') . ' (' . ($state['quantity'] ?? 0) . ' pcs)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function updateTotalQuantity(Forms\Get $get, Forms\Set $set): void
    {
        $productId = $get('product_id');
        if (!$productId) return;

        $product = \App\Models\Product::find($productId);
        $isi = $product ? ($product->isi ?? 1) : 1;
        
        $cartons = floatval(SaleResource::parseNumber($get('quantity_carton') ?? 0));
        
        $totalQuantity = ($cartons * $isi);
        
        $set('quantity', $totalQuantity);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockEntries::route('/'),
            'create' => Pages\CreateStockEntry::route('/create'),
            'edit' => Pages\EditStockEntry::route('/{record}/edit'),
        ];
    }
}
