<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualDeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManualDeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Surat Jalan Manual';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 33;
    protected static ?string $slug = 'surat-jalan-manual';
    protected static ?string $modelLabel = 'Surat Jalan Manual';
    protected static ?string $pluralModelLabel = 'Surat Jalan Manual';
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengiriman')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default('MANUAL'),
                        Forms\Components\Select::make('sale_id')
                            ->label('Nomor Invoice (Opsional)')
                            ->relationship('sale', 'invoice_number')
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $sale = \App\Models\Sale::with('items.product')->find($state);
                                    if ($sale) {
                                        $set('customer_id', $sale->customer_id);
                                        $items = collect($sale->items)->map(fn ($item) => [
                                            'product_id' => $item->product_id,
                                            'unit' => $item->unit,
                                            'quantity' => $item->quantity,
                                        ])->toArray();
                                        $set('items', $items);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor SJ')
                            ->default(fn () => 'SJM/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending',
                                'SHIPPED' => 'Shipped',
                                'DELIVERED' => 'Delivered',
                            ])
                            ->required()
                            ->default('PENDING'),
                        Forms\Components\TextInput::make('driver_name')
                            ->label('Nama Sopir')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('vehicle_number')
                            ->label('Nomor Kendaraan')
                            ->maxLength(255),
                    ])->columns(2)
                    ->disabled(fn (?DeliveryNote $record) => $record?->sale_id !== null),

                Forms\Components\Section::make('Item Barang')
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
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            $set('unit', $product?->uom ?? 'PCS');
                                        }
                                    })
                                    ->columnSpan(4),
                                Forms\Components\Select::make('unit')
                                    ->label('Satuan')
                                    ->options([
                                        'PCS' => 'PCS',
                                        'Dus' => 'Dus',
                                        'Set' => 'Set',
                                    ])
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(8)
                            ->defaultItems(1)
                            ->reorderable(false),
                    ])
                    ->disabled(fn (?DeliveryNote $record) => $record?->sale_id !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->label('Nomor Invoice')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->label('Nomor SJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver_name')
                    ->label('Nama Sopir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Nomor Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'danger',
                        'SHIPPED' => 'warning', // filament uses warning for orange/yellow
                        'DELIVERED' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('print')
                    ->label('Cetak SJ')
                    ->icon('heroicon-o-printer')
                    ->url(fn (DeliveryNote $record): string => route('delivery-notes.print', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn (DeliveryNote $record) => $record->status === 'DELIVERED'),
                DeleteAction::make()
                    ->hidden(fn (DeliveryNote $record) => $record->status === 'DELIVERED'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'MANUAL')
            ->with(['sale', 'customer', 'items.product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManualDeliveryNotes::route('/'),
            'create' => Pages\CreateManualDeliveryNote::route('/create'),
            'edit' => Pages\EditManualDeliveryNote::route('/{record}/edit'),
        ];
    }
}
