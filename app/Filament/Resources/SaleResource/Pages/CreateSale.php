<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public ?string $invoiceType = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->invoiceType = $data['invoice_type'] ?? 'NORMAL';

        return $data;
    }

    protected function afterCreate(): void
    {
        $sale = $this->record;

        if ($this->invoiceType === 'NORMAL') {
            $deliveryNote = \App\Models\DeliveryNote::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'type' => 'AUTOMATIC',
                'number' => 'SJ/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'date' => $sale->date,
                'status' => 'PENDING',
            ]);

            foreach ($sale->items as $item) {
                $deliveryNote->items()->create([
                    'product_id' => $item->product_id,
                    'unit' => $item->unit ?? ($item->product ? $item->product->uom : 'PCS'),
                    'quantity' => $item->quantity,
                ]);
            }
        }
    }
}
