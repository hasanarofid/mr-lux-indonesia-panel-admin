<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $slug = 'pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';
    protected static ?string $pluralModelLabel = 'Pelanggan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Informasi Umum')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label('ID Pelanggan')
                                    ->required()
                                    ->default(fn () => 'CUST-' . strtoupper(bin2hex(random_bytes(3))))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('category')
                                    ->label('Kategori')
                                    ->datalist(fn () => \App\Models\Customer::query()->whereNotNull('category')->distinct()->pluck('category')->toArray()),
                                Forms\Components\TextInput::make('phone_business')
                                    ->label('No. Telp. Bisnis')
                                    ->tel(),
                                Forms\Components\TextInput::make('handphone')
                                    ->label('Handphone')
                                    ->tel(),
                                Forms\Components\TextInput::make('whatsapp')
                                    ->label('No. WhatsApp')
                                    ->tel(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                                Forms\Components\TextInput::make('fax')
                                    ->label('Faximil'),
                                Forms\Components\TextInput::make('website')
                                    ->label('Website')
                                    ->url(),
                            ])->columnSpan(1),

                        Forms\Components\Section::make('Info Lainnya')
                            ->schema([
                                Forms\Components\Textarea::make('billing_street')
                                    ->label('Alamat Penagihan (Jalan)')
                                    ->rows(3),
                                Forms\Components\TextInput::make('billing_city')
                                    ->label('Kota'),
                                Forms\Components\TextInput::make('billing_postcode')
                                    ->label('K.Pos'),
                                Forms\Components\TextInput::make('billing_province')
                                    ->label('Provinsi'),
                                Forms\Components\TextInput::make('billing_country')
                                    ->label('Negara'),
                                Forms\Components\Select::make('group')
                                    ->label('Grup PPN')
                                    ->options([
                                        'PPN' => 'PPN',
                                        'Non-PPN' => 'Non-PPN',
                                    ])
                                    ->required()
                                    ->default('Non-PPN'),
                            ])->columnSpan(1),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->label('ID Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('Kontak Utama')
                    ->searchable()
                    ->default(fn ($record) => $record->phone_business ?? $record->handphone),
                Tables\Columns\TextColumn::make('sales_sum_grand_total')
                    ->label('Saldo')
                    ->sum('sales', 'grand_total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PPN' => 'success',
                        'Non-PPN' => 'warning',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
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
            RelationManagers\SalesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
