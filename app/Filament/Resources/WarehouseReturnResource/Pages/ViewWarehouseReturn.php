<?php

namespace App\Filament\Resources\WarehouseReturnResource\Pages;

use App\Filament\Resources\WarehouseReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouseReturn extends ViewRecord
{
    protected static string $resource = WarehouseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
