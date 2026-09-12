<?php

namespace App\Http\Requests\Store\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store = current_store();

        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'required',
                'string',
                'regex:/^[6-9]\d{9}$/',
                Rule::unique('customers', 'phone')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store?->id)->whereNull('deleted_at');
                }),
            ],
            'alternate_phone' => ['nullable', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'blood_group' => ['nullable', 'string', Rule::in(Customer::BLOOD_GROUPS)],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'regex:/^[6-9]\d{9}$/'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'doctor_name' => ['nullable', 'string', 'max:150'],
            'tags' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'The customer primary phone number is required.',
            'phone.regex' => 'The primary phone must be a valid 10-digit Indian mobile number (e.g., 9876543210).',
            'phone.unique' => 'A customer with this phone number already exists in your store.',
            'alternate_phone.regex' => 'The alternate phone must be a valid 10-digit mobile number.',
            'emergency_contact_phone.regex' => 'The emergency contact phone must be a valid 10-digit mobile number.',
            'blood_group.in' => 'Selected blood group is invalid. Valid choices: A+, A-, B+, B-, AB+, AB-, O+, O-.',
        ];
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);
        if (is_array($data)) {
            if (! empty($data['postal_code']) && empty($data['pincode'])) {
                $data['pincode'] = $data['postal_code'];
            }
            unset($data['postal_code']);
        }

        return $data;
    }
}
