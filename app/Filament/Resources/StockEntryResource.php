<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockEntryResource\Pages;
use App\Filament\Resources\StockEntryResource\RelationManagers;
use App\Models\StockEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockEntryResource extends Resource
{
    protected static ?string $model = StockEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Production';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock Entry Details')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'PRODUCTION' => 'Production',
                                'ADJUSTMENT' => 'Adjustment',
                                'TRANSFER' => 'Transfer',
                            ])
                            ->required()
                            ->default('PRODUCTION'),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull(),
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
                                    ->preload(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => (\App\Models\Product::find($state['product_id'])?->name ?? 'Item') . ' (' . ($state['quantity'] ?? 0) . ')'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
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
            'index' => Pages\ListStockEntries::route('/'),
            'create' => Pages\CreateStockEntry::route('/create'),
            'edit' => Pages\EditStockEntry::route('/{record}/edit'),
        ];
    }
}
