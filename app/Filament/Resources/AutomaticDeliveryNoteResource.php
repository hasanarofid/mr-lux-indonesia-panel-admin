<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutomaticDeliveryNoteResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Models\DeliveryNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class AutomaticDeliveryNoteResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
    protected static ?string $model = DeliveryNote::class;
    
    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Surat Jalan Otomatis';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 32;
    protected static ?string $slug = 'surat-jalan-otomatis';
    protected static ?string $modelLabel = 'Surat Jalan Otomatis';
    protected static ?string $pluralModelLabel = 'Surat Jalan Otomatis';
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengiriman')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default('AUTOMATIC'),
                        Forms\Components\Select::make('sale_id')
                            ->label('Nomor Invoice')
                            ->relationship('sale', 'invoice_number')
                            ->required()
                            ->searchable()
                            ->live()
                            ->disabled(fn (?DeliveryNote $record) => $record && $record->exists)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $sale = \App\Models\Sale::with('items.product')->find($state);
                                    if ($sale) {
                                        $set('customer_id', $sale->customer_id);
                                        
                                        // Pre-fill address
                                        if ($sale->customer) {
                                            $addressParts = array_filter([
                                                $sale->customer->billing_street,
                                                $sale->customer->billing_city,
                                                $sale->customer->billing_province,
                                                $sale->customer->billing_postcode,
                                                $sale->customer->billing_country,
                                            ]);
                                            $set('address', implode(', ', $addressParts));
                                        }

                                        $items = $sale->items->map(fn ($item) => [
                                            'product_id' => $item->product_id,
                                            'description' => $item->description,
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
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->billing_city} ({$record->code})")
                            ->visible(fn (Forms\Get $get) => $get('sale_id') !== null)
                            ->disabled()
                            ->dehydrated()
                            ->searchable(['name', 'billing_city', 'code']),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull()
                            ->dehydrated()
                            ->disabled(fn (?DeliveryNote $record) => $record && $record->exists && $record->status === 'DELIVERED'),
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor SJ')
                            ->default(fn () => 'SJ/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->required()
                            ->readOnly(fn (?DeliveryNote $record) => $record && $record->exists)
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required()
                            ->readOnly(fn (?DeliveryNote $record) => $record && $record->exists),
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
                    ])->columns(2),

                Forms\Components\Section::make('Item Barang')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->addable()
                            ->deletable()
                            ->reorderable(false)
                            ->helperText('Item surat jalan otomatis tidak bisa diedit secara langsung. Silakan edit Invoice terkait untuk mengubah barang.')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->allowHtml()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $stock = number_format($record->stock, 0, ',', '.');
                                        $dus = $record->isi > 0 ? floor($record->stock / $record->isi) : 0;
                                        $stockInfo = $record->is_track_stock ? "(Dus: {$dus} Stok: {$stock})" : "(Non-Stok)";
                                        
                                        return "
                                            <div>
                                                <div class='font-medium text-sm'>{$record->sku} - {$record->name}</div>
                                                <div class='text-xs opacity-70'>{$stockInfo}</div>
                                            </div>
                                        ";
                                    })
                                    ->nullable()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product) {
                                                $set('unit', $product->uom ?? 'PCS');
                                                $set('description', $product->name);
                                            }
                                        }
                                    })
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('description')
                                    ->label('Keterangan Custom')
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(8)
                            ->defaultItems(1)
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'AUTOMATIC' => 'success',
                        'MANUAL' => 'warning',
                        default => 'primary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->label('Nomor Invoice')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
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
                    ->label('Cetak SJ')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('delivery-notes.print', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn (DeliveryNote $record) => $record->status === 'DELIVERED'),
                DeleteAction::make()
                    ->hidden(fn (DeliveryNote $record) => $record->status === 'DELIVERED'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Filament\Tables\Actions\DeleteBulkAction $action, Collection $records) {
                            if ($records->where('status', 'DELIVERED')->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal!')
                                    ->body('Beberapa surat jalan yang dipilih sudah DELIVERED dan tidak bisa dihapus.')
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'AUTOMATIC')
            ->with(['sale', 'customer', 'items.product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryNotes::route('/'),
            'create' => Pages\CreateDeliveryNote::route('/create'),
            'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        ];
    }
}

