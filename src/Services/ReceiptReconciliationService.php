<?php

namespace Wilbere\BagistoManualPayments\Services;

use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Wilbere\BagistoManualPayments\Models\ManualPaymentAccount;
use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;

class ReceiptReconciliationService
{
    public function __construct(
        protected AmountResolverService $amountResolver
    ) {}

    /**
     * Inicializa un nuevo recibo de pago manual para una orden recién creada.
     */
    public function createReceiptForOrder(mixed $order, string $paymentMethod): ManualPaymentReceipt
    {
        $resolved = $this->amountResolver->resolve($order, $paymentMethod);
        $ttlMinutes = (int) config('manual-payments.ttl_minutes', 60);

        return ManualPaymentReceipt::create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'token' => bin2hex(random_bytes(32)),
            'paid_currency' => $resolved['currency'],
            'paid_amount' => $resolved['amount'],
            'exchange_rate' => $resolved['exchange_rate'],
            'status' => ManualPaymentReceipt::STATUS_PENDING_SUBMISSION,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }

    /**
     * Procesa la subida y registro de comprobante de pago por parte del comprador.
     *
     * @param  array{
     *     payment_account_id?: int|null,
     *     reference_number: string,
     *     payer_name?: string|null,
     *     payer_id?: string|null,
     *     payer_phone?: string|null,
     *     payer_email?: string|null,
     *     payer_bank?: string|null,
     *     paid_amount?: float|null,
     *     paid_at?: string|null
     * }  $data
     */
    public function submitReceipt(ManualPaymentReceipt $receipt, array $data, UploadedFile $file): ManualPaymentReceipt
    {
        if (! $receipt->canBeSubmitted()) {
            throw new DomainException(trans('manual_payments::app.errors.receipt_cannot_be_submitted'));
        }

        $referenceNumber = trim($data['reference_number']);

        // Anti-fraude: Validar unicidad de la referencia para este método en pagos no rechazados
        $isDuplicate = ManualPaymentReceipt::query()
            ->where('payment_method', $receipt->payment_method)
            ->where('reference_number', $referenceNumber)
            ->whereIn('status', [
                ManualPaymentReceipt::STATUS_PENDING_VERIFICATION,
                ManualPaymentReceipt::STATUS_APPROVED,
            ])
            ->where('id', '!=', $receipt->id)
            ->exists();

        if ($isDuplicate) {
            throw new InvalidArgumentException(trans('manual_payments::app.errors.duplicate_reference'));
        }

        $diskName = config('manual-payments.storage.disk', 'local');
        $storagePath = config('manual-payments.storage.path', 'manual_payments/proofs');

        // Almacenar el archivo de forma segura en disco privado
        $storedPath = $file->store($storagePath, $diskName);

        DB::transaction(function () use ($receipt, $data, $file, $storedPath, $referenceNumber) {
            // Eliminar archivo anterior si se trata de un reenvío
            if ($receipt->proof_image_path) {
                Storage::disk(config('manual-payments.storage.disk', 'local'))->delete($receipt->proof_image_path);
            }

            $receipt->update([
                'payment_account_id' => $data['payment_account_id'] ?? $receipt->payment_account_id,
                'reference_number' => $referenceNumber,
                'payer_name' => $data['payer_name'] ?? null,
                'payer_id' => $data['payer_id'] ?? null,
                'payer_phone' => $data['payer_phone'] ?? null,
                'payer_email' => $data['payer_email'] ?? null,
                'payer_bank' => $data['payer_bank'] ?? null,
                'paid_amount' => $data['paid_amount'] ?? $receipt->paid_amount,
                'paid_at' => ! empty($data['paid_at']) ? $data['paid_at'] : now(),
                'proof_image_path' => $storedPath,
                'proof_original_name' => $file->getClientOriginalName(),
                'proof_mime' => $file->getClientMimeType(),
                'proof_size' => $file->getSize(),
                'status' => ManualPaymentReceipt::STATUS_PENDING_VERIFICATION,
                'rejection_reason' => null,
            ]);
        });

        Event::dispatch('manual_payments.receipt.submitted', [$receipt]);

        return $receipt->fresh();
    }

    /**
     * Aprueba la conciliación del pago manual, facturando automáticamente la orden en Bagisto.
     */
    public function approveReceipt(ManualPaymentReceipt $receipt, ?int $adminId = null, ?string $notes = null): bool
    {
        if ($receipt->isApproved()) {
            return true;
        }

        return DB::transaction(function () use ($receipt, $adminId, $notes) {
            $receipt->update([
                'status' => ManualPaymentReceipt::STATUS_APPROVED,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'admin_notes' => $notes,
            ]);

            $this->createBagistoInvoice($receipt->order);

            Event::dispatch('manual_payments.receipt.approved', [$receipt]);

            return true;
        });
    }

    /**
     * Rechaza el comprobante de pago con un motivo formal.
     */
    public function rejectReceipt(ManualPaymentReceipt $receipt, string $reason, ?int $adminId = null, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($receipt, $reason, $adminId, $notes) {
            $receipt->update([
                'status' => ManualPaymentReceipt::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'admin_notes' => $notes,
            ]);

            // Si no se permite reenvío, cancelar la orden en Bagisto
            if (! config('manual-payments.allow_resubmission', true)) {
                $this->cancelBagistoOrder($receipt->order);
            }

            Event::dispatch('manual_payments.receipt.rejected', [$receipt]);

            return true;
        });
    }

    /**
     * Expira un recibo pendiente por superación del tiempo límite (TTL) y cancela la orden para liberar stock.
     */
    public function expireReceipt(ManualPaymentReceipt $receipt): bool
    {
        return DB::transaction(function () use ($receipt) {
            $receipt->update([
                'status' => ManualPaymentReceipt::STATUS_EXPIRED,
            ]);

            $this->cancelBagistoOrder($receipt->order);

            Event::dispatch('manual_payments.receipt.expired', [$receipt]);

            return true;
        });
    }

    /**
     * Genera la factura en Bagisto transicionando la orden a processing de forma 100% nativa.
     */
    protected function createBagistoInvoice(mixed $order): void
    {
        if (! $order) {
            return;
        }

        if (method_exists($order, 'canInvoice') && ! $order->canInvoice()) {
            return;
        }

        $invoiceRepositoryClass = 'Webkul\Sales\Repositories\InvoiceRepository';

        if (! class_exists($invoiceRepositoryClass)) {
            // Entorno de prueba o sin Bagisto: actualizar estado de orden si existe la columna
            if (isset($order->status)) {
                $order->status = 'processing';
                $order->save();
            }

            return;
        }

        $invoiceRepo = app($invoiceRepositoryClass);

        $items = [];
        if (! empty($order->items)) {
            foreach ($order->items as $item) {
                $qty = method_exists($item, 'qtyToInvoice')
                    ? $item->qtyToInvoice()
                    : ($item->qty_to_invoice ?? ($item->qty_ordered - ($item->qty_invoiced ?? 0)));

                if ($qty > 0) {
                    $items[$item->id] = $qty;
                }
            }
        }

        if (! empty($items)) {
            $invoiceRepo->create([
                'order_id' => $order->id,
                'invoice' => [
                    'items' => $items,
                ],
            ]);
        }
    }

    /**
     * Cancela la orden en Bagisto si está pendiente para retornar el inventario reservado.
     */
    protected function cancelBagistoOrder(mixed $order): void
    {
        if (! $order) {
            return;
        }

        $orderRepositoryClass = 'Webkul\Sales\Repositories\OrderRepository';

        if (class_exists($orderRepositoryClass)) {
            $orderRepo = app($orderRepositoryClass);
            if (method_exists($orderRepo, 'cancel') && method_exists($order, 'canCancel') && $order->canCancel()) {
                $orderRepo->cancel($order->id);

                return;
            }
        }

        if (isset($order->status) && in_array($order->status, ['pending', 'pending_payment'], true)) {
            $order->status = 'canceled';
            $order->save();
        }
    }
}
