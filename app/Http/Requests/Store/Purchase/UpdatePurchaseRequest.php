<?php

namespace App\Http\Requests\Store\Purchase;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
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
        $purchase = $this->route('purchase');
        $purchaseId = $purchase instanceof Purchase ? $purchase->id : $purchase;

        return [
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->where('store_id', $storeId),
            ],
            'invoice_number' => [
                'required',
                'string',
                'max:80',
                function ($attribute, $value, $fail) use ($storeId, $purchaseId) {
                    if (! $storeId || ! $this->filled('supplier_id')) {
                        return;
                    }

                    $exists = Purchase::where('store_id', $storeId)
                        ->where('supplier_id', $this->input('supplier_id'))
                        ->where('id', '!=', $purchaseId)
                        ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [strtolower(trim($value))])
                        ->exists();

                    if ($exists) {
                        $fail('An invoice with this number already exists for this supplier in your store.');
                    }
                },
            ],
            'purchase_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => [
                'required',
                'integer',
                Rule::exists('medicines', 'id')->where('store_id', $storeId),
            ],
            'items.*.batch_number' => ['required', 'string', 'max:80'],
            'items.*.manufacturing_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['required', 'date'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.free_quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'items.*.mrp' => ['required', 'numeric', 'min:0'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier selection is required.',
            'supplier_id.exists' => 'Selected supplier does not exist or does not belong to your store.',
            'invoice_number.required' => 'Invoice number is required.',
            'purchase_date.required' => 'Purchase date is required.',
            'items.required' => 'At least one medicine item is required in the purchase.',
            'items.min' => 'At least one medicine item is required in the purchase.',
        ];
    }
}
