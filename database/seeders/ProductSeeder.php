<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Spatie\SimpleExcel\SimpleExcelReader;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('dokumen/daftar-barang.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->warn("File not found: {$filePath}");
            return;
        }

        $rows = SimpleExcelReader::create($filePath)->fromSheet(1)->getRows();

        $rows->each(function (array $row) {
            $sku = $row['Kode Barang'] ?? '';
            
            // If SKU is empty, we might not want to seed it or we need a unique identifier.
            // However, the user said B-F only. 
            // If SKU is empty but Name exists, we could use Name as identifier if needed.
            // For now, let's skip rows with empty SKU if they are just empty rows.
            if (empty($sku)) {
                return;
            }

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $row['Nama Barang'] ?? '',
                    'category' => $row['Kategori Barang'] ?? '',
                    'uom' => $row['Satuan'] ?? 'PCS',
                    'description' => $row['Jenis Barang'] ?? '',
                    'price' => 0,
                    'stock' => 0,
                    'isi' => 1,
                ]
            );
        });
    }
}
