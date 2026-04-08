<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

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
    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penjualan')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->billing_city} ({$record->code})")
                            ->required()
                            ->searchable(['name', 'billing_city', 'code'])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $customer = \App\Models\Customer::find($state);
                                if ($customer) {
                                    $set('is_ppn', $customer->group === 'PPN');
                                }
                                self::calculateTotals($get, $set);
                            }),
                        Forms\Components\Select::make('invoice_type')
                            ->label('Jenis Invoice')
                            ->options([
                                'NORMAL' => 'Normal',
                                'SJM' => 'SJM',
                            ])
                            ->default('NORMAL')
                            ->live()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->default('INV/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Belum Lunas' => 'Belum Lunas',
                                'Lunas' => 'Lunas',
                                'Dibatalkan' => 'Dibatalkan',
                            ])
                            ->default('Belum Lunas')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Item')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name', fn (Builder $query) => $query->where('stock', '>', 0)->orWhere('is_track_stock', false))
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->sku} - {$record->name}" . ($record->is_track_stock ? " (Stok: " . number_format($record->stock, 0, ',', '.') . ")" : " (Non-Stok)"))
                                    ->searchable(['name', 'sku'])
                                    ->nullable()
                                    // ->createOptionForm([
                                    //     Forms\Components\TextInput::make('name')
                                    //         ->label('Nama Produk')
                                    //         ->required(),
                                    //     Forms\Components\TextInput::make('category')
                                    //         ->label('Kategori')
                                    //         ->default('Manual')
                                    //         ->datalist(fn () => \App\Models\Product::query()->whereNotNull('category')->distinct()->pluck('category')->toArray())
                                    //         ->required(),
                                    //     Forms\Components\Toggle::make('is_track_stock')
                                    //         ->label('Lacak Stok')
                                    //         ->default(false),
                                    //     Forms\Components\Select::make('uom')
                                    //         ->label('Satuan')
                                    //         ->options([
                                    //             'PCS' => 'PCS',
                                    //             'SET' => 'SET',
                                    //             'KG' => 'KG',
                                    //         ])
                                    //         ->default('PCS')
                                    //         ->required(),
                                    //     Forms\Components\TextInput::make('price')
                                    //         ->label('Harga Jual')
                                    //         ->prefix('Rp')
                                    //         ->numeric()
                                    //         ->required(),
                                    // ])
                                    ->rules([
                                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) {
                                            if (!$value) return;
                                            $product = \App\Models\Product::find($value);
                                            if ($product && $product->is_track_stock && $product->stock <= 0) {
                                                $fail("Stok produk ini sedang kosong.");
                                            }
                                        },
                                    ])
                                    ->live()
                                    ->columnSpan(3)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $unit = $product->uom ?? 'PCS';
                                            $set('unit', $unit);
                                            
                                            
                                            $price = match($unit) {
                                                'DUS' => $product->price_per_carton,
                                                'SET' => ($product->uom === 'SET') ? $product->price : $product->price_per_set,
                                                'KG' => $product->price,
                                                default => $product->price,
                                            };
                                            $set('price', number_format($price, 0, ',', '.'));
                                            $set('quantity', 1);
                                        }
                                        self::updateItemSubtotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('description')
                                    ->label('Keterangan / Deskripsi')
                                    ->placeholder('Isi jika ada keterangan tambahan atau manual')
                                    ->dehydrated()
                                    ->nullable()
                                    ->columnSpan(4),

                                 Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->datalist(['PCS', 'DUS', 'SET', 'KG'])
                                    ->required()
                                    ->live()
                                    ->columnSpan(1)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $product = \App\Models\Product::find($get('product_id'));
                                        if ($product) {
                                            $price = match(strtoupper($state)) {
                                                'DUS' => $product->price_per_carton,
                                                'SET' => ($product->uom === 'SET') ? $product->price : $product->price_per_set,
                                                'KG' => $product->price,
                                                default => $product->price,
                                            };
                                            $set('price', number_format($price, 0, ',', '.'));
                                        }
                                        self::updateItemSubtotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->default(1)
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(onBlur: true)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
                                    ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set))
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(onBlur: true)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        self::updateItemSubtotal($get, $set);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('discount_percent')
                                    ->label('Diskon %')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->extraInputAttributes(['onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46'])
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $price = self::parseNumber($get('price') ?? 0);
                                        $qty = self::parseNumber($get('quantity') ?? 1);
                                        $lineSubtotal = $price * $qty;
                                        
                                        $discountNominal = round($lineSubtotal * (self::parseNumber($state) / 100));
                                        $set('discount_item', self::formatMoney($discountNominal));
                                        self::updateItemSubtotal($get, $set);
                                    })
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('discount_item')
                                    ->label('Diskon Rp')
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->stripCharacters('.')
                                    ->live(onBlur: true)
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $price = self::parseNumber($get('price') ?? 0);
                                        $qty = self::parseNumber($get('quantity') ?? 1);
                                        $lineSubtotal = $price * $qty;
                                        
                                        $nominal = self::parseNumber($state ?? 0);
                                        if ($lineSubtotal > 0) {
                                            $set('discount_percent', round(($nominal / $lineSubtotal) * 100, 2));
                                        }
                                        $set('discount_item', self::formatMoney($nominal));
                                        self::updateItemSubtotal($get, $set);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn ($state) => self::formatMoney($state))
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
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
                            ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
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
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
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
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state))
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                            ]),
                        Forms\Components\TextInput::make('ppn_amount')
                                    ->label('PPN (11%)')
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn ($state) => self::formatMoney($state))
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state)),
                                Forms\Components\TextInput::make('grand_total')
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn ($state) => self::formatMoney($state))
                                    ->dehydrateStateUsing(fn ($state) => self::parseNumber($state)),
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

        // If it's already a float/int (from DB or already parsed state)
        if (is_float($value) || is_int($value)) {
            return round((float)$value);
        }

        $value = (string)$value;
        $value = str_replace(['Rp', ' '], '', $value);

        // Ambiguity check: if it's strictly a numeric string with one dot
        // and NO thousands-formatting dots (e.g. "30000.00" vs "30.000")
        if (preg_match('/^\d+\.\d+$/', $value)) {
             // If there is ONLY one dot and it's followed by NO digits, 
             // it might be a user just typed a dot.
             $parts = explode('.', $value);
             if (strlen($parts[1]) === 3) {
                  // Looks like Indonesian thousands (1.000, 20.000).
                  return (float)str_replace('.', '', $value);
             }
             
             // If it's not strictly 3 digits, it's a standard decimal (DB float).
             return (float)$value;
        }

        // For all other cases (multiple dots, or strings with dots at "wrong" places),
        // we treat ALL dots as thousands separators.
        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean); // Handle comma as decimal if any
        
        return (float)$clean;
    }

    public static function updateItemSubtotal(Forms\Get $get, Forms\Set $set): void
    {
        $quantity = self::parseNumber($get('quantity') ?? 0);
        $price = self::parseNumber($get('price') ?? 0);
        $discountLine = self::parseNumber($get('discount_item') ?? 0);

        $subtotal = round(($quantity * $price) - $discountLine);
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

        // Detect if we're inside a repeater row by checking for item-specific fields
        $isInRow = $get('price') !== null || $get('quantity') !== null;

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
                        'Belum Lunas' => 'danger',
                        'Dibatalkan' => 'gray',
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
                Tables\Columns\TextColumn::make('invoice_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NORMAL' => 'info',
                        'SJM' => 'gray',
                        default => 'gray',
                    })
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $records->each(function (Sale $record) {
                                try {
                                    $record->delete();
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title("Gagal menghapus {$record->invoice_number}")
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                }
                            });

                            Notification::make()
                                ->title('Proses hapus selesai')
                                ->success()
                                ->send();
                        }),
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
