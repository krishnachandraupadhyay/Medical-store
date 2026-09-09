<?php

namespace App\Http\Requests\SuperAdmin\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
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
        $payment = $this->route('payment');
        $paymentId = $payment instanceof Payment ? $payment->id : $payment;

        return [
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(PaymentStatus::class)],
            'transaction_id' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('payments', 'transaction_id')->ignore($paymentId)],
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
            'amount.min' => 'The payment amount must be at least 0.01.',
            'transaction_id.unique' => 'A payment with this Transaction ID already exists.',
            'payment_method.required' => 'Please select a valid payment method.',
            'payment_date.required' => 'Please select a valid payment date.',
            'status.required' => 'Please select a valid payment status.',
        ];
    }
}
