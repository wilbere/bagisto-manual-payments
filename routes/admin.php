<?php

use Illuminate\Support\Facades\Route;
use Wilbere\BagistoManualPayments\Http\Controllers\Admin\AccountController;
use Wilbere\BagistoManualPayments\Http\Controllers\Admin\ReceiptController;

$adminPrefix = config('app.admin_url', 'admin');

Route::group(['middleware' => ['web', 'admin'], 'prefix' => $adminPrefix], function () {
    // Conciliación de Comprobantes (Ventas)
    Route::prefix('sales/manual-payments')->name('admin.sales.manual_payments.')->group(function () {
        Route::get('/', [ReceiptController::class, 'index'])->name('index');
        Route::get('/{id}', [ReceiptController::class, 'view'])->name('view');
        Route::get('/{id}/proof', [ReceiptController::class, 'downloadProof'])->name('download_proof');
        Route::post('/{id}/approve', [ReceiptController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ReceiptController::class, 'reject'])->name('reject');
    });

    // Gestión de Cuentas Receptoras de la Tienda (Configuración)
    Route::prefix('settings/manual-payment-accounts')->name('admin.settings.manual_payment_accounts.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/create', [AccountController::class, 'create'])->name('create');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AccountController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountController::class, 'destroy'])->name('destroy');
    });
});
