<?php

namespace Wilbere\BagistoManualPayments\Console;

use Illuminate\Console\Command;
use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;
use Wilbere\BagistoManualPayments\Services\ReceiptReconciliationService;

class ExpirePendingOrdersCommand extends Command
{
    protected $signature = 'manual-payments:expire';

    protected $description = 'Cancela órdenes de pagos manuales que hayan superado el tiempo límite sin comprobante para liberar inventario.';

    public function handle(ReceiptReconciliationService $service): int
    {
        $this->info('Verificando órdenes con pagos manuales expirados...');

        $expiredReceipts = ManualPaymentReceipt::query()
            ->expired()
            ->get();

        if ($expiredReceipts->isEmpty()) {
            $this->info('No hay órdenes pendientes expiradas.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($expiredReceipts as $receipt) {
            $service->expireReceipt($receipt);
            $count++;
            $this->line("Orden #{$receipt->order_id} (Recibo #{$receipt->id}) expirada y cancelada.");
        }

        $this->info("Total de órdenes expiradas procesadas: {$count}");

        return self::SUCCESS;
    }
}
