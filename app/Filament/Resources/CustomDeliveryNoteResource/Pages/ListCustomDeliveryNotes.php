<?php

namespace App\Filament\Resources\CustomDeliveryNoteResource\Pages;

use App\Filament\Resources\CustomDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomDeliveryNotes extends ListRecords
{
    protected static string $resource = CustomDeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
