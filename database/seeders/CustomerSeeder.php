<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Spatie\SimpleExcel\SimpleExcelReader;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('dokumen/daftar-pelanggan.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->warn("File not found: {$filePath}");
            return;
        }

        $rows = SimpleExcelReader::create($filePath)->fromSheet(1)->getRows();

        $rows->each(function (array $row) {
            $code = $row['ID Pelanggan'] ?? '';
            
            if (empty($code)) {
                return;
            }

            Customer::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $row['Nama'] ?? '',
                    'phone' => $row['Kontak'] ?? '',
                    'phone_business' => $row['No. Telp. Bisnis'] ?? '',
                    'handphone' => $row['Handphone'] ?? '',
                    'whatsapp' => $row['Handphone'] ?? '',
                    'email' => $row['Email'] ?? '',
                    'fax' => $row['Faximili'] ?? '',
                    'website' => $row['Website'] ?? '',
                    'address' => $row['Alamat Penagihan'] ?? '',
                    'billing_street' => $row['Alamat Penagihan'] ?? '',
                    'billing_city' => $row['Kota'] ?? '',
                    'billing_postcode' => $row['Kode Pos'] ?? '',
                    'billing_province' => $row['Provinsi'] ?? '',
                    'billing_country' => $row['Negara'] ?? '',
                    'group' => $row['Kategori'] ?? '',
                    'category' => $row['Kategori'] ?? '',
                ]
            );
        });
    }
}
