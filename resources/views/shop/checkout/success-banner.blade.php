@php
    $receipt = \Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt::where('order_id', $order->id)->first();
@endphp

@if ($receipt && in_array($receipt->payment_method, ['manual_pagomovil', 'manual_bank_transfer', 'manual_zelle', 'manual_binance']))
    <div class="my-6 w-full max-w-xl mx-auto rounded-2xl border border-blue-200 bg-blue-50 p-6 text-center shadow-xs">
        <h3 class="text-lg font-bold text-blue-900">
            ¡Tu pedido ha sido registrado con éxito!
        </h3>
        <p class="text-sm text-blue-800 mt-2">
            Seleccionaste pago manual. Para completar tu orden, debes transferir y subir tu comprobante de pago.
        </p>

        <div class="mt-4">
            <a
                href="{{ route('shop.manual_payments.receipt.show', $receipt->token) }}"
                class="inline-block px-8 py-3 rounded-xl bg-navyBlue hover:bg-blue-900 text-white font-bold text-sm shadow-md transition"
            >
                Reportar Pago y Subir Comprobante
            </a>
        </div>
    </div>
@endif
