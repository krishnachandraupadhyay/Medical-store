<?php

namespace App\Http\Requests\Store\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockCountRequest extends FormRequest
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
            'count_date' => ['required', 'date', 'before_or_equal:today'],
            'scope' => ['required', 'string', 'in:full,category,partial'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.batch_id' => ['required_with:items', 'integer'],
            'items.*.physical_quantity' => ['required_with:items', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'count_date.required' => 'Count date is required.',
            'count_date.before_or_equal' => 'Count date cannot be in the future.',
            'scope.required' => 'Stock count scope is required.',
            'scope.in' => 'Scope must be full, category, or partial.',
        ];
    }
}
