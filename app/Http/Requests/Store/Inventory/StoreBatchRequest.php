<?php

namespace App\Http\Requests\Store\Inventory;

use App\Models\Batch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBatchRequest extends FormRequest
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

        return [
            'medicine_id' => [
                'required',
                'integer',
                Rule::exists('medicines', 'id')->where('store_id', $storeId),
            ],
            'batch_number' => [
                'required',
                'string',
                'max:80',
                function ($attribute, $value, $fail) use ($storeId) {
                    if (! $storeId || ! $this->filled('medicine_id')) {
                        return;
                    }

                    $exists = Batch::where('store_id', $storeId)
                        ->where('medicine_id', $this->input('medicine_id'))
                        ->whereRaw('LOWER(TRIM(batch_number)) = ?', [strtolower(trim($value))])
                        ->exists();

                    if ($exists) {
                        $fail('A batch with this batch number already exists for the selected medicine.');
                    }
                },
            ],
            'manufacturing_date' => ['nullable', 'date', 'before_or_equal:expiry_date'],
            'expiry_date' => ['required', 'date'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'secondary_barcode' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'opening_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'medicine_id.required' => 'Medicine selection is required.',
            'medicine_id.exists' => 'Selected medicine is invalid or does not belong to your store.',
            'batch_number.required' => 'Batch number is required.',
            'expiry_date.required' => 'Expiry date is required.',
            'purchase_price.required' => 'Purchase price is required.',
            'mrp.required' => 'MRP is required.',
            'selling_price.required' => 'Selling price is required.',
            'opening_stock.min' => 'Opening stock cannot be negative.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
