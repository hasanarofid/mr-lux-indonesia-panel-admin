<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/sales/{sale}/print', [\App\Http\Controllers\SalePrintController::class, 'show'])->name('sales.print');
Route::get('/delivery-notes/{deliveryNote}/print', [\App\Http\Controllers\DeliveryNotePrintController::class, 'show'])->name('delivery-notes.print');
Route::get('/reports/sales/print', [\App\Http\Controllers\SalesReportPrintController::class, 'show'])->name('sales.report.print');
