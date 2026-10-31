<?php

namespace Wilbere\BagistoManualPayments\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Wilbere\BagistoManualPayments\DataGrids\ManualPaymentReceiptsDataGrid;
use Wilbere\BagistoManualPayments\Models\ManualPaymentReceipt;
use Wilbere\BagistoManualPayments\Services\ReceiptReconciliationService;

class ReceiptController extends Controller
{
    public function __construct(
        protected ReceiptReconciliationService $reconciliationService
    ) {}

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return app(ManualPaymentReceiptsDataGrid::class)->toJson();
        }

        return view('manual_payments::admin.receipts.index');
    }

    public function view(int $id): JsonResponse|View
    {
        $receipt = ManualPaymentReceipt::query()
            ->with(['order.items', 'account', 'verifier'])
            ->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'receipt' => $receipt,
                'order' => $receipt->order,
                'account' => $receipt->account,
            ]);
        }

        return view('manual_payments::admin.receipts.view', compact('receipt'));
    }

    /**
     * Descarga o visualiza de forma segura el archivo del comprobante desde el storage privado.
     */
    public function downloadProof(int $id): StreamedResponse|RedirectResponse
    {
        $receipt = ManualPaymentReceipt::findOrFail($id);

        if (! $receipt->proof_image_path) {
            abort(404, 'Comprobante no encontrado.');
        }

        $disk = Storage::disk(config('manual-payments.storage.disk', 'local'));

        if (! $disk->exists($receipt->proof_image_path)) {
            abort(404, 'El archivo físico del comprobante no existe en el servidor.');
        }

        return $disk->response(
            $receipt->proof_image_path,
            $receipt->proof_original_name ?? basename($receipt->proof_image_path),
            [
                'Content-Type' => $receipt->proof_mime ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.($receipt->proof_original_name ?? 'comprobante').'"',
            ]
        );
    }

    public function approve(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $receipt = ManualPaymentReceipt::findOrFail($id);

        $adminId = auth()->guard('admin')->id();
        $notes = $request->input('admin_notes');

        $this->reconciliationService->approveReceipt($receipt, $adminId, $notes);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => trans('manual_payments::app.admin.receipts.approved_successfully'),
            ]);
        }

        session()->flash('success', trans('manual_payments::app.admin.receipts.approved_successfully'));

        return redirect()->back();
    }

    public function reject(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'admin_notes' => 'nullable|string',
        ]);

        $receipt = ManualPaymentReceipt::findOrFail($id);

        $adminId = auth()->guard('admin')->id();
        $reason = $request->input('reason');
        $notes = $request->input('admin_notes');

        $this->reconciliationService->rejectReceipt($receipt, $reason, $adminId, $notes);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => trans('manual_payments::app.admin.receipts.rejected_successfully'),
            ]);
        }

        session()->flash('success', trans('manual_payments::app.admin.receipts.rejected_successfully'));

        return redirect()->back();
    }
}
