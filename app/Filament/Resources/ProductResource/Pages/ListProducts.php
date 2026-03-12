<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_template')
                ->label('Download Template Import')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => asset('dokumen/template-import-produk.xlsx'))
                ->openUrlInNewTab(),
            
            Actions\Action::make('export_excel')
                ->label('Export Produk')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    $products = Product::all();
                    $writer = SimpleExcelWriter::streamDownload('export-produk-' . date('Y-m-d') . '.xlsx');

                    foreach ($products as $product) {
                        $writer->addRow([
                            'Nama' => $product->name,
                            'Kategori' => $product->category,
                            'SKU' => $product->sku,
                            'Satuan' => $product->uom,
                            'Isi per Dus' => $product->isi,
                            'Isi per Set' => $product->isi_set,
                            'Harga per Unit' => $product->price,
                            'Harga per Dus' => $product->price_per_carton,
                            'Harga per Set' => $product->price_per_set,
                            'Total Stok' => $product->stock,
                            'Deskripsi' => $product->description,
                        ]);
                    }

                    return $writer->toBrowser();
                }),

            Actions\Action::make('import_excel')
                ->label('Import Produk')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', '.xlsx', '.csv'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $file = Storage::disk('local')->path($data['file']);
                    
                    $rows = SimpleExcelReader::create($file)->getRows();
                    $count = 0;
                    
                    $rows->each(function (array $row) use (&$count) {
                        $sku = $row['SKU'] ?? ($row['SKU/Kode'] ?? '');
                        if (empty($sku)) {
                            return;
                        }

                        Product::updateOrCreate(
                            ['sku' => $sku],
                            [
                                'name' => $row['Nama'] ?? '',
                                'category' => $row['Kategori'] ?? '',
                                'uom' => $row['Satuan'] ?? 'PCS',
                                'isi' => isset($row['Isi per Dus']) && is_numeric($row['Isi per Dus']) ? (float) $row['Isi per Dus'] : 1,
                                'isi_set' => isset($row['Isi per Set']) && is_numeric($row['Isi per Set']) ? (float) $row['Isi per Set'] : 1,
                                'price' => isset($row['Harga per Unit']) && is_numeric($row['Harga per Unit']) ? (float) $row['Harga per Unit'] : 0,
                                'price_per_carton' => isset($row['Harga per Dus']) && is_numeric($row['Harga per Dus']) ? (float) $row['Harga per Dus'] : 0,
                                'price_per_set' => isset($row['Harga per Set']) && is_numeric($row['Harga per Set']) ? (float) $row['Harga per Set'] : 0,
                                'description' => $row['Deskripsi'] ?? '',
                            ]
                        );
                        $count++;
                    });
                    
                    Notification::make()
                        ->title("Berhasil mengimport {$count} produk!")
                        ->success()
                        ->send();
                }),
                
            Actions\CreateAction::make(),
        ];
    }
}
