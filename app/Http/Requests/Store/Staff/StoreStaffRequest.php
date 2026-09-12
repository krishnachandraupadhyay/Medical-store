<?php

namespace App\Http\Requests\Store\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('staff.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $store = current_store();
        $storeId = $store?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) use ($storeId) {
                    $query->where(function ($q) use ($storeId) {
                        $q->where('store_id', $storeId)
                            ->orWhereNull('store_id');
                    })->where('status', 'active');
                }),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
