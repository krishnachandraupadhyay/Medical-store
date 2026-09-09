<?php

namespace App\Http\Requests\SuperAdmin\Subscription;

use App\Enums\PlanStatus;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subscription_plan_id' => [
                'required',
                'integer',
                'exists:subscription_plans,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $plan = SubscriptionPlan::find($value);
                    if ($plan && $plan->status !== PlanStatus::ACTIVE && $value != $this->subscription?->subscription_plan_id) {
                        $fail('Only active subscription plans can be selected.');
                    }
                },
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'trial_ends_at' => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:end_date'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'subscription_plan_id.required' => 'Please select a subscription plan.',
            'subscription_plan_id.exists' => 'The selected subscription plan does not exist.',
            'start_date.required' => 'The subscription start date is required.',
            'end_date.required' => 'The subscription end date is required.',
            'end_date.after_or_equal' => 'The end date cannot be earlier than the start date.',
            'trial_ends_at.after_or_equal' => 'The trial end date must be on or after the start date.',
            'trial_ends_at.before_or_equal' => 'The trial end date cannot exceed the subscription end date.',
            'status.required' => 'Please select a valid subscription status.',
        ];
    }
}
