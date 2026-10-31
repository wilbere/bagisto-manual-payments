<?php

namespace Wilbere\BagistoManualPayments\Payment;

class WhatsApp extends BaseManualPayment
{
    /**
     * Código identificador del método en Bagisto.
     */
    protected $code = 'manual_whatsapp';

    /**
     * Verifica si el método está disponible (activo y con número de teléfono configurado).
     */
    public function isAvailable(): bool
    {
        if (! parent::isAvailable()) {
            return false;
        }

        $phone = $this->getConfigData('phone');

        return ! empty($phone);
    }
}
