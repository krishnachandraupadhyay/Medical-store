<?php

namespace App\Http\Requests\Store\Return;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isStoreOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('purchases.return'));
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'integer'],
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'settlement_type' => ['nullable', 'string', 'in:credit,refund,split'],
            'settlement_mode' => ['nullable', 'string', 'in:credit,refund,split'],
            'adjustment_amount' => ['nullable', 'numeric', 'min:0'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'refund_method' => ['nullable', 'string', 'max:50'],
            'refund_reference' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_id.required' => 'A valid purchase order is required.',
            'items.required' => 'At least one item must be selected for return.',
            'reason.required' => 'A return reason is required.',
            'return_date.required' => 'Return date is required.',
        ];
    }
}
