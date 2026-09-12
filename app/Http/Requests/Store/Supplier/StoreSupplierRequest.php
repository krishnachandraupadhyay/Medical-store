<?php

namespace App\Http\Requests\Store\Supplier;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:150',
                function ($attribute, $value, $fail) use ($storeId) {
                    if (! $storeId) {
                        return;
                    }

                    $exists = Supplier::where('store_id', $storeId)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($value))])
                        ->exists();

                    if ($exists) {
                        $fail('A supplier with this name already exists in your store.');
                    }
                },
            ],
            'company_name' => ['nullable', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'drug_license_no' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
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
            'name.required' => 'Supplier name is required.',
            'phone.required' => 'Phone number is required.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
