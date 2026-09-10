<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreLogoRequest extends FormRequest
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
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
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
            'logo.required' => 'Please select an image file to upload as the store logo.',
            'logo.image' => 'The uploaded file must be a valid image.',
            'logo.mimes' => 'Store logo must be a JPEG, PNG, JPG, WEBP, or SVG file.',
            'logo.max' => 'Store logo cannot exceed 2MB (2048 KB) in size.',
        ];
    }
}
