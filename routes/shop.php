<?php

use Illuminate\Support\Facades\Route;
use Wilbere\BagistoManualPayments\Http\Controllers\Shop\ManualPaymentController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('checkout/manual-payments')->name('shop.manual_payments.')->group(function () {
        Route::get('/process', [ManualPaymentController::class, 'process'])->name('process');
        Route::get('/{token}', [ManualPaymentController::class, 'show'])->name('receipt.show');
        Route::post('/{token}', [ManualPaymentController::class, 'submit'])->name('receipt.submit');
    });
});
