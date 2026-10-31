<?php

namespace Wilbere\BagistoManualPayments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Wilbere\BagistoManualPayments\Models\ManualPaymentAccount;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    ManualPaymentAccount::TYPE_PAGOMOVIL,
                    ManualPaymentAccount::TYPE_BANK_TRANSFER,
                    ManualPaymentAccount::TYPE_ZELLE,
                    ManualPaymentAccount::TYPE_BINANCE,
                    ManualPaymentAccount::TYPE_WHATSAPP,
                ]),
            ],
            'title' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_code' => 'nullable|string|max:10',
            'account_number' => 'nullable|string|max:50',
            'holder_name' => 'required|string|max:255',
            'holder_id' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'binance_id' => 'nullable|string|max:100',
            'qr_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'instructions' => 'nullable|string',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ];
    }
}
