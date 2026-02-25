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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sale Information')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
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
                            ->default('INV/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->default(now())
                            ->required(),
                        Forms\Components\Toggle::make('is_ppn')
                            ->label('Include PPN (11%)')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                    ])->columns(2),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
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
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('discount_item')
                                    ->label('Disc/Item')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateItemSubtotal($get, $set)),
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->required()
                                    ->readOnly()
                                    ->prefix('Rp'),
                            ])
                            ->columns(5)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                    ]),

                Forms\Components\Section::make('Summary')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('discount_invoice')
                            ->label('Discount Invoice')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotals($get, $set)),
                        Forms\Components\TextInput::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('grand_total')
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp'),
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function updateItemSubtotal(Forms\Get $get, Forms\Set $set): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $price = floatval($get('price') ?? 0);
        $discount = floatval($get('discount_item') ?? 0);

        $subtotal = $quantity * ($price - $discount);
        $set('subtotal', $subtotal);
    }

    public static function calculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $items = collect($get('items') ?? []);
        $subtotal = $items->sum('subtotal');
        $discountInvoice = floatval($get('discount_invoice') ?? 0);
        
        $baseTotal = $subtotal - $discountInvoice;
        $ppnAmount = $get('is_ppn') ? ($baseTotal * 0.11) : 0;
        $grandTotal = $baseTotal + $ppnAmount;

        $set('subtotal', $subtotal);
        $set('ppn_amount', $ppnAmount);
        $set('grand_total', $grandTotal);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_item_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_invoice')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_ppn')
                    ->boolean(),
                Tables\Columns\TextColumn::make('ppn_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
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
                    ->label('Print Nota')
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
