<?php

namespace Wilbere\BagistoManualPayments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSizeKb = config('manual-payments.storage.max_file_size_kb', 5120);

        return [
            'reference_number' => 'required|string|min:4|max:64',
            'payment_account_id' => 'nullable|integer|exists:manual_payment_accounts,id',
            'payer_name' => 'nullable|string|max:255',
            'payer_id' => 'nullable|string|max:30',
            'payer_phone' => 'nullable|string|max:30',
            'payer_email' => 'nullable|email|max:255',
            'payer_bank' => 'nullable|string|max:100',
            'paid_amount' => 'nullable|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'proof' => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:'.$maxSizeKb,
        ];
    }
}
