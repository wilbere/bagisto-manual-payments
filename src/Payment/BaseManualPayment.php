<?php

namespace Wilbere\BagistoManualPayments\Payment;

if (! class_exists(\Webkul\Payment\Payment\Payment::class)) {
    abstract class BasePaymentStub
    {
        protected $cart;
        protected $code;

        public function getCode()
        {
            return $this->code;
        }

        public function getConfigData($field)
        {
            return null;
        }

        public function isAvailable()
        {
            return true;
        }

        public function getTitle()
        {
            return $this->code;
        }

        public function getDescription()
        {
            return '';
        }

        public function getImage()
        {
            return '';
        }

        abstract public function getRedirectUrl();
    }

    class_alias(BasePaymentStub::class, \Webkul\Payment\Payment\Payment::class);
}

use Webkul\Payment\Payment\Payment;

abstract class BaseManualPayment extends Payment
{
    /**
     * Verifica si el método de pago está habilitado en Bagisto.
     */
    public function isAvailable(): bool
    {
        $active = $this->getConfigData('active');

        if ($active !== null) {
            return (bool) $active;
        }

        return (bool) config('payment_methods.'.$this->getCode().'.active', true);
    }

    /**
     * URL de redirección post-checkout para procesar la orden y mostrar
     * las instrucciones de pago con subida de comprobante.
     */
    public function getRedirectUrl(): string
    {
        return route('shop.manual_payments.process');
    }

    /**
     * Información adicional que se despliega en el checkout.
     */
    public function getAdditionalDetails(): array
    {
        $instructions = $this->getConfigData('instructions');

        if (empty($instructions)) {
            return [];
        }

        return [
            'title' => trans('manual_payments::app.shop.instructions'),
            'value' => $instructions,
        ];
    }
}
