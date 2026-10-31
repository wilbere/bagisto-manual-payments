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

        if (empty($phone) && class_exists(\Wilbere\BagistoManualPayments\Models\ManualPaymentAccount::class)) {
            $phone = \Wilbere\BagistoManualPayments\Models\ManualPaymentAccount::query()
                ->active()
                ->ofType(\Wilbere\BagistoManualPayments\Models\ManualPaymentAccount::TYPE_WHATSAPP)
                ->value('phone');
        }

        return ! empty($phone);
    }
}
