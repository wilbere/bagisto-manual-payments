<?php

namespace Wilbere\BagistoManualPayments\Services;

class AmountResolverService
{
    /**
     * Resuelve la moneda, monto exacto a transferir y tasa de cambio aplicable para una orden.
     *
     * @param  mixed  $order  Instancia de Order de Bagisto
     * @param  string  $paymentMethod  Código del método (manual_pagomovil, manual_bank_transfer, etc.)
     * @return array{
     *     currency: string,
     *     amount: float,
     *     formatted: string,
     *     exchange_rate: float|null,
     *     rate_source: string|null
     * }
     */
    public function resolve(mixed $order, string $paymentMethod): array
    {
        $isVesMethod = in_array($paymentMethod, ['manual_pagomovil', 'manual_bank_transfer'], true);

        if ($isVesMethod) {
            return $this->resolveVesAmount($order);
        }

        if ($paymentMethod === 'manual_binance') {
            $amount = (float) ($order->base_grand_total ?? $order->grand_total ?? 0);

            return [
                'currency' => 'USDT',
                'amount' => $amount,
                'formatted' => number_format($amount, 2, '.', ',').' USDT',
                'exchange_rate' => null,
                'rate_source' => null,
            ];
        }

        // Zelle u otros métodos basados en USD
        $amount = (float) ($order->base_grand_total ?? $order->grand_total ?? 0);

        return [
            'currency' => 'USD',
            'amount' => $amount,
            'formatted' => '$ '.number_format($amount, 2, '.', ','),
            'exchange_rate' => null,
            'rate_source' => null,
        ];
    }

    /**
     * Resuelve el monto en Bolívares integrando con bagisto-bcv si está presente.
     */
    protected function resolveVesAmount(mixed $order): array
    {
        $orderCurrency = $order->order_currency_code ?? 'USD';
        $orderGrandTotal = (float) ($order->grand_total ?? 0);
        $orderBaseGrandTotal = (float) ($order->base_grand_total ?? $orderGrandTotal);

        // 1. Si la orden ya está liquidada en VES
        if (strtoupper($orderCurrency) === 'VES') {
            return [
                'currency' => 'VES',
                'amount' => $orderGrandTotal,
                'formatted' => 'Bs. '.number_format($orderGrandTotal, 2, ',', '.'),
                'exchange_rate' => null,
                'rate_source' => 'order_currency_ves',
            ];
        }

        // 2. Verificar si existe tasa congelada de bagisto-bcv (OrderRate)
        if (class_exists(\Wilbere\BagistoBcv\Models\OrderRate::class) && ! empty($order->id)) {
            $orderRate = \Wilbere\BagistoBcv\Models\OrderRate::query()->where('order_id', $order->id)->first();

            if ($orderRate && (float) $orderRate->rate > 0) {
                $rate = (float) $orderRate->rate;
                $vesAmount = round($orderBaseGrandTotal * $rate, 2);

                return [
                    'currency' => 'VES',
                    'amount' => $vesAmount,
                    'formatted' => 'Bs. '.number_format($vesAmount, 2, ',', '.'),
                    'exchange_rate' => $rate,
                    'rate_source' => 'bcv_frozen_rate',
                ];
            }
        }

        // 3. Fallback a la tasa de cambio de Bagisto si existe
        $exchangeRate = (float) ($order->currency_exchange_rate ?? 0);

        if ($exchangeRate > 0) {
            $vesAmount = round($orderBaseGrandTotal * $exchangeRate, 2);

            return [
                'currency' => 'VES',
                'amount' => $vesAmount,
                'formatted' => 'Bs. '.number_format($vesAmount, 2, ',', '.'),
                'exchange_rate' => $exchangeRate,
                'rate_source' => 'bagisto_exchange_rate',
            ];
        }

        // 4. Último recurso: usar el total base (asumiendo paridad si no hay tasa configurada)
        return [
            'currency' => 'VES',
            'amount' => $orderBaseGrandTotal,
            'formatted' => 'Bs. '.number_format($orderBaseGrandTotal, 2, ',', '.'),
            'exchange_rate' => 1.0,
            'rate_source' => 'fallback_1to1',
        ];
    }
}
