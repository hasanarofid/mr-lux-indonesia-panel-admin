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
    protected static ?int $navigationSort = 31;
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
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $customer = \App\Models\Customer::find($state);
                                if ($customer) {
                                    $set('is_ppn', $customer->group === 'PPN');
                                }
                                self::calculateTotals($get, $set);
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
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Belum Lunas' => 'Belum Lunas',
                                'Lunas' => 'Lunas',
                                'Dibatalkan' => 'Dibatalkan',
                            ])
                            ->default('Belum Lunas')
                            ->required(),
                        Forms\Components\Toggle::make('is_ppn')
                            ->label('Include PPN (11%)')
                            ->live()
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                    ])->columns(2),

                Forms\Components\Section::make('Item')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->sku} - {$record->name}")
                                    ->searchable(['name', 'sku'])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('price', $product->price);
                                            $set('quantity', 1);
                                        }
                                        self::updateItemSubtotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live()
                                    ->extraInputAttributes(['onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57'])
                                    ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(debounce: 500)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        self::updateItemSubtotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('discount_percent')
                                    ->label('Diskon %')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->extraInputAttributes(['onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46'])
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $price = self::parseNumber($get('price') ?? 0);
                                        $discountNominal = round($price * (self::parseNumber($state) / 100));
                                        $set('discount_item', self::formatMoney($discountNominal));
                                        self::updateItemSubtotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('discount_item')
                                    ->label('Diskon Rp')
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(debounce: 500)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $price = self::parseNumber($get('price') ?? 0);
                                        $nominal = self::parseNumber($state ?? 0);
                                        if ($price > 0) {
                                            $set('discount_percent', round(($nominal / $price) * 100, 2));
                                        }
                                        $set('discount_item', self::formatMoney($nominal));
                                        self::updateItemSubtotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn ($state) => self::formatMoney($state)),
                            ])
                            ->columns(6)
                            ->live()
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set))
                            ->extraAttributes([
                                'onkeydown' => "if (event.key === 'Enter') { event.preventDefault(); return false; }",
                            ]),
                    ]),

                Forms\Components\Section::make('Ringkasan')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->readOnly()
                            ->prefix('Rp')
                            ->formatStateUsing(fn ($state) => self::formatMoney($state))
                            ->afterStateHydrated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('discount_invoice_percent')
                                    ->label('Diskon %')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->extraInputAttributes(['onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46'])
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $subtotal = self::parseNumber($get('subtotal') ?? 0);
                                        $discountNominal = round($subtotal * (self::parseNumber($state) / 100));
                                        $set('discount_invoice', self::formatMoney($discountNominal));
                                        self::calculateTotals($get, $set);
                                    }),
                                Forms\Components\TextInput::make('discount_invoice')
                                    ->label('Diskon Rp')
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(debounce: 500)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $subtotal = self::parseNumber($get('subtotal') ?? 0);
                                        $nominal = self::parseNumber($state ?? 0);
                                        if ($subtotal > 0) {
                                            $set('discount_invoice_percent', round(($nominal / $subtotal) * 100, 2));
                                        }
                                        self::calculateTotals($get, $set);
                                    }),
                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Ongkir')
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(debounce: 500)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                            ]),
                        Forms\Components\TextInput::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->readOnly()
                            ->prefix('Rp')
                            ->formatStateUsing(fn ($state) => self::formatMoney($state)),
                        Forms\Components\TextInput::make('grand_total')
                            ->readOnly()
                            ->prefix('Rp')
                            ->formatStateUsing(fn ($state) => self::formatMoney($state)),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('discount_item_total')
                            ->default(0),
                    ])->columns(2),
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
        $quantity = self::parseNumber($get('quantity') ?? 0);
        $price = self::parseNumber($get('price') ?? 0);
        $discount = self::parseNumber($get('discount_item') ?? 0);

        $subtotal = round($quantity * ($price - $discount));
        $set('subtotal', self::formatMoney($subtotal));
        
        // Explicitly trigger summary calculation at parent level
        self::calculateTotals($get, $set);
    }

    public static function calculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        // Get items. One of these will work depending on the current scope.
        $items = collect($get('items') ?? $get('../../items') ?? []);
        
        $subtotal = $items->sum(function ($item) {
            return self::parseNumber($item['subtotal'] ?? 0);
        });

        $discountItemTotal = $items->sum(function ($item) {
            $price = self::parseNumber($item['price'] ?? 0);
            $quantity = self::parseNumber($item['quantity'] ?? 0);
            $discount = self::parseNumber($item['discount_item'] ?? 0);
            return $discount * $quantity;
        });
        
        $discountInvoice = self::parseNumber($get('discount_invoice') ?? $get('../../discount_invoice') ?? 0);
        $discountInvoicePercent = self::parseNumber($get('discount_invoice_percent') ?? $get('../../discount_invoice_percent') ?? 0);
        $shippingCost = self::parseNumber($get('shipping_cost') ?? $get('../../shipping_cost') ?? 0);
        $isPpn = $get('is_ppn') ?? $get('../../is_ppn') ?? false;

        $baseTotal = $subtotal - $discountInvoice;
        $ppnAmount = $isPpn ? round($baseTotal * 0.11) : 0;
        $grandTotal = $baseTotal + $ppnAmount + $shippingCost;

        // Try setting both local and parent for summary fields.
        $isInRow = !empty($get('product_id'));

        if ($isInRow) {
            $set('../../subtotal', self::formatMoney($subtotal));
            $set('../../discount_item_total', round($discountItemTotal));
            $set('../../discount_invoice', self::formatMoney($discountInvoice));
            $set('../../ppn_amount', self::formatMoney($ppnAmount));
            $set('../../grand_total', self::formatMoney($grandTotal));
        } else {
            $set('subtotal', self::formatMoney($subtotal));
            $set('discount_item_total', round($discountItemTotal));
            $set('discount_invoice', self::formatMoney($discountInvoice));
            $set('ppn_amount', self::formatMoney($ppnAmount));
            $set('grand_total', self::formatMoney($grandTotal));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'warning',
                        'Dibatalkan' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('age')
                    ->label('Umur (hr)')
                    ->state(function (Sale $record): int {
                        if (!$record->date)
                            return 0;
                        return now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($record->date)->startOfDay(), false) * -1;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . self::formatMoney($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.category')
                    ->label('Kategori Pelanggan')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak Nota')
                    ->icon('heroicon-o-printer')
                    ->url(fn(Sale $record): string => route('sales.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer']);
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
