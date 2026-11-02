<?php

namespace Wilbere\BagistoManualPayments\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Wilbere\BagistoManualPayments\DataGrids\ManualPaymentAccountsDataGrid;
use Wilbere\BagistoManualPayments\Http\Requests\StoreAccountRequest;
use Wilbere\BagistoManualPayments\Models\ManualPaymentAccount;

class AccountController extends Controller
{
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return app(ManualPaymentAccountsDataGrid::class)->process();
        }

        return view('manual_payments::admin.accounts.index');
    }

    public function create(): View
    {
        $banks = config('manual-payments.banks', []);

        return view('manual_payments::admin.accounts.create', compact('banks'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('qr_image')) {
            $data['qr_image_path'] = $request->file('qr_image')->store('manual_payments/qrs', 'public');
        }

        ManualPaymentAccount::create($data);

        session()->flash('success', trans('manual_payments::app.admin.accounts.created_successfully'));

        return redirect()->route('admin.settings.manual_payment_accounts.index');
    }

    public function edit(int $id): View
    {
        $account = ManualPaymentAccount::findOrFail($id);
        $banks = config('manual-payments.banks', []);

        return view('manual_payments::admin.accounts.edit', compact('account', 'banks'));
    }

    public function update(StoreAccountRequest $request, int $id): RedirectResponse
    {
        $account = ManualPaymentAccount::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('qr_image')) {
            if ($account->qr_image_path) {
                Storage::disk('public')->delete($account->qr_image_path);
            }

            $data['qr_image_path'] = $request->file('qr_image')->store('manual_payments/qrs', 'public');
        }

        $account->update($data);

        session()->flash('success', trans('manual_payments::app.admin.accounts.updated_successfully'));

        return redirect()->route('admin.settings.manual_payment_accounts.index');
    }

    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        $account = ManualPaymentAccount::findOrFail($id);

        if ($account->qr_image_path) {
            Storage::disk('public')->delete($account->qr_image_path);
        }

        $account->delete();

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('manual_payments::app.admin.accounts.deleted_successfully'),
            ]);
        }

        session()->flash('success', trans('manual_payments::app.admin.accounts.deleted_successfully'));

        return redirect()->route('admin.settings.manual_payment_accounts.index');
    }
}
