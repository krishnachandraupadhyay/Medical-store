<?php

namespace App\Http\Requests\SuperAdmin\StoreOwner;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStoreOwnerRequest extends FormRequest
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
        $userId = $this->route('user') instanceof User
            ? $this->route('user')->id
            : (int) $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'mobile' => ['required', 'string', 'max:20'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->route('user') instanceof User
                ? $this->route('user')->id
                : (int) $this->route('user');

            $storeId = $this->input('store_id');
            $isActive = (bool) $this->input('is_active');

            if ($storeId && $isActive) {
                $existingOwner = User::where('store_id', $storeId)
                    ->where('role', UserRole::STORE_OWNER)
                    ->where('is_active', true)
                    ->where('id', '!=', $userId)
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
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'store_id.required' => 'Please select a medical store to assign.',
            'store_id.exists' => 'Selected medical store does not exist.',
        ];
    }
}
