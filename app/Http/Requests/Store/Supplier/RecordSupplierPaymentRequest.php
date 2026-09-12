<?php

namespace App\Http\Requests\Store\Supplier;

use App\Enums\PaymentMethod;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isStoreOwner() || auth()->user()->isSuperAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $store = current_store();
        $storeId = $store?->id;
        $supplier = $this->route('supplier');
        $supplierId = $supplier instanceof Supplier ? $supplier->id : $supplier;

        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
            'purchase_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($storeId, $supplierId) {
                    if (! $value) {
                        return;
                    }

                    $exists = Purchase::where('id', $value)
                        ->where('store_id', $storeId)
                        ->where('supplier_id', $supplierId)
                        ->exists();

                    if (! $exists) {
                        $fail('The selected purchase invoice does not belong to this supplier.');
                    }
                },
            ],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be greater than zero.',
            'payment_date.required' => 'Payment date is required.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
