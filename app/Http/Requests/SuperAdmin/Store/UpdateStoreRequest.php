<?php

namespace App\Http\Requests\SuperAdmin\Store;

use App\Enums\StoreStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'drug_license_no' => ['nullable', 'string', 'max:100'],
            'license_expiry_date' => ['nullable', 'date'],
            'store_type' => ['required', 'string', 'in:Retail,Wholesale,Hospital Pharmacy,Both'],
            'status' => ['required', Rule::enum(StoreStatus::class)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Store Name is required.',
            'mobile.required' => 'Store Mobile Number is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'pincode.required' => 'Pincode is required.',
            'store_type.required' => 'Please select a store type.',
            'logo.image' => 'Store logo must be a valid image file (PNG, JPG, WEBP, SVG).',
            'logo.max' => 'Store logo cannot exceed 2MB in size.',
        ];
    }
}
