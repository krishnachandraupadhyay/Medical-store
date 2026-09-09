<?php

namespace App\Http\Requests\SuperAdmin\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'subscription_id' => ['nullable', 'integer', 'exists:subscriptions,id'],
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'currency' => ['required', 'string', 'max:10'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'transaction_id' => ['required', 'string', 'max:100', 'unique:payments,transaction_id'],
            'payment_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(PaymentStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
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
            'store_id.required' => 'Please select a medical store.',
            'store_id.exists' => 'The selected store does not exist.',
            'subscription_id.exists' => 'The selected subscription does not exist.',
            'amount.required' => 'Please provide the payment amount.',
            'amount.min' => 'The payment amount must be at least 0.01.',
            'transaction_id.required' => 'Transaction ID is required.',
            'transaction_id.unique' => 'A payment with this Transaction ID already exists.',
            'payment_method.required' => 'Please select a valid payment method.',
            'payment_date.required' => 'Please select a valid payment date.',
            'status.required' => 'Please select a valid payment status.',
        ];
    }
}
