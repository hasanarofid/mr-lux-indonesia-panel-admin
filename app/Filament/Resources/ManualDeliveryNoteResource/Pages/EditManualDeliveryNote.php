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
            Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->status === 'DELIVERED'),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->status === 'DELIVERED') {
            return [];
        }

        return parent::getFormActions();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
