<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'produk';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Produk')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->datalist(fn () => \App\Models\Product::query()->whereNotNull('category')->distinct()->pluck('category')->toArray())
                            ->required(),
                        Forms\Components\Toggle::make('is_track_stock')
                            ->label('Lacak Stok')
                            ->default(true)
                            ->live(),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU/Kode')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('uom')
                            ->label('Satuan')
                            ->options([
                                'PCS' => 'PCS',
                                'SET' => 'SET',
                                'KG' => 'KG',
                            ])
                            ->required()
                            ->default('PCS')
                            ->live(),
                        Forms\Components\TextInput::make('isi')
                            ->label(fn (Forms\Get $get) => 'Isi per ' . ($get('uom') ?? 'PCS') . ' / Dus')
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                            ->default(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateCartonPrice($get, $set)),
                        Forms\Components\TextInput::make('isi_set')
                            ->label('Units per Set (Isi)')
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                            ->default(1)
                            ->required()
                            ->hidden(fn (Forms\Get $get) => in_array($get('uom'), ['PCS', 'SET', 'KG'])),
                        Forms\Components\TextInput::make('price')
                            ->label(fn (Forms\Get $get) => match ($get('uom')) {
                                'PCS' => 'Harga per PCS',
                                'SET' => 'Harga per Set',
                                'KG' => 'Harga per Kg',
                                default => 'Harga per PCS',
                            })
                             ->required()
                            ->prefix('Rp')
                            ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                            ->stripCharacters('.')
                            ->live(onBlur: true)
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                            ->dehydrateStateUsing(fn ($state) => SaleResource::parseNumber($state))
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateCartonPrice($get, $set))
                            ->default(0),
                        Forms\Components\TextInput::make('price_per_carton')
                            ->label('Harga per Dus')
                            ->prefix('Rp')
                            ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                            ->stripCharacters('.')
                            ->live(onBlur: true)
                            ->readOnly()
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                            ->dehydrateStateUsing(fn ($state) => SaleResource::parseNumber($state))
                            ->default(0),
                        Forms\Components\TextInput::make('price_per_set')
                            ->label('Harga per Set')
                            ->prefix('Rp')
                            ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                            ->stripCharacters('.')
                            ->live(onBlur: true)
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                            ->dehydrateStateUsing(fn ($state) => SaleResource::parseNumber($state))
                            ->default(0)
                            ->hidden(fn (Forms\Get $get) => in_array($get('uom'), ['PCS', 'SET', 'KG'])),
                        Forms\Components\TextInput::make('stock')
                            ->label(fn (Forms\Get $get) => 'Total Stok (' . ($get('uom') ?? 'PCS') . ')')
                            ->required()
                            ->default(0)
                            ->disabled()
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
    
    public static function calculateCartonPrice(Forms\Get $get, Forms\Set $set): void
    {
        $isi = (float) ($get('isi') ?? 0);
        $price = SaleResource::parseNumber($get('price') ?? 0);
        
        $pricePerCarton = round($isi * $price);
        
        $set('price_per_carton', number_format($pricePerCarton, 0, ',', '.'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_track_stock')
                    ->label('Lacak Stok')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('uom')
                    ->label('Satuan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('isi')
                    ->label('Isi/Dus')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->color(fn (Product $record): string => $record->price == 0 ? 'danger' : 'success')
                    ->weight(fn (Product $record) => $record->price == 0 ? 'bold' : 'normal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dus')
                    ->label('Dus')
                    ->getStateUsing(fn (Product $record) => $record->isi > 0 ? floor($record->stock / $record->isi) : 0)
                    ->numeric(decimalPlaces: 0),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric(decimalPlaces: 0)
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view_stock_report')
                    ->label('Lihat Laporan Stok')
                    ->url(fn (): string => ProductResource::getUrl('stock-report'))
                    ->icon('heroicon-o-chart-bar')
                    ->color('info'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'stock-report' => Pages\StockReport::route('/stock-report'),
        ];
    }
}
