<?php

namespace App\Filament\Resources\AutomaticDeliveryNoteResource\Pages;

use App\Filament\Resources\AutomaticDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryNote extends CreateRecord
{
    protected static string $resource = AutomaticDeliveryNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
