<?php

return [
    /**
     * Tiempo límite en minutos que tiene el comprador para transferir y subir
     * el comprobante de pago antes de que la orden expire y se libere el inventario.
     */
    'ttl_minutes' => (int) env('MANUAL_PAYMENTS_TTL_MINUTES', 60),

    /**
     * Si se permite al comprador reenviar un nuevo comprobante en caso
     * de que el administrador rechace el pago inicial (ej. monto incompleto o referencia errónea).
     */
    'allow_resubmission' => (bool) env('MANUAL_PAYMENTS_ALLOW_RESUBMISSION', true),

    /**
     * Disco y ruta de almacenamiento para los comprobantes de pago.
     * Se recomienda usar un disco privado (no público) para proteger datos financieros.
     */
    'storage' => [
        'disk' => env('MANUAL_PAYMENTS_DISK', 'local'),
        'path' => 'manual_payments/proofs',
        'max_file_size_kb' => (int) env('MANUAL_PAYMENTS_MAX_FILE_SIZE_KB', 5120), // 5MB
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
        ],
    ],

    /**
     * Catálogo referencial de la banca venezolana (Código de 4 dígitos y Nombre).
     */
    'banks' => [
        '0102' => 'Banco de Venezuela',
        '0104' => 'Venezolano de Crédito',
        '0105' => 'Banco Mercantil',
        '0108' => 'Banco Provincial (BBVA)',
        '0114' => 'Bancaribe',
        '0115' => 'Banco Exterior',
        '0128' => 'Banco Caroní',
        '0134' => 'Banesco Banco Universal',
        '0138' => 'Banco Plaza',
        '0151' => 'BFC Banco Fondo Común',
        '0156' => '100% Banco',
        '0157' => 'DelSur Banco Universal',
        '0163' => 'Banco del Tesoro',
        '0166' => 'Banco Agrícola de Venezuela',
        '0168' => 'Bancrecer',
        '0169' => 'Mi Banco',
        '0171' => 'Banco Activo',
        '0172' => 'Bancamiga',
        '0174' => 'Banplus',
        '0175' => 'Banco Bicentenario',
        '0177' => 'BANFANB',
        '0191' => 'Banco Nacional de Crédito (BNC)',
    ],

    /**
     * Modelos y repositorios del core de Bagisto.
     */
    'models' => [
        'order' => env('MANUAL_PAYMENTS_ORDER_MODEL', 'Webkul\Sales\Models\Order'),
        'admin' => env('MANUAL_PAYMENTS_ADMIN_MODEL', 'Webkul\User\Models\Admin'),
    ],
];
