<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;
use Wilbere\BagistoManualPayments\Services\AmountResolverService;
use Wilbere\BagistoManualPayments\Services\ReceiptReconciliationService;

beforeEach(function () {
    Storage::fake('local');
    Event::fake();
});

it('creates a manual payment receipt with unique token and TTL', function () {
    $service = new ReceiptReconciliationService(new AmountResolverService);

    $order = (object) [
        'id' => 10,
        'grand_total' => 50.0,
        'base_grand_total' => 50.0,
        'order_currency_code' => 'USD',
    ];

    $receipt = $service->createReceiptForOrder($order, 'manual_zelle');

    expect($receipt->order_id)->toBe(10)
        ->and($receipt->payment_method)->toBe('manual_zelle')
        ->and(strlen($receipt->token))->toBe(64)
        ->and($receipt->status)->toBe(ManualPaymentReceipt::STATUS_PENDING_SUBMISSION)
        ->and($receipt->expires_at)->not->toBeNull();
});

it('submits a receipt proof securely and prevents duplicates', function () {
    $service = new ReceiptReconciliationService(new AmountResolverService);

    $order = (object) [
        'id' => 20,
        'grand_total' => 25.0,
        'base_grand_total' => 25.0,
        'order_currency_code' => 'USD',
    ];

    $receipt = $service->createReceiptForOrder($order, 'manual_zelle');

    $file = UploadedFile::fake()->image('voucher.jpg');

    $updated = $service->submitReceipt($receipt, [
        'reference_number' => 'REF-987654',
        'payer_name' => 'Juan Pérez',
        'payer_email' => 'juan@example.com',
    ], $file);

    expect($updated->status)->toBe(ManualPaymentReceipt::STATUS_PENDING_VERIFICATION)
        ->and($updated->reference_number)->toBe('REF-987654')
        ->and($updated->proof_image_path)->not->toBeNull();

    Storage::disk('local')->assertExists($updated->proof_image_path);

    // Intento de registrar la misma referencia en otra orden debe fallar (Anti-Fraude)
    $secondOrder = (object) [
        'id' => 21,
        'grand_total' => 25.0,
        'base_grand_total' => 25.0,
        'order_currency_code' => 'USD',
    ];

    $secondReceipt = $service->createReceiptForOrder($secondOrder, 'manual_zelle');

    expect(fn () => $service->submitReceipt($secondReceipt, [
        'reference_number' => 'REF-987654', // misma referencia
    ], $file))->toThrow(InvalidArgumentException::class);
});

it('approves a receipt and marks it as approved', function () {
    $service = new ReceiptReconciliationService(new AmountResolverService);

    $receipt = ManualPaymentReceipt::create([
        'order_id' => 30,
        'payment_method' => 'manual_zelle',
        'token' => 'token-30',
        'status' => ManualPaymentReceipt::STATUS_PENDING_VERIFICATION,
        'reference_number' => 'REF-30',
    ]);

    $service->approveReceipt($receipt, 1, 'Verificado en banco');

    expect($receipt->fresh()->isApproved())->toBeTrue()
        ->and($receipt->fresh()->verified_by)->toBe(1)
        ->and($receipt->fresh()->admin_notes)->toBe('Verificado en banco');
});

it('rejects a receipt with formal reason', function () {
    $service = new ReceiptReconciliationService(new AmountResolverService);

    $receipt = ManualPaymentReceipt::create([
        'order_id' => 40,
        'payment_method' => 'manual_pagomovil',
        'token' => 'token-40',
        'status' => ManualPaymentReceipt::STATUS_PENDING_VERIFICATION,
        'reference_number' => 'REF-40',
    ]);

    $service->rejectReceipt($receipt, 'Referencia no encontrada', 1, 'Revisado en app bancaria');

    expect($receipt->fresh()->isRejected())->toBeTrue()
        ->and($receipt->fresh()->rejection_reason)->toBe('Referencia no encontrada');
});
