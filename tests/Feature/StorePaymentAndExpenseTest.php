<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\PurchaseStatus;
use App\Enums\SaleStatus;
use App\Enums\StorePaymentType;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StorePayment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePaymentAndExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = SubscriptionPlan::create([
            'name' => 'Complete Plan',
            'slug' => 'complete-plan',
            'price' => 2499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'features' => [
                'expense_management',
                'customer_management',
                'supplier_management',
                'sales_management',
                'purchase_management',
                'reports',
            ],
            'limits' => [],
        ]);

        $this->storeA = Store::create([
            'code' => 'EXP-001',
            'name' => 'Elite Pharmacy A',
            'email' => 'elite@store.test',
            'mobile' => '9888877771',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner A',
            'email' => 'ownera@elite.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->plan->features,
        ]);

        $this->storeB = Store::create([
            'code' => 'EXP-002',
            'name' => 'Elite Pharmacy B',
            'email' => 'eliteb@store.test',
            'mobile' => '9888877772',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110002',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner B',
            'email' => 'ownerb@elite.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->plan->features,
        ]);
    }

    public function test_can_create_expense_category_and_paid_expense(): void
    {
        $catResponse = $this->actingAs($this->ownerA)->post(route('store.expense-categories.store'), [
            'name' => 'Utilities & Electricity',
            'description' => 'Monthly electricity bill',
        ]);
        $catResponse->assertRedirect(route('store.expense-categories.index'));

        $category = ExpenseCategory::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($category);
        $this->assertEquals('Utilities & Electricity', $category->name);

        $expResponse = $this->actingAs($this->ownerA)->post(route('store.expenses.store'), [
            'category_id' => $category->id,
            'title' => 'Electricity Bill August',
            'amount' => 3500.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'upi',
            'status' => 'paid',
            'notes' => 'Paid via GPay',
        ]);

        $expense = Expense::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($expense);
        $expResponse->assertRedirect(route('store.expenses.show', $expense->id));
        $this->assertEquals(3500.00, $expense->amount);
        $this->assertEquals(ExpenseStatus::PAID, $expense->status);

        // Verify StorePayment was automatically created
        $payment = StorePayment::where('store_id', $this->storeA->id)
            ->where('expense_id', $expense->id)
            ->first();
        $this->assertNotNull($payment);
        $this->assertEquals(3500.00, $payment->amount);
        $this->assertEquals(StorePaymentType::EXPENSE_PAYMENT, $payment->type);
    }

    public function test_cancelling_expense_cancels_associated_payment(): void
    {
        $category = ExpenseCategory::create([
            'store_id' => $this->storeA->id,
            'name' => 'Office Stationery',
        ]);

        $this->actingAs($this->ownerA)->post(route('store.expenses.store'), [
            'category_id' => $category->id,
            'title' => 'Printer Paper',
            'amount' => 450.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $expense = Expense::where('store_id', $this->storeA->id)->first();
        $payment = StorePayment::where('expense_id', $expense->id)->first();
        $this->assertEquals('completed', $payment->status);

        $this->actingAs($this->ownerA)->post(route('store.expenses.cancel', $expense->id), [
            'reason' => 'Returned paper to vendor',
        ]);

        $expense->refresh();
        $payment->refresh();
        $this->assertEquals(ExpenseStatus::CANCELLED, $expense->status);
        $this->assertEquals('cancelled', $payment->status);
    }

    public function test_record_customer_payment_against_partial_sale(): void
    {
        $customer = Customer::create([
            'store_id' => $this->storeA->id,
            'name' => 'Ramesh Kumar',
            'phone' => '9988776655',
            'customer_code' => 'CUST-001',
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'store_id' => $this->storeA->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-001',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
            'paid_amount' => 400.00,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.sales.record-payment', $sale->id), [
            'amount' => 600.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'RECEIPT-001',
        ]);

        $response->assertRedirect(route('store.sales.show', $sale->id));

        $sale->refresh();
        $this->assertEquals(1000.00, $sale->paid_amount);
        $this->assertEquals(PaymentStatus::PAID, $sale->payment_status);

        $payment = StorePayment::where('sale_id', $sale->id)->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(600.00, $payment->amount);
        $this->assertEquals(StorePaymentType::SALE_PAYMENT, $payment->type);
    }

    public function test_record_supplier_payment_against_purchase(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'name' => 'Cipla Distro',
            'phone' => '9111222333',
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'PO-TEST-001',
            'purchase_date' => now()->toDateString(),
            'status' => PurchaseStatus::COMPLETED,
            'subtotal' => 10000.00,
            'grand_total' => 10000.00,
            'paid_amount' => 3000.00,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.purchases.record-payment', $purchase->id), [
            'amount' => 7000.00,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'NEFT-999888',
        ]);

        $response->assertRedirect(route('store.purchases.show', $purchase->id));

        $purchase->refresh();
        $this->assertEquals(10000.00, $purchase->paid_amount);
        $this->assertEquals(PaymentStatus::PAID, $purchase->payment_status);

        $payment = StorePayment::where('purchase_id', $purchase->id)->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(7000.00, $payment->amount);
        $this->assertEquals(StorePaymentType::PURCHASE_PAYMENT, $payment->type);
    }

    public function test_cross_store_payment_and_expense_isolation(): void
    {
        $categoryA = ExpenseCategory::create([
            'store_id' => $this->storeA->id,
            'name' => 'Store A Category',
        ]);

        $expenseA = Expense::create([
            'store_id' => $this->storeA->id,
            'expense_category_id' => $categoryA->id,
            'expense_number' => 'EXP-A-001',
            'title' => 'Secret Expense',
            'amount' => 1200.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::CASH,
            'status' => ExpenseStatus::PAID,
        ]);

        // Owner B tries to view Store A's expense
        $response = $this->actingAs($this->ownerB)->get(route('store.expenses.show', $expenseA->id));
        $response->assertStatus(404);

        // Owner B tries to cancel Store A's expense
        $cancelResponse = $this->actingAs($this->ownerB)->post(route('store.expenses.cancel', $expenseA->id), [
            'reason' => 'Hack attempt',
        ]);
        $cancelResponse->assertStatus(404);
    }
}
