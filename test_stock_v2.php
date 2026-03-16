<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::beginTransaction();

try {
    // 1. Create a product
    $product = Product::create([
        'name' => 'Test Product ' . time(),
        'sku' => 'TEST-' . time(),
        'stock' => 100,
        'isi' => 10,
    ]);
    echo "Initial stock: {$product->stock}\n";

    // Create a customer
    $customer = \App\Models\Customer::create([
        'name' => 'Test Customer',
        'code' => 'CUST-' . time(),
    ]);

    // 2. Create a sale
    $sale = Sale::create([
        'customer_id' => $customer->id,
        'invoice_number' => 'TEST-INV-' . time(),
        'date' => now(),
        'status' => 'Belum Lunas',
    ]);

    // 3. Create sale item
    $saleItem = SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'price' => 1000,
        'subtotal' => 10000,
    ]);

    $product->refresh();
    echo "Stock after sale creation: {$product->stock} (expected 90)\n";

    if ($product->stock != 90) {
        throw new Exception("Stock reduction failed! Current stock: " . $product->stock);
    }

    // 4. Delete sale
    echo "Deleting sale...\n";
    // Force call delete to trigger hooks
    $sale->delete();

    $product->refresh();
    echo "Stock after sale deletion: {$product->stock} (expected 100)\n";

    if ($product->stock != 100) {
        throw new Exception("Stock reversion failed! Current stock: " . $product->stock);
    }

    echo "ALL TESTS PASSED!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
}
