<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStorePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
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
            'current_password.required' => 'Current password is required.',
            'current_password.current_password' => 'The provided current password does not match our records.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'The new password must be at least 8 characters in length.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password_confirmation.required' => 'Please confirm your new password.',
        ];
    }
}
