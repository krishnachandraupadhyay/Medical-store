<?php

namespace App\Http\Requests\Store\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ProcessExpiredStockRequest extends FormRequest
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
            'batch_id.required' => 'Expired batch selection is required.',
            'quantity.required' => 'Disposal quantity is required.',
            'quantity.min' => 'Disposal quantity must be at least 1.',
            'reason.required' => 'Disposal method / reason is required.',
        ];
    }
}
