<?php

namespace App\Filament\Resources\ProductionReturnResource\Pages;

use App\Filament\Resources\ProductionReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionReturns extends ListRecords
{
    protected static string $resource = ProductionReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
