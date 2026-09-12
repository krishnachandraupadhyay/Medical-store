<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PaymentMethod;
use App\Enums\StorePaymentType;
use App\Events\CustomerPaymentRecorded;
use App\Events\SupplierPaymentRecorded;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StorePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Record a customer payment against a completed sale.
     *
     * @throws ValidationException
     */
    public function recordSalePayment(Store $store, Sale $sale, array $data, ?int $userId = null): StorePayment
    {
        return DB::transaction(function () use ($store, $sale, $data, $userId) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $sale->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedSale->isCompleted()) {
                throw ValidationException::withMessages([
                    'sale' => 'Payments can only be recorded for completed sales.',
                ]);
            }

            $amount = round((float) ($data['amount'] ?? 0.00), 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            $outstanding = $lockedSale->outstandingAmount();
            if ($amount > $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => "Payment amount (₹{$amount}) cannot exceed the outstanding amount (₹{$outstanding}).",
                ]);
            }

            $paymentMethod = $data['payment_method'] ?? PaymentMethod::CASH->value;
            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentNumber = StorePayment::generatePaymentNumber($store->id);

            $payment = StorePayment::create([
                'store_id' => $store->id,
                'payment_number' => $paymentNumber,
                'type' => StorePaymentType::SALE_PAYMENT,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'sale_id' => $lockedSale->id,
                'customer_id' => $lockedSale->customer_id,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $lockedSale->paid_amount = round((float) $lockedSale->paid_amount + $amount, 2);
            $lockedSale->updatePaymentStatus();

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::STORE_PAYMENTS,
                "Recorded customer payment #{$payment->payment_number} of ₹{$amount} for Sale #{$lockedSale->invoice_number}.",
                $payment,
                null,
                $payment->toArray()
            );

            DB::afterCommit(fn () => event(new CustomerPaymentRecorded($payment)));

            return $payment;
        });
    }

    /**
     * Record a payment made to a supplier against a completed purchase.
     *
     * @throws ValidationException
     */
    public function recordPurchasePayment(Store $store, Purchase $purchase, array $data, ?int $userId = null): StorePayment
    {
        return DB::transaction(function () use ($store, $purchase, $data, $userId) {
            /** @var Purchase $lockedPurchase */
            $lockedPurchase = Purchase::where('id', $purchase->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedPurchase->isCompleted()) {
                throw ValidationException::withMessages([
                    'purchase' => 'Payments can only be recorded for completed purchases.',
                ]);
            }

            $amount = round((float) ($data['amount'] ?? 0.00), 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            $outstanding = $lockedPurchase->outstandingAmount();
            if ($amount > $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => "Payment amount (₹{$amount}) cannot exceed the outstanding amount (₹{$outstanding}).",
                ]);
            }

            $paymentMethod = $data['payment_method'] ?? PaymentMethod::BANK_TRANSFER->value;
            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentNumber = StorePayment::generatePaymentNumber($store->id);

            $payment = StorePayment::create([
                'store_id' => $store->id,
                'payment_number' => $paymentNumber,
                'type' => StorePaymentType::PURCHASE_PAYMENT,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'purchase_id' => $lockedPurchase->id,
                'supplier_id' => $lockedPurchase->supplier_id,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $lockedPurchase->paid_amount = round((float) $lockedPurchase->paid_amount + $amount, 2);
            $lockedPurchase->updatePaymentStatus();

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::STORE_PAYMENTS,
                "Recorded supplier payment #{$payment->payment_number} of ₹{$amount} for Purchase #{$lockedPurchase->invoice_number}.",
                $payment,
                null,
                $payment->toArray()
            );

            DB::afterCommit(fn () => event(new SupplierPaymentRecorded($payment)));

            return $payment;
        });
    }
}
