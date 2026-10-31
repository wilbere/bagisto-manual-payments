<?php

namespace Wilbere\BagistoManualPayments\Services;

use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;

class WhatsAppService
{
    public function __construct(
        protected AmountResolverService $amountResolver
    ) {}

    /**
     * Sanitiza y normaliza un número de teléfono para el formato internacional de WhatsApp (wa.me).
     */
    public function sanitizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Eliminar cualquier caracter no numérico
        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Si es un número venezolano que empieza con 0 (ej. 04141234567, 0424, 0412, 0416, 0426)
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '58'.substr($digits, 1);
        }

        // Si es un número venezolano sin el cero ni código de país (ej. 4141234567, 10 dígitos)
        if (strlen($digits) === 10 && in_array(substr($digits, 0, 3), ['414', '424', '412', '416', '426'], true)) {
            return '58'.$digits;
        }

        return $digits;
    }

    /**
     * Construye el texto enriquecido y estructurado del pedido para enviar por WhatsApp.
     */
    public function buildOrderMessage(mixed $order, ManualPaymentReceipt $receipt): string
    {
        $orderId = $order->increment_id ?? $order->id ?? 'N/A';
        $storeName = function_exists('core') ? (core()->getCurrentChannel()?->name ?? 'la tienda') : 'la tienda';

        $msg = "👋 ¡Hola! Acabo de registrar el pedido *#{$orderId}* en {$storeName}.\n\n";

        // 1. Resumen de Productos
        $msg .= "📦 *Resumen de Productos:*\n";
        if (! empty($order->items) && count($order->items) > 0) {
            foreach ($order->items as $item) {
                $qty = (int) ($item->qty_ordered ?? 1);
                $name = $item->name ?? 'Producto';
                $price = number_format((float) ($item->base_total ?? $item->total ?? 0), 2, '.', ',');
                $msg .= "• {$qty}x {$name} (Ref: \${$price})\n";
            }
        } else {
            $msg .= "• Pedido #{$orderId}\n";
        }
        $msg .= "\n";

        // 2. Monto exacto a transferir (VES y USD)
        $resolvedVes = $this->amountResolver->resolve($order, 'manual_pagomovil');
        $baseTotalUsd = number_format((float) ($order->base_grand_total ?? $order->grand_total ?? 0), 2, '.', ',');

        $msg .= "💵 *Total a Transferir:*\n";
        $msg .= "👉 *Bolívares (VES):* {$resolvedVes['formatted']}\n";
        if ($resolvedVes['exchange_rate']) {
            $tasaFormatted = number_format((float) $resolvedVes['exchange_rate'], 4, ',', '.');
            $msg .= "   _(Tasa Oficial BCV: {$tasaFormatted} Bs./USD | Ref: \${$baseTotalUsd})_\n";
        } else {
            $msg .= "   _(Ref en Divisa: \${$baseTotalUsd} USD)_\n";
        }
        $msg .= "\n";

        // 3. Datos del Comprador
        $customerName = trim(($order->customer_first_name ?? '').' '.($order->customer_last_name ?? ''));
        if (empty($customerName) && ! empty($order->customer_full_name)) {
            $customerName = $order->customer_full_name;
        }
        if (empty($customerName)) {
            $customerName = 'Cliente';
        }

        $phone = $order->billing_address?->phone ?? $order->shipping_address?->phone ?? null;
        $city = $order->shipping_address?->city ?? null;

        $msg .= "👤 *Datos del Comprador:*\n";
        $msg .= "• *Nombre:* {$customerName}\n";
        if ($phone) {
            $msg .= "• *Teléfono:* {$phone}\n";
        }
        if ($city) {
            $msg .= "• *Ciudad/Destino:* {$city}\n";
        }
        $msg .= "\n";

        // 4. Enlace del Pedido / Comprobante
        if (! empty($receipt->token)) {
            $proofUrl = route('shop.manual_payments.receipt.show', $receipt->token);
            $msg .= "🔗 *Ver pedido y subir comprobante:*\n";
            $msg .= "{$proofUrl}\n\n";
        }

        // 5. Cierre
        $msg .= '¿Me confirman disponibilidad para realizar la transferencia? ¡Muchas gracias!';

        return $msg;
    }

    /**
     * Genera la URL completa de WhatsApp (wa.me) con el mensaje codificado.
     */
    public function generateWhatsAppUrl(mixed $order, ManualPaymentReceipt $receipt, ?string $customPhone = null): ?string
    {
        $rawPhone = $customPhone;

        if (! $rawPhone && function_exists('core')) {
            $rawPhone = core()->getConfigData('sales.payment_methods.manual_whatsapp.phone');
        }

        $cleanPhone = $this->sanitizePhone($rawPhone);

        if (empty($cleanPhone)) {
            return null;
        }

        $message = $this->buildOrderMessage($order, $receipt);

        return 'https://wa.me/'.$cleanPhone.'?text='.rawurlencode($message);
    }
}
