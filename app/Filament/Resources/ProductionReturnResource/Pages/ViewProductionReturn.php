<?php

namespace App\Filament\Resources\ProductionReturnResource\Pages;

use App\Filament\Resources\ProductionReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductionReturn extends ViewRecord
{
    protected static string $resource = ProductionReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
