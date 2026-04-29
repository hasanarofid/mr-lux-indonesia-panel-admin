<?php

namespace App\Filament\Resources\WarehouseReturnResource\Pages;

use App\Filament\Resources\WarehouseReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseReturn extends EditRecord
{
    protected static string $resource = WarehouseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
