<?php

use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;
use Wilbere\BagistoManualPayments\Services\AmountResolverService;
use Wilbere\BagistoManualPayments\Services\WhatsAppService;

it('sanitizes Venezuelan and international phone numbers for WhatsApp', function () {
    $service = new WhatsAppService(new AmountResolverService);

    // Formato local común venezolano
    expect($service->sanitizePhone('04141234567'))->toBe('584141234567')
        ->and($service->sanitizePhone('0424 987-6543'))->toBe('584249876543')
        ->and($service->sanitizePhone('+58 (412) 111.22.33'))->toBe('584121112233')
        ->and($service->sanitizePhone('4165554433'))->toBe('584165554433')
        // Número internacional (USA)
        ->and($service->sanitizePhone('+1 (555) 123-4567'))->toBe('15551234567')
        ->and($service->sanitizePhone(''))->toBe('');
});

it('builds structured order message with BCV rate and token link', function () {
    $service = new WhatsAppService(new AmountResolverService);

    $order = (object) [
        'id' => 1050,
        'increment_id' => '1000042',
        'customer_first_name' => 'Carlos',
        'customer_last_name' => 'Gómez',
        'grand_total' => 20.0,
        'base_grand_total' => 20.0,
        'order_currency_code' => 'USD',
        'currency_exchange_rate' => 50.0,
        'items' => [
            (object) [
                'name' => 'Camisa Oxford',
                'qty_ordered' => 2,
                'base_total' => 20.0,
            ],
        ],
        'billing_address' => (object) [
            'phone' => '04141112233',
        ],
        'shipping_address' => (object) [
            'city' => 'Caracas',
        ],
    ];

    $receipt = new ManualPaymentReceipt([
        'order_id' => 1050,
        'token' => 'sample-test-token-abcdef123456',
        'payment_method' => 'manual_whatsapp',
    ]);

    $message = $service->buildOrderMessage($order, $receipt);

    expect($message)->toContain('#1000042')
        ->and($message)->toContain('2x Camisa Oxford')
        ->and($message)->toContain('Bs. 1.000,00')
        ->and($message)->toContain('Carlos Gómez')
        ->and($message)->toContain('Caracas')
        ->and($message)->toContain('sample-test-token-abcdef123456');
});

it('generates a valid wa.me URL encoded link', function () {
    $service = new WhatsAppService(new AmountResolverService);

    $order = (object) [
        'id' => 200,
        'grand_total' => 10.0,
        'base_grand_total' => 10.0,
        'order_currency_code' => 'USD',
        'items' => [],
    ];

    $receipt = new ManualPaymentReceipt([
        'order_id' => 200,
        'token' => 'token-200',
        'payment_method' => 'manual_whatsapp',
    ]);

    $url = $service->generateWhatsAppUrl($order, $receipt, '+58 414 123-4567');

    expect($url)->toStartWith('https://wa.me/584141234567?text=')
        ->and($url)->toContain('%23200');

    // Sin teléfono configurado retorna null
    expect($service->generateWhatsAppUrl($order, $receipt, ''))->toBeNull();
});
