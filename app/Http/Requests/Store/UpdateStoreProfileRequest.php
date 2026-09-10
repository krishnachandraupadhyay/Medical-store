<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreProfileRequest extends FormRequest
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
            // Basic & Store Type
            'name' => ['required', 'string', 'max:255'],
            'store_type' => ['required', 'string', 'in:Retail,Wholesale,Retail + Wholesale,Hospital Pharmacy,Both'],

            // Contact Information
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'alternate_mobile' => ['nullable', 'string', 'max:20'],

            // Address Information
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],

            // Legal Information
            'gstin' => ['nullable', 'string', 'max:30'],
            'drug_license_no' => ['nullable', 'string', 'max:100'],
            'license_expiry_date' => ['nullable', 'date'],

            // Store Preferences
            'timezone' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:10'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
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
            'store_type.required' => 'Please select a valid Store Type.',
            'mobile.required' => 'Mobile number is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'pincode.required' => 'Pincode is required.',
            'email.email' => 'Please provide a valid email address.',
            'license_expiry_date.date' => 'Please enter a valid expiry date.',
        ];
    }
}
