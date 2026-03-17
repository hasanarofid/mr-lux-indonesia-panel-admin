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
use Filament\Support\RawJs;
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
                        Forms\Components\Select::make('sales')
                            ->label('Nomor Invoice (Opsional)')
                            ->relationship('sales', 'invoice_number', function (Builder $query, ?DeliveryNote $record) {
                                return $query->where('invoice_type', 'SJM')
                                    ->when($record, function ($query) use ($record) {
                                        return $query->orWhereIn('sales.id', $record->sales->pluck('id')->toArray());
                                    });
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if (is_array($state) && count($state) > 0) {
                                    $sales = \App\Models\Sale::with('items.product')->whereIn('id', $state)->get();
                                    if ($sales->isNotEmpty()) {
                                        // Set customer from the first sale automatically
                                        $set('customer_id', $sales->first()->customer_id);

                                        // Collect items from all selected sales without full aggregation 
                                        // if we want to show invoice number per line.
                                        // However, the user might still want to see aggregated items but with 
                                        // a reference to the sales. 
                                        // Let's go with one line per item/invoice for transparency.
                                        $items = [];
                                        foreach ($sales as $sale) {
                                            foreach ($sale->items as $item) {
                                                $items[] = [
                                                    'sale_id' => $sale->id,
                                                    'invoice_number' => $sale->invoice_number,
                                                    'customer_name' => $sale->customer?->name,
                                                    'product_id' => $item->product_id,
                                                    'unit' => strtoupper($item->unit),
                                                    'quantity' => (float)$item->quantity,
                                                ];
                                            }
                                        }
                                        $set('items', $items);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->billing_city} ({$record->code})")
                            ->required()
                            ->searchable(['name', 'billing_city', 'code']),
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
                    ->disabled(fn (?DeliveryNote $record) => $record && $record->exists && $record->status === 'DELIVERED'),

                Forms\Components\Section::make('Item Barang')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Hidden::make('sale_id'),
                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Invoice')
                                    ->afterStateHydrated(fn($state, $set, $record) => $set('invoice_number', $record?->sale?->invoice_number))
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->columnSpan(['md' => 2]),
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Customer')
                                    ->afterStateHydrated(fn($state, $set, $record) => $set('customer_name', $record?->sale?->customer?->name))
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->columnSpan(['md' => 2]),
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            $set('unit', strtoupper($product?->uom ?? 'PCS'));
                                        }
                                    })
                                    ->columnSpan(['md' => 4]),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->required()
                                    ->readOnly()
                                    ->dehydrated()
                                    ->columnSpan(['md' => 1]),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => (float) str_replace('.', '', $state))
                                    ->columnSpan(['md' => 1]),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->reorderable(false),
                    ])
                    ->disabled(fn (?DeliveryNote $record) => $record && $record->exists && $record->status === 'DELIVERED'),
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
                Tables\Columns\TextColumn::make('sales.invoice_number')
                    ->label('Nomor Invoice')
                    ->placeholder('N/A')
                    ->listWithLineBreaks()
                    ->bulleted()
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
            ->with(['sales', 'customer', 'items.product', 'items.sale.customer']);
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
