<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Pembelian';
    protected static ?string $slug = 'pembelian';
    protected static ?string $modelLabel = 'Pembelian';
    protected static ?string $pluralModelLabel = 'Pembelian';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pembelian')
                    ->schema([
                        Forms\Components\TextInput::make('supplier_name')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
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
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('cost', $product->price);
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('cost')
                                    ->label('Biaya/Unit')
                                    ->required()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(debounce: 500)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn ($state) => self::formatMoney($state)),
                            ])
                            ->columns(4)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                    ]),

                Forms\Components\Section::make('Ringkasan')
                    ->schema([
                        Forms\Components\TextInput::make('total')
                            ->readOnly()
                            ->prefix('Rp')
                            ->formatStateUsing(fn ($state) => self::formatMoney($state)),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function formatMoney($value): string
    {
        return number_format(self::parseNumber($value), 0, ',', '.');
    }

    public static function parseNumber($value): float
    {
        if (is_null($value) || $value === '') {
            return 0;
        }

        $value = (string)$value;
        $value = str_replace(['Rp', ' '], '', $value);

        // Strip everything except digits
        $cleanDigits = preg_replace('/[^0-9]/', '', $value);
        
        return $cleanDigits !== '' ? (float) $cleanDigits : 0;
    }

    public static function updateItemSubtotal(Forms\Get $get, Forms\Set $set): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $cost = self::parseNumber($get('cost') ?? 0);

        $subtotal = round($quantity * $cost);
        $set('subtotal', self::formatMoney($subtotal));
        
        self::calculateTotals($get, $set);
    }

    public static function calculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $items = collect($get('items') ?? []);
        $total = $items->sum(fn($item) => self::parseNumber($item['subtotal'] ?? 0));

        $set('total', self::formatMoney($total));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Nama Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
