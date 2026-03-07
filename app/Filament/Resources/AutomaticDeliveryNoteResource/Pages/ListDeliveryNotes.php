<?php

namespace App\Filament\Resources\AutomaticDeliveryNoteResource\Pages;

use App\Filament\Resources\AutomaticDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryNotes extends ListRecords
{
    protected static string $resource = AutomaticDeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
