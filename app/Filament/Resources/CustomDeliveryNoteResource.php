<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomDeliveryNoteResource\Pages;
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
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class CustomDeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Surat Jalan Custom';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 34;
    protected static ?string $slug = 'surat-jalan-custom';
    protected static ?string $modelLabel = 'Surat Jalan Custom';
    protected static ?string $pluralModelLabel = 'Surat Jalan Custom';
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengiriman')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default('CUSTOM'),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->billing_city} ({$record->code})")
                            ->searchable(['name', 'billing_city', 'code'])
                            ->required()
                            ->dehydrated()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $customer = \App\Models\Customer::find($state);
                                    if ($customer) {
                                        $addressParts = array_filter([
                                            $customer->billing_street,
                                            $customer->billing_city,
                                            $customer->billing_province,
                                            $customer->billing_postcode,
                                            $customer->billing_country,
                                        ]);
                                        $set('address', implode(', ', $addressParts));
                                    }
                                }
                            }),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor SJ')
                            ->default(fn () => 'SJC/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
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
                    ])->columns(2),

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
                                            $set('unit', strtoupper($product?->uom ?? 'PCS'));
                                        }
                                    })
                                    ->dehydrated()
                                    ->columnSpan(['md' => 4]),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->required()
                                    ->readOnly()
                                    ->dehydrated()
                                    ->columnSpan(['md' => 2]),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn ($state) => (float) str_replace('.', '', $state))
                                    ->dehydrated()
                                    ->columnSpan(['md' => 2]),
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
                        'SHIPPED' => 'warning',
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
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'CUSTOM')
            ->with(['customer', 'items.product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomDeliveryNotes::route('/'),
            'create' => Pages\CreateCustomDeliveryNote::route('/create'),
            'edit' => Pages\EditCustomDeliveryNote::route('/{record}/edit'),
        ];
    }
}
