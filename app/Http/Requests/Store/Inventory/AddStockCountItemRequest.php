<?php

namespace App\Http\Requests\Store\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class AddStockCountItemRequest extends FormRequest
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
            'physical_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch_id.required' => 'Medicine batch is required.',
            'physical_quantity.required' => 'Physical counted quantity is required.',
            'physical_quantity.min' => 'Physical counted quantity cannot be negative.',
        ];
    }
}
