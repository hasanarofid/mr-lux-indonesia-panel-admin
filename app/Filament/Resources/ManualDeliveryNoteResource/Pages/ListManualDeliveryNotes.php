<?php

namespace App\Filament\Resources\ManualDeliveryNoteResource\Pages;

use App\Filament\Resources\ManualDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManualDeliveryNotes extends ListRecords
{
    protected static string $resource = ManualDeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
