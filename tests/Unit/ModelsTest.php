<?php

use Wilbere\BagistoManualPayments\Models\ManualPaymentAccount;
use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;

it('can create and query manual payment accounts with scopes', function () {
    $activePm = ManualPaymentAccount::create([
        'type' => ManualPaymentAccount::TYPE_PAGOMOVIL,
        'title' => 'Banesco Pago Móvil',
        'bank_name' => 'Banesco',
        'bank_code' => '0134',
        'holder_name' => 'Comercio C.A.',
        'holder_id' => 'J-12345678-0',
        'phone' => '04141234567',
        'status' => true,
        'sort_order' => 1,
    ]);

    $inactiveZelle = ManualPaymentAccount::create([
        'type' => ManualPaymentAccount::TYPE_ZELLE,
        'title' => 'Zelle Secundario',
        'holder_name' => 'Comercio Inc',
        'email' => 'zelle@comercio.com',
        'status' => false,
        'sort_order' => 2,
    ]);

    expect(ManualPaymentAccount::active()->count())->toBe(1)
        ->and(ManualPaymentAccount::ofType(ManualPaymentAccount::TYPE_PAGOMOVIL)->count())->toBe(1)
        ->and(ManualPaymentAccount::ofType(ManualPaymentAccount::TYPE_ZELLE)->count())->toBe(1)
        ->and($activePm->type_name)->toBe(trans('manual_payments::app.methods.pagomovil'));
});

it('verifies receipt status lifecycle and submission checks', function () {
    $receipt = ManualPaymentReceipt::create([
        'order_id' => 101,
        'payment_method' => 'manual_pagomovil',
        'token' => 'test-token-12345',
        'status' => ManualPaymentReceipt::STATUS_PENDING_SUBMISSION,
        'expires_at' => now()->addMinutes(30),
    ]);

    expect($receipt->isPendingSubmission())->toBeTrue()
        ->and($receipt->isPendingVerification())->toBeFalse()
        ->and($receipt->isApproved())->toBeFalse()
        ->and($receipt->isRejected())->toBeFalse()
        ->and($receipt->isExpired())->toBeFalse()
        ->and($receipt->canBeSubmitted())->toBeTrue();

    // Simula expiración
    $receipt->expires_at = now()->subMinutes(10);
    expect($receipt->isExpired())->toBeTrue()
        ->and($receipt->canBeSubmitted())->toBeFalse();
});
