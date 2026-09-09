<?php

namespace App\Http\Requests\SuperAdmin\Subscription;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSubscriptionPlanRequest extends FormRequest
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
        $supportedFeatureKeys = array_keys(SubscriptionPlan::supportedFeatures());

        return [
            'name' => ['required', 'string', 'max:100', 'unique:subscription_plans,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:subscription_plans,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'billing_cycle' => ['required', new Enum(BillingCycle::class)],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'status' => ['required', new Enum(PlanStatus::class)],
            'is_popular' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            // Plan Limit Inputs (-1 allowed for unlimited, or >= 0)
            'limits' => ['nullable', 'array'],
            'limits.max_staff' => ['required', 'integer', 'min:-1', 'max:10000'],
            'limits.max_medicines' => ['required', 'integer', 'min:-1', 'max:1000000'],
            'limits.max_invoices' => ['required', 'integer', 'min:-1', 'max:1000000'],
            'limits.max_customers' => ['required', 'integer', 'min:-1', 'max:1000000'],

            // Supported Features Array
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in($supportedFeatureKeys)],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Subscription Plan Name is required.',
            'name.unique' => 'A subscription plan with this name already exists.',
            'slug.unique' => 'This plan slug is already in use.',
            'price.required' => 'Plan price is required (enter 0 for free plan).',
            'price.min' => 'Price cannot be negative.',
            'billing_cycle.required' => 'Please select a valid billing cycle.',
            'trial_days.min' => 'Trial days cannot be negative.',
            'limits.max_staff.min' => 'Staff limit cannot be negative (use -1 for unlimited).',
            'limits.max_medicines.min' => 'Medicine catalog limit cannot be negative (use -1 for unlimited).',
            'limits.max_invoices.min' => 'Monthly invoice limit cannot be negative (use -1 for unlimited).',
            'limits.max_customers.min' => 'Customer limit cannot be negative (use -1 for unlimited).',
        ];
    }
}
