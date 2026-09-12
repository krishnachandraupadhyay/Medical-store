<?php

namespace App\Listeners;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Events\CustomerPaymentRecorded;
use App\Events\ExpenseRecorded;
use App\Events\PurchaseCompleted;
use App\Events\PurchaseReturnCompleted;
use App\Events\SaleCompleted;
use App\Events\SalesReturnCompleted;
use App\Events\SupplierPaymentRecorded;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\StorePayment;
use App\Services\NotificationService;
use Illuminate\Events\Dispatcher;

class NotificationListener
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handleSaleCompleted(SaleCompleted $event): void
    {
        $sale = $event->sale;
        $totalFormatted = number_format($sale->grand_total, 2);

        $this->notificationService->notify([
            'store_id' => $sale->store_id,
            'type' => NotificationType::SALE_COMPLETED,
            'title' => 'Sale Completed',
            'message' => "Sale #{$sale->invoice_number} has been completed for ₹{$totalFormatted}.",
            'priority' => NotificationPriority::NORMAL,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'alert_key' => "event:sale:{$sale->id}",
            'action_url' => route('store.sales.show', $sale->id),
            'metadata' => [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'grand_total' => (float) $sale->grand_total,
            ],
        ]);
    }

    public function handlePurchaseCompleted(PurchaseCompleted $event): void
    {
        $purchase = $event->purchase;
        $totalFormatted = number_format($purchase->grand_total, 2);

        $this->notificationService->notify([
            'store_id' => $purchase->store_id,
            'type' => NotificationType::PURCHASE_COMPLETED,
            'title' => 'Purchase Received',
            'message' => "Purchase #{$purchase->invoice_number} has been received and stock added (₹{$totalFormatted}).",
            'priority' => NotificationPriority::NORMAL,
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
            'alert_key' => "event:purchase:{$purchase->id}",
            'action_url' => route('store.purchases.show', $purchase->id),
            'metadata' => [
                'purchase_id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'grand_total' => (float) $purchase->grand_total,
            ],
        ]);
    }

    public function handleSalesReturnCompleted(SalesReturnCompleted $event): void
    {
        $return = $event->salesReturn;
        $totalFormatted = number_format($return->refund_amount, 2);

        $this->notificationService->notify([
            'store_id' => $return->store_id,
            'type' => NotificationType::SALES_RETURN_COMPLETED,
            'title' => 'Sales Return Processed',
            'message' => "Sales return #{$return->return_number} for ₹{$totalFormatted} was processed and inventory restocked.",
            'priority' => NotificationPriority::NORMAL,
            'reference_type' => SalesReturn::class,
            'reference_id' => $return->id,
            'alert_key' => "event:sales_return:{$return->id}",
            'action_url' => route('store.sales-returns.show', $return->id),
            'metadata' => [
                'sales_return_id' => $return->id,
                'return_number' => $return->return_number,
                'refund_amount' => (float) $return->refund_amount,
            ],
        ]);
    }

    public function handlePurchaseReturnCompleted(PurchaseReturnCompleted $event): void
    {
        $return = $event->purchaseReturn;
        $totalFormatted = number_format($return->refund_amount, 2);

        $this->notificationService->notify([
            'store_id' => $return->store_id,
            'type' => NotificationType::PURCHASE_RETURN_COMPLETED,
            'title' => 'Purchase Return Processed',
            'message' => "Purchase return #{$return->return_number} for ₹{$totalFormatted} was returned to supplier.",
            'priority' => NotificationPriority::NORMAL,
            'reference_type' => PurchaseReturn::class,
            'reference_id' => $return->id,
            'alert_key' => "event:purchase_return:{$return->id}",
            'action_url' => route('store.purchase-returns.show', $return->id),
            'metadata' => [
                'purchase_return_id' => $return->id,
                'return_number' => $return->return_number,
                'refund_amount' => (float) $return->refund_amount,
            ],
        ]);
    }

    public function handleCustomerPaymentRecorded(CustomerPaymentRecorded $event): void
    {
        $payment = $event->payment;
        $amountFormatted = number_format($payment->amount, 2);
        $customerName = $payment->customer?->name ?: 'Customer';

        $this->notificationService->notify([
            'store_id' => $payment->store_id,
            'type' => NotificationType::CUSTOMER_PAYMENT,
            'title' => 'Customer Payment Received',
            'message' => "Payment of ₹{$amountFormatted} received from {$customerName} (#{$payment->payment_number}).",
            'priority' => NotificationPriority::NORMAL,
            'reference_type' => StorePayment::class,
            'reference_id' => $payment->id,
            'alert_key' => "event:payment:cust:{$payment->id}",
            'action_url' => route('store.payments.show', $payment->id),
            'metadata' => [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'customer_id' => $payment->customer_id,
            ],
        ]);
    }

    public function handleSupplierPaymentRecorded(SupplierPaymentRecorded $event): void
    {
        $payment = $event->payment;
        $amountFormatted = number_format($payment->amount, 2);
        $supplierName = $payment->supplier?->name ?: 'Supplier';

        $this->notificationService->notify([
            'store_id' => $payment->store_id,
            'type' => NotificationType::SUPPLIER_PAYMENT,
            'title' => 'Supplier Payment Paid',
            'message' => "Payment of ₹{$amountFormatted} paid to {$supplierName} (#{$payment->payment_number}).",
            'priority' => NotificationPriority::NORMAL,
            'reference_type' => StorePayment::class,
            'reference_id' => $payment->id,
            'alert_key' => "event:payment:supp:{$payment->id}",
            'action_url' => route('store.payments.show', $payment->id),
            'metadata' => [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'supplier_id' => $payment->supplier_id,
            ],
        ]);
    }

    public function handleExpenseRecorded(ExpenseRecorded $event): void
    {
        $expense = $event->expense;
        $amountFormatted = number_format($expense->amount, 2);

        $this->notificationService->notify([
            'store_id' => $expense->store_id,
            'type' => NotificationType::EXPENSE_CREATED,
            'title' => 'Expense Recorded',
            'message' => "Expense #{$expense->expense_number} ({$expense->title}) of ₹{$amountFormatted} was recorded.",
            'priority' => NotificationPriority::LOW,
            'reference_type' => Expense::class,
            'reference_id' => $expense->id,
            'alert_key' => "event:expense:{$expense->id}",
            'action_url' => route('store.expenses.show', $expense->id),
            'metadata' => [
                'expense_id' => $expense->id,
                'amount' => (float) $expense->amount,
            ],
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            SaleCompleted::class => 'handleSaleCompleted',
            PurchaseCompleted::class => 'handlePurchaseCompleted',
            SalesReturnCompleted::class => 'handleSalesReturnCompleted',
            PurchaseReturnCompleted::class => 'handlePurchaseReturnCompleted',
            CustomerPaymentRecorded::class => 'handleCustomerPaymentRecorded',
            SupplierPaymentRecorded::class => 'handleSupplierPaymentRecorded',
            ExpenseRecorded::class => 'handleExpenseRecorded',
        ];
    }
}
