<?php

namespace App\Filament\Resources\WarehousePickupResource\Pages;

use App\Filament\Resources\WarehousePickupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehousePickup extends CreateRecord
{
    protected static string $resource = WarehousePickupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
