<?php

namespace App\Http\Requests\Store\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('staff.edit') ?? false;
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
        $staff = $this->route('staff');
        $staffId = is_object($staff) ? $staff->id : $staff;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffId),
            ],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
