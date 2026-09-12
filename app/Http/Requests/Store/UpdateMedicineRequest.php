<?php

namespace App\Http\Requests\Store;

use App\Models\Medicine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
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
        $medicine = $this->route('medicine');
        $medicineId = $medicine instanceof Medicine ? $medicine->id : (int) $medicine;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                function ($attribute, $value, $fail) use ($storeId, $medicineId) {
                    if (! $storeId) {
                        return;
                    }

                    $exists = Medicine::where('store_id', $storeId)
                        ->where('id', '!=', $medicineId)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($value))])
                        ->when(
                            $this->filled('strength'),
                            fn ($q) => $q->whereRaw('LOWER(TRIM(strength)) = ?', [strtolower(trim($this->input('strength')))]),
                            fn ($q) => $q->whereNull('strength')->orWhere('strength', '')
                        )
                        ->when(
                            $this->filled('dosage_form_id'),
                            fn ($q) => $q->where('dosage_form_id', $this->input('dosage_form_id')),
                            fn ($q) => $q->whereNull('dosage_form_id')
                        )
                        ->exists();

                    if ($exists) {
                        $fail('Another medicine with this name, strength, and dosage form already exists in your store catalog.');
                    }
                },
            ],
            'generic_name' => ['nullable', 'string', 'max:150'],
            'brand_name' => ['nullable', 'string', 'max:150'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($storeId) {
                    $query->whereNull('store_id')->orWhere('store_id', $storeId);
                }),
            ],
            'manufacturer_id' => [
                'nullable',
                'integer',
                Rule::exists('manufacturers', 'id')->where(function ($query) use ($storeId) {
                    $query->whereNull('store_id')->orWhere('store_id', $storeId);
                }),
            ],
            'dosage_form_id' => [
                'nullable',
                'integer',
                Rule::exists('dosage_forms', 'id')->where(function ($query) use ($storeId) {
                    $query->whereNull('store_id')->orWhere('store_id', $storeId);
                }),
            ],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) use ($storeId) {
                    $query->whereNull('store_id')->orWhere('store_id', $storeId);
                }),
            ],
            'strength' => ['nullable', 'string', 'max:50'],
            'pack_size' => ['nullable', 'string', 'max:50'],
            'prescription_required' => ['nullable', 'boolean'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Medicine Name is required.',
            'name.max' => 'Medicine Name cannot exceed 150 characters.',
            'category_id.exists' => 'Selected Category is invalid or not accessible for your store.',
            'manufacturer_id.exists' => 'Selected Manufacturer is invalid or not accessible for your store.',
            'dosage_form_id.exists' => 'Selected Dosage Form is invalid.',
            'unit_id.exists' => 'Selected Unit is invalid.',
            'gst_rate.required' => 'GST Rate is required (enter 0 if exempt).',
            'gst_rate.numeric' => 'GST Rate must be a valid numeric percentage.',
            'reorder_level.required' => 'Reorder Level is required.',
            'reorder_level.min' => 'Reorder Level cannot be negative.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
