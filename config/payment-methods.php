<?php

use Wilbere\BagistoManualPayments\Payment\BankTransfer;
use Wilbere\BagistoManualPayments\Payment\Binance;
use Wilbere\BagistoManualPayments\Payment\PagoMovil;
use Wilbere\BagistoManualPayments\Payment\WhatsApp;
use Wilbere\BagistoManualPayments\Payment\Zelle;

return [
    'manual_pagomovil' => [
        'class' => PagoMovil::class,
        'code' => 'manual_pagomovil',
        'title' => 'Pago Móvil (VES)',
        'description' => 'Paga en Bolívares mediante Pago Móvil interbancario instantáneo.',
        'active' => true,
        'sort' => 1,
    ],

    'manual_bank_transfer' => [
        'class' => BankTransfer::class,
        'code' => 'manual_bank_transfer',
        'title' => 'Transferencia Bancaria Nacional (VES)',
        'description' => 'Transferencia directa entre bancos nacionales en Bolívares.',
        'active' => true,
        'sort' => 2,
    ],

    'manual_zelle' => [
        'class' => Zelle::class,
        'code' => 'manual_zelle',
        'title' => 'Zelle (USD)',
        'description' => 'Pago en dólares estadounidenses directo a cuenta Zelle.',
        'active' => true,
        'sort' => 3,
    ],

    'manual_binance' => [
        'class' => Binance::class,
        'code' => 'manual_binance',
        'title' => 'Binance Pay / Crypto (USDT)',
        'description' => 'Transferencia inmediata sin comisiones en USDT vía Binance Pay.',
        'active' => true,
        'sort' => 4,
    ],

    'manual_whatsapp' => [
        'class' => WhatsApp::class,
        'code' => 'manual_whatsapp',
        'title' => 'Acordar por WhatsApp (Confirmar Stock y Pago)',
        'description' => 'Reserva el pedido en la tienda y coordina el pago con atención personalizada por WhatsApp.',
        'active' => true,
        'sort' => 5,
    ],
];
