<?php

use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;

it('expires overdue pending submissions via console command', function () {
    // Recibo vencido (debe expirar)
    $expired = ManualPaymentReceipt::create([
        'order_id' => 100,
        'payment_method' => 'manual_pagomovil',
        'token' => 'token-expired',
        'status' => ManualPaymentReceipt::STATUS_PENDING_SUBMISSION,
        'expires_at' => now()->subMinutes(15),
    ]);

    // Recibo aún vigente (no debe expirar)
    $active = ManualPaymentReceipt::create([
        'order_id' => 101,
        'payment_method' => 'manual_pagomovil',
        'token' => 'token-active',
        'status' => ManualPaymentReceipt::STATUS_PENDING_SUBMISSION,
        'expires_at' => now()->addMinutes(45),
    ]);

    $this->artisan('manual-payments:expire')
        ->assertSuccessful();

    expect($expired->fresh()->status)->toBe(ManualPaymentReceipt::STATUS_EXPIRED)
        ->and($active->fresh()->status)->toBe(ManualPaymentReceipt::STATUS_PENDING_SUBMISSION);
});
