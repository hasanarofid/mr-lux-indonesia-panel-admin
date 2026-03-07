<?php

namespace App\Filament\Resources\ManualDeliveryNoteResource\Pages;

use App\Filament\Resources\ManualDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManualDeliveryNote extends EditRecord
{
    protected static string $resource = ManualDeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
