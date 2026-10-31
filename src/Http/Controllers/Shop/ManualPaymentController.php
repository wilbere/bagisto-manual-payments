<?php

namespace Wilbere\BagistoManualPayments\Http\Controllers\Shop;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Wilbere\BagistoManualPayments\Http\Requests\SubmitReceiptRequest;
use Wilbere\BagistoManualPayments\Models\ManualPaymentAccount;
use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;
use Wilbere\BagistoManualPayments\Services\ReceiptReconciliationService;

class ManualPaymentController extends Controller
{
    public function __construct(
        protected ReceiptReconciliationService $reconciliationService,
        protected ?OrderRepository $orderRepository = null
    ) {
        if (! $this->orderRepository && class_exists(OrderRepository::class)) {
            $this->orderRepository = app(OrderRepository::class);
        }
    }

    /**
     * Procesa la orden nativa tras finalizar el checkout y redirige a la vista de reporte.
     */
    public function process(): RedirectResponse
    {
        if (class_exists(Cart::class)) {
            if (Cart::hasError()) {
                return redirect()->route('shop.checkout.cart.index');
            }

            $cart = Cart::getCart();

            if (! $cart) {
                return redirect()->route('shop.checkout.cart.index');
            }

            $paymentMethod = $cart->payment?->method ?? 'manual_pagomovil';

            $orderData = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($orderData);

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            $receipt = $this->reconciliationService->createReceiptForOrder($order, $paymentMethod);

            return redirect()->route('shop.manual_payments.receipt.show', ['token' => $receipt->token]);
        }

        return redirect()->route('shop.home.index');
    }

    /**
     * Muestra la pantalla de instrucciones bancarias y formulario de carga de comprobante.
     */
    public function show(string $token): View|RedirectResponse
    {
        $receipt = ManualPaymentReceipt::query()
            ->with(['order', 'account'])
            ->where('token', $token)
            ->firstOrFail();

        $accountType = match ($receipt->payment_method) {
            'manual_pagomovil' => ManualPaymentAccount::TYPE_PAGOMOVIL,
            'manual_bank_transfer' => ManualPaymentAccount::TYPE_BANK_TRANSFER,
            'manual_zelle' => ManualPaymentAccount::TYPE_ZELLE,
            'manual_binance' => ManualPaymentAccount::TYPE_BINANCE,
            default => null,
        };

        $accounts = $accountType
            ? ManualPaymentAccount::query()->active()->ofType($accountType)->get()
            : collect();

        $banks = config('manual-payments.banks', []);

        return view('manual_payments::shop.checkout.receipt-form', [
            'receipt' => $receipt,
            'order' => $receipt->order,
            'accounts' => $accounts,
            'banks' => $banks,
        ]);
    }

    /**
     * Procesa el envío del comprobante de pago por el comprador.
     */
    public function submit(SubmitReceiptRequest $request, string $token): RedirectResponse
    {
        $receipt = ManualPaymentReceipt::query()
            ->where('token', $token)
            ->firstOrFail();

        try {
            $this->reconciliationService->submitReceipt(
                $receipt,
                $request->validated(),
                $request->file('proof')
            );

            session()->flash('success', trans('manual_payments::app.shop.receipt_submitted_successfully'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('shop.manual_payments.receipt.show', ['token' => $token]);
    }
}
