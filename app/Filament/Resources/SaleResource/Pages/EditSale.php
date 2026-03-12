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
            Actions\DeleteAction::make(),
        ];
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
