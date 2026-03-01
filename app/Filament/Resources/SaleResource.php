<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'penjualan';
    protected static ?string $modelLabel = 'Penjualan';
    protected static ?string $pluralModelLabel = 'Penjualan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penjualan')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $customer = \App\Models\Customer::find($state);
                                if ($customer) {
                                    $set('is_ppn', $customer->group === 'PPN');
                                }
                            }),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->default('INV/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Jatuh Tempo'),
                        Forms\Components\Toggle::make('is_ppn')
                            ->label('Include PPN (11%)')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
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
                                            $set('price', $product->price);
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                                    ->stripCharacters('.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('discount_item')
                                    ->label('Diskon/Unit')
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                                    ->stripCharacters('.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input, ",", ".", 0)')),
                            ])
                            ->columns(5)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set))
                            ->extraAttributes([
                                'onkeydown' => "if (event.key === 'Enter') { event.preventDefault(); return false; }",
                            ]),
                    ]),

                Forms\Components\Section::make('Ringkasan')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->readOnly()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                            ->afterStateHydrated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                        Forms\Components\TextInput::make('discount_invoice')
                            ->label('Diskon Invoice')
                            ->default(0)
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                            ->stripCharacters('.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                        Forms\Components\TextInput::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->readOnly()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, ",", ".", 0)')),
                        Forms\Components\TextInput::make('grand_total')
                            ->readOnly()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, ",", ".", 0)')),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('discount_item_total')
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function updateItemSubtotal(Forms\Get $get, Forms\Set $set): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $price = floatval(str_replace('.', '', $get('price') ?? 0));
        $discount = floatval(str_replace('.', '', $get('discount_item') ?? 0));

        $subtotal = $quantity * ($price - $discount);
        $set('subtotal', $subtotal);
        
        // Explicitly trigger summary calculation at parent level
        self::calculateTotals($get, $set);
    }

    public static function calculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        // Get items. One of these will work depending on the current scope.
        $items = collect($get('items') ?? $get('../../items') ?? []);
        
        $subtotal = $items->sum(function ($item) {
            return floatval(str_replace('.', '', $item['subtotal'] ?? 0));
        });

        $discountItemTotal = $items->sum(function ($item) {
            $price = floatval(str_replace('.', '', $item['price'] ?? 0));
            $quantity = floatval($item['quantity'] ?? 0);
            $discount = floatval(str_replace('.', '', $item['discount_item'] ?? 0));
            return $discount * $quantity;
        });
        
        $discountInvoice = floatval(str_replace('.', '', $get('discount_invoice') ?? $get('../../discount_invoice') ?? 0));
        $isPpn = $get('is_ppn') ?? $get('../../is_ppn') ?? false;
        
        $baseTotal = $subtotal - $discountInvoice;
        $ppnAmount = $isPpn ? ($baseTotal * 0.11) : 0;
        $grandTotal = $baseTotal + $ppnAmount;

        // Try setting both local and parent for summary fields. 
        $isInRow = !empty($get('product_id'));

        if ($isInRow) {
            $set('../../subtotal', $subtotal);
            $set('../../discount_item_total', $discountItemTotal);
            $set('../../ppn_amount', $ppnAmount);
            $set('../../grand_total', $grandTotal);
        } else {
            $set('subtotal', $subtotal);
            $set('discount_item_total', $discountItemTotal);
            $set('ppn_amount', $ppnAmount);
            $set('grand_total', $grandTotal);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_item_total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_invoice')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_ppn')
                    ->boolean(),
                Tables\Columns\TextColumn::make('ppn_amount')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('IDR', locale: 'id')
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
                Tables\Actions\Action::make('print')
                    ->label('Cetak Nota')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Sale $record): string => route('sales.print', $record))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
