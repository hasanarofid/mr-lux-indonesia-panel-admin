<?php

namespace App\Filament\Resources\CustomDeliveryNoteResource\Pages;

use App\Filament\Resources\CustomDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomDeliveryNote extends CreateRecord
{
    protected static string $resource = CustomDeliveryNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
