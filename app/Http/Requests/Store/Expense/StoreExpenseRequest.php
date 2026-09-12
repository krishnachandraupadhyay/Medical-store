<?php

namespace App\Http\Requests\Store\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:200'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'string', 'in:cash,card,upi,bank_transfer,cheque,other'],
            'status' => ['nullable', 'string', 'in:draft,paid'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
