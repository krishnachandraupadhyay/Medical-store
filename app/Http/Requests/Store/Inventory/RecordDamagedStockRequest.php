<?php

namespace App\Http\Requests\Store\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class RecordDamagedStockRequest extends FormRequest
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
        return [
            'batch_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch_id.required' => 'Batch selection is required.',
            'quantity.required' => 'Quantity to write off is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'reason.required' => 'Damage cause / reason is required.',
        ];
    }
}
