<?php

use Wilbere\BagistoManualPayments\Services\AmountResolverService;

it('resolves USD amount for Zelle', function () {
    $service = new AmountResolverService;

    $order = (object) [
        'id' => 1,
        'grand_total' => 25.50,
        'base_grand_total' => 25.50,
        'order_currency_code' => 'USD',
    ];

    $resolved = $service->resolve($order, 'manual_zelle');

    expect($resolved['currency'])->toBe('USD')
        ->and($resolved['amount'])->toBe(25.50)
        ->and($resolved['formatted'])->toBe('$ 25.50');
});

it('resolves USDT amount for Binance Pay', function () {
    $service = new AmountResolverService;

    $order = (object) [
        'id' => 2,
        'grand_total' => 100.00,
        'base_grand_total' => 100.00,
        'order_currency_code' => 'USD',
    ];

    $resolved = $service->resolve($order, 'manual_binance');

    expect($resolved['currency'])->toBe('USDT')
        ->and($resolved['amount'])->toBe(100.00)
        ->and($resolved['formatted'])->toBe('100.00 USDT');
});

it('resolves VES amount using exchange rate for Pago Movil', function () {
    $service = new AmountResolverService;

    $order = (object) [
        'id' => 3,
        'grand_total' => 10.00,
        'base_grand_total' => 10.00,
        'order_currency_code' => 'USD',
        'currency_exchange_rate' => 50.0000,
    ];

    $resolved = $service->resolve($order, 'manual_pagomovil');

    expect($resolved['currency'])->toBe('VES')
        ->and($resolved['amount'])->toBe(500.00)
        ->and($resolved['exchange_rate'])->toBe(50.0);
});
