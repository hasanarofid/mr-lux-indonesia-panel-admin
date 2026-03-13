<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->status === 'Lunas'),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->status === 'Lunas') {
            return [];
        }

        return parent::getFormActions();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['invoice_type']);
        unset($data['delivery_note_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $sale = $this->record;

        $deliveryNote = \App\Models\DeliveryNote::where('sale_id', $sale->id)
            ->where('type', 'AUTOMATIC')
            ->first();

        if ($deliveryNote) {
            $deliveryNote->items()->delete();

            foreach ($sale->items as $item) {
                $deliveryNote->items()->create([
                    'product_id' => $item->product_id,
                    'unit' => $item->unit ?? ($item->product ? $item->product->uom : 'PCS'),
                    'quantity' => $item->quantity,
                ]);
            }
            
            $deliveryNote->update([
                'customer_id' => $sale->customer_id,
            ]);
        }
    }
}
