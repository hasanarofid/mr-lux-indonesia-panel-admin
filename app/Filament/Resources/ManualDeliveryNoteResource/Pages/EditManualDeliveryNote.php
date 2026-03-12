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
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $deliveryNote = $this->record;
        $originalStatus = $deliveryNote->getOriginal('status');
        $newStatus = $deliveryNote->status;

        // If status changed from PENDING to SHIPPED or DELIVERED, deduct stock
        if ($originalStatus === 'PENDING' && in_array($newStatus, ['SHIPPED', 'DELIVERED'])) {
            foreach ($deliveryNote->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }
        }
        
        // If status changed from SHIPPED/DELIVERED back to PENDING, restore stock (optional but good practice)
        if (in_array($originalStatus, ['SHIPPED', 'DELIVERED']) && $newStatus === 'PENDING') {
            foreach ($deliveryNote->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        }
    }
}
