<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryNoteResource\Pages;
use App\Filament\Resources\DeliveryNoteResource\RelationManagers;
use App\Models\DeliveryNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Surat Jalan';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'surat-jalan';
    protected static ?string $modelLabel = 'Surat Jalan';
    protected static ?string $pluralModelLabel = 'Surat Jalan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengiriman')
                    ->schema([
                        Forms\Components\Select::make('sale_id')
                            ->label('Nomor Invoice')
                            ->relationship('sale', 'invoice_number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor SJ')
                            ->default('SJ/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->label('Nomor Invoice')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
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
