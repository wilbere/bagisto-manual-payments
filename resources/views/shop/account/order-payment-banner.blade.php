@php
    $receipt = \Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt::where('order_id', $order->id)->first();
@endphp

@if ($receipt && in_array($receipt->payment_method, ['manual_pagomovil', 'manual_bank_transfer', 'manual_zelle', 'manual_binance']))
    <div class="mb-6 rounded-2xl border p-5 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4 {{ $receipt->isApproved() ? 'bg-emerald-50 border-emerald-200' : ($receipt->isRejected() ? 'bg-rose-50 border-rose-200' : 'bg-blue-50 border-blue-200') }}">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs uppercase font-bold tracking-wider {{ $receipt->isApproved() ? 'text-emerald-800' : ($receipt->isRejected() ? 'text-rose-800' : 'text-blue-800') }}">
                    Pago Manual ({{ $receipt->payment_method }})
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $receipt->isApproved() ? 'bg-emerald-200 text-emerald-900' : ($receipt->isRejected() ? 'bg-rose-200 text-rose-900' : 'bg-blue-200 text-blue-900') }}">
                    {{ $receipt->status_name }}
                </span>
            </div>
            <p class="text-sm text-gray-700 mt-1">
                @if ($receipt->isPendingSubmission())
                    Tu pedido está reservado. Por favor transfiere y sube el comprobante para que podamos despachar tu compra.
                @elseif ($receipt->isPendingVerification())
                    Comprobante recibido. Tu pago está siendo verificado por nuestro equipo contable.
                @elseif ($receipt->isApproved())
                    Pago conciliado y facturado con éxito.
                @elseif ($receipt->isRejected())
                    Tu comprobante no pudo ser verificado: <strong>{{ $receipt->rejection_reason }}</strong>
                @endif
            </p>
        </div>

        <div>
            <a
                href="{{ route('shop.manual_payments.receipt.show', $receipt->token) }}"
                class="px-5 py-2.5 rounded-xl font-bold text-sm shadow-xs transition inline-block {{ $receipt->isApproved() ? 'bg-emerald-700 text-white hover:bg-emerald-800' : ($receipt->isRejected() ? 'bg-rose-700 text-white hover:bg-rose-800' : 'bg-navyBlue text-white hover:bg-blue-900') }}"
            >
                {{ $receipt->isPendingSubmission() ? 'Subir Comprobante' : ($receipt->isRejected() ? 'Reenviar Comprobante' : 'Ver Detalles del Pago') }}
            </a>
        </div>
    </div>
@endif
