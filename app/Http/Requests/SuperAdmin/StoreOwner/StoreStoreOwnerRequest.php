<?php

namespace App\Http\Requests\SuperAdmin\StoreOwner;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStoreOwnerRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $storeId = $this->input('store_id');
            if ($storeId) {
                $existingOwner = User::where('store_id', $storeId)
                    ->where('role', UserRole::STORE_OWNER)
                    ->where('is_active', true)
                    ->first();

                if ($existingOwner) {
                    $validator->errors()->add(
                        'store_id',
                        "This store already has an active primary Store Owner ({$existingOwner->name} - {$existingOwner->email}). Please deactivate or reassign the existing owner before assigning a new primary owner."
                    );
                }
            }
        });
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Store Owner Full Name is required.',
            'email.required' => 'Store Owner Email is required.',
            'email.unique' => 'This email address is already registered to another user.',
            'mobile.required' => 'Mobile number is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'store_id.required' => 'Please select a medical store to assign.',
            'store_id.exists' => 'Selected medical store does not exist.',
        ];
    }
}
