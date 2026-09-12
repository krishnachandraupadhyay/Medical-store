<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\ExpenseStatus;
use App\Enums\StorePaymentType;
use App\Events\ExpenseRecorded;
use App\Models\Expense;
use App\Models\Store;
use App\Models\StorePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    /**
     * Create an expense.
     *
     * @throws ValidationException
     */
    public function createExpense(Store $store, array $data, ?int $userId = null): Expense
    {
        return DB::transaction(function () use ($store, $data, $userId) {
            $amount = round((float) ($data['amount'] ?? 0.00), 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Expense amount must be greater than zero.',
                ]);
            }

            $status = ($data['status'] ?? 'paid') === 'draft' ? ExpenseStatus::DRAFT : ExpenseStatus::PAID;
            $expenseNumber = Expense::generateExpenseNumber($store->id);
            $expenseDate = $data['expense_date'] ?? now()->toDateString();
            $paymentMethod = $data['payment_method'] ?? 'cash';

            $expense = Expense::create([
                'store_id' => $store->id,
                'expense_category_id' => (int) ($data['expense_category_id'] ?? $data['category_id'] ?? null),
                'expense_number' => $expenseNumber,
                'title' => trim($data['title']),
                'expense_date' => $expenseDate,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'reference_number' => $data['reference_number'] ?? null,
                'description' => $data['description'] ?? $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if ($status === ExpenseStatus::PAID) {
                StorePayment::create([
                    'store_id' => $store->id,
                    'payment_number' => StorePayment::generatePaymentNumber($store->id),
                    'type' => StorePaymentType::EXPENSE_PAYMENT,
                    'payment_date' => $expenseDate,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'expense_id' => $expense->id,
                    'reference_number' => $data['reference_number'] ?? null,
                    'notes' => "Disbursement for Expense #{$expense->expense_number} ({$expense->title})",
                    'created_by' => $userId,
                ]);
            }

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::EXPENSES,
                "Recorded expense #{$expense->expense_number} [{$expense->title}] for ₹{$amount}.",
                $expense,
                null,
                $expense->toArray()
            );

            DB::afterCommit(fn () => event(new ExpenseRecorded($expense)));

            return $expense;
        });
    }

    /**
     * Cancel an expense.
     *
     * @throws ValidationException
     */
    public function cancelExpense(Store $store, Expense $expense, ?string $reason = null, ?int $userId = null): Expense
    {
        return DB::transaction(function () use ($store, $expense, $reason, $userId) {
            /** @var Expense $lockedExpense */
            $lockedExpense = Expense::where('id', $expense->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedExpense->isCancelled()) {
                throw ValidationException::withMessages([
                    'expense' => 'Expense is already cancelled.',
                ]);
            }

            $oldStatus = $lockedExpense->status;
            $lockedExpense->status = ExpenseStatus::CANCELLED;
            $lockedExpense->description = trim(($lockedExpense->description ? $lockedExpense->description."\n" : '').'Cancelled: '.($reason ?: 'Cancelled by user'));
            $lockedExpense->updated_by = $userId;
            $lockedExpense->save();

            StorePayment::where('store_id', $store->id)
                ->where('expense_id', $lockedExpense->id)
                ->update(['status' => 'cancelled', 'updated_by' => $userId]);

            AuditLogger::log(
                AuditAction::CANCELLED,
                AuditModule::EXPENSES,
                "Cancelled expense #{$lockedExpense->expense_number}.",
                $lockedExpense,
                ['status' => $oldStatus->value],
                ['status' => ExpenseStatus::CANCELLED->value]
            );

            return $lockedExpense;
        });
    }
}
