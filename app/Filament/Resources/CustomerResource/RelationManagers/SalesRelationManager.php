<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total (Rp)')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('Total Keseluruhan')
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (\App\Models\Sale $record): string => \App\Filament\Resources\SaleResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
            ]);
    }
}
