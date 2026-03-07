<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualDeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengiriman')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default('MANUAL'),
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
                                            $set('unit', $product?->uom ?? 'PCS');
                                        }
                                    })
                                    ->columnSpan(4),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
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
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak SJ')
                    ->icon('heroicon-o-printer')
                    ->url(fn (DeliveryNote $record): string => route('delivery-notes.print', $record))
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
        return parent::getEloquentQuery()
            ->where('type', 'MANUAL')
            ->with(['customer', 'items.product']);
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
