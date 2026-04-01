<?php

namespace App\Filament\Resources\CustomDeliveryNoteResource\Pages;

use App\Filament\Resources\CustomDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomDeliveryNote extends EditRecord
{
    protected static string $resource = CustomDeliveryNoteResource::class;

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
