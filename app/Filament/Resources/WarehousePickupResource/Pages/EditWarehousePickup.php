<?php

namespace App\Filament\Resources\WarehousePickupResource\Pages;

use App\Filament\Resources\WarehousePickupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehousePickup extends EditRecord
{
    protected static string $resource = WarehousePickupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
