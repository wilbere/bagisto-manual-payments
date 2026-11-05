@php
    $receipt = \Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt::where('order_id', $order->id)->first();
@endphp

@if ($receipt && in_array($receipt->payment_method, ['manual_pagomovil', 'manual_bank_transfer', 'manual_zelle', 'manual_binance']))
    <div class="my-6 w-full max-w-xl mx-auto rounded-2xl border border-[#ede8dc] bg-[#fdfbf7] p-6 text-center shadow-xs">
        <h3 class="text-lg font-bold text-[#18181b]">
            ¡Tu pedido ha sido registrado con éxito!
        </h3>
        <p class="text-sm text-[#71717a] mt-2">
            Seleccionaste pago manual. Para completar tu orden, debes transferir y subir tu comprobante de pago.
        </p>

        <div class="mt-5">
            <a
                href="{{ route('shop.manual_payments.receipt.show', $receipt->token) }}"
                class="inline-block px-8 py-3.5 rounded-full bg-[#18181b] hover:bg-[#9e8464] text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all active:scale-[0.99]"
                style="background-color: #18181b !important; color: #ffffff !important;"
            >
                Reportar Pago y Subir Comprobante
            </a>
        </div>
    </div>
@endif
