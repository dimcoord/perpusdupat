<?php

// Tempel di dalam grup middleware(['auth']) pada routes/web.php

use App\Http\Controllers\CartController;
use App\Http\Controllers\LoanReceiptController;

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/books/{book}', [CartController::class, 'add'])->name('add');
    Route::delete('/books/{book}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// POST /loans (loans.store) sekarang berarti "checkout keranjang"
Route::get('/loan-receipts/{receipt}', [LoanReceiptController::class, 'show'])->name('loan-receipts.show');
Route::post('/loan-receipts/{receipt}/approve', [LoanReceiptController::class, 'approve'])->name('loan-receipts.approve');
