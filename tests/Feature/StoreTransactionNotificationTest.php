<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Enums\PurchaseStatus;
use App\Enums\SaleStatus;
use App\Enums\StorePaymentType;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Events\CustomerPaymentRecorded;
use App\Events\ExpenseRecorded;
use App\Events\PurchaseCompleted;
use App\Events\SaleCompleted;
use App\Events\SupplierPaymentRecorded;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\NotificationPreference;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StorePayment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTransactionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create([
            'code' => 'TXN-001',
            'name' => 'Transaction Testing Pharmacy',
            'email' => 'txn@pharmacy.test',
            'mobile' => '9833322211',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Txn Owner',
            'email' => 'txnowner@pharmacy.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->store->id,
        ]);
    }

    public function test_sale_completed_event_creates_notification(): void
    {
        $sale = Sale::create([
            'store_id' => $this->store->id,
            'customer_name' => 'Vikram Singh',
            'invoice_number' => 'INV-TX-001',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 500.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 500.00,
            'paid_amount' => 500.00,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        event(new SaleCompleted($sale));

        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::SALE_COMPLETED->value,
            'alert_key' => "event:sale:{$sale->id}",
            'reference_id' => $sale->id,
        ]);
    }

    public function test_purchase_completed_event_creates_notification(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->store->id,
            'name' => 'Cipla Distribution',
            'phone' => '9988776655',
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'store_id' => $this->store->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'PUR-TX-001',
            'purchase_date' => now()->toDateString(),
            'status' => PurchaseStatus::COMPLETED,
            'subtotal' => 5000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 5000.00,
            'paid_amount' => 5000.00,
            'payment_status' => 'paid',
        ]);

        event(new PurchaseCompleted($purchase));

        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::PURCHASE_COMPLETED->value,
            'alert_key' => "event:purchase:{$purchase->id}",
            'reference_id' => $purchase->id,
        ]);
    }

    public function test_customer_and_supplier_payment_events_create_notifications(): void
    {
        $customer = Customer::create([
            'store_id' => $this->store->id,
            'customer_code' => 'CUS-PAY-1',
            'name' => 'Amit Patel',
            'phone' => '9876543211',
            'status' => 'active',
        ]);

        $custPayment = StorePayment::create([
            'store_id' => $this->store->id,
            'payment_number' => 'PAY-CUST-01',
            'type' => StorePaymentType::SALE_PAYMENT,
            'payment_date' => now()->toDateString(),
            'amount' => 750.00,
            'payment_method' => PaymentMethod::UPI->value,
            'customer_id' => $customer->id,
        ]);

        event(new CustomerPaymentRecorded($custPayment));

        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::CUSTOMER_PAYMENT->value,
            'alert_key' => "event:payment:cust:{$custPayment->id}",
        ]);

        $supplier = Supplier::create([
            'store_id' => $this->store->id,
            'name' => 'Sun Pharma Distributors',
            'phone' => '9876543212',
            'status' => 'active',
        ]);

        $suppPayment = StorePayment::create([
            'store_id' => $this->store->id,
            'payment_number' => 'PAY-SUPP-01',
            'type' => StorePaymentType::PURCHASE_PAYMENT,
            'payment_date' => now()->toDateString(),
            'amount' => 3000.00,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'supplier_id' => $supplier->id,
        ]);

        event(new SupplierPaymentRecorded($suppPayment));

        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::SUPPLIER_PAYMENT->value,
            'alert_key' => "event:payment:supp:{$suppPayment->id}",
        ]);
    }

    public function test_notification_skipped_when_preference_is_disabled(): void
    {
        // Disable expense notifications
        NotificationPreference::create([
            'store_id' => $this->store->id,
            'user_id' => null,
            'notification_type' => NotificationType::EXPENSE_CREATED->value,
            'is_enabled' => false,
        ]);

        $category = ExpenseCategory::create([
            'store_id' => $this->store->id,
            'name' => 'Utilities',
            'status' => 'active',
        ]);

        $expense = Expense::create([
            'store_id' => $this->store->id,
            'expense_category_id' => $category->id,
            'expense_number' => 'EXP-TX-001',
            'title' => 'Electricity Bill',
            'expense_date' => now()->toDateString(),
            'amount' => 1200.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        event(new ExpenseRecorded($expense));

        $this->assertDatabaseMissing('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::EXPENSE_CREATED->value,
            'alert_key' => "event:expense:{$expense->id}",
        ]);
    }
}
