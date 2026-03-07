<?php

namespace App\Filament\Resources\AutomaticDeliveryNoteResource\Pages;

use App\Filament\Resources\AutomaticDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryNote extends EditRecord
{
    protected static string $resource = AutomaticDeliveryNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
