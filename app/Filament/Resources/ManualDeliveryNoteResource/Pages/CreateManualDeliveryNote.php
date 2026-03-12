<?php

namespace App\Filament\Resources\ManualDeliveryNoteResource\Pages;

use App\Filament\Resources\ManualDeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateManualDeliveryNote extends CreateRecord
{
    protected static string $resource = ManualDeliveryNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $deliveryNote = $this->record;

        // If newly created manual delivery note is directly set to SHIPPED or DELIVERED
        if (in_array($deliveryNote->status, ['SHIPPED', 'DELIVERED'])) {
            foreach ($deliveryNote->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }
        }
    }
}
