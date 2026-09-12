<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Enums\StorePaymentType;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StorePayment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSalesReturnManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $fullPlan;

    protected SubscriptionPlan $restrictedPlan;

    protected Customer $customerA;

    protected Medicine $medicineA;

    protected Batch $batchA;

    protected Sale $saleA;

    protected SaleItem $saleItemA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fullPlan = SubscriptionPlan::create([
            'name' => 'All Inclusive Pharmacy Plan',
            'slug' => 'all-inclusive-pharmacy-plan',
            'price' => 2999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [],
            'features' => [
                'medicine_management',
                'inventory_management',
                'customer_management',
                'sales_management',
                'sales_return',
                'reports',
            ],
        ]);

        $this->restrictedPlan = SubscriptionPlan::create([
            'name' => 'Restricted Plan No Returns',
            'slug' => 'restricted-plan-no-returns',
            'price' => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [],
            'features' => [
                'medicine_management',
                'sales_management',
            ],
        ]);

        // Store A
        $this->storeA = Store::create([
            'code' => 'AP-001',
            'name' => 'Apex Healthcare Pharmacy',
            'email' => 'contact@apexhealth.test',
            'mobile' => '9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'tax_number' => '27AAAAA0000A1Z5',
            'dl_number' => 'DL-20B-123456',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Apex Owner',
            'email' => 'owner@apexhealth.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->fullPlan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->fullPlan->features,
        ]);

        // Store B
        $this->storeB = Store::create([
            'code' => 'BP-002',
            'name' => 'Beacon Medical Store',
            'email' => 'contact@beacon.test',
            'mobile' => '9123456780',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Beacon Owner',
            'email' => 'owner@beacon.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->fullPlan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->fullPlan->features,
        ]);

        // Customer in Store A
        $this->customerA = Customer::create([
            'store_id' => $this->storeA->id,
            'name' => 'Rahul Sharma',
            'phone' => '9820011223',
            'customer_code' => 'CUST-000001',
            'status' => 'active',
            'loyalty_tier' => 'regular',
        ]);

        // Medicine in Store A
        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Augmentin 625 Duo',
            'status' => 'active',
        ]);

        // Batch in Store A (initially 30 remaining after selling 20)
        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AUG-625-X',
            'expiry_date' => Carbon::now()->addMonths(12),
            'purchase_price' => 120.00,
            'selling_price' => 180.00,
            'mrp' => 200.00,
            'quantity' => 30,
            'status' => 'active',
        ]);

        // Sale in Store A (20 units @ 180 = 3600)
        $this->saleA = Sale::create([
            'store_id' => $this->storeA->id,
            'customer_id' => $this->customerA->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9820011223',
            'invoice_number' => 'INV-2026-000100',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 3600.00,
            'tax' => 0.00,
            'grand_total' => 3600.00,
            'paid_amount' => 3600.00,
            'payment_status' => PaymentStatus::PAID,
            'completed_at' => now(),
        ]);

        $this->saleItemA = SaleItem::create([
            'sale_id' => $this->saleA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 20,
            'unit_price' => 180.00,
            'mrp' => 200.00,
            'gst_rate' => 0.00,
            'line_total' => 3600.00,
        ]);
    }

    public function test_full_sales_return_restores_stock_and_creates_refund_ledger(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Patient stopped course by doctor',
            'refund_method' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 10,
                    'reason' => 'Unopened foil strip',
                ],
            ],
        ]);

        $salesReturn = SalesReturn::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($salesReturn);
        $response->assertRedirect(route('store.sales-returns.show', $salesReturn->id));

        $this->assertEquals(ReturnStatus::COMPLETED, $salesReturn->status);
        $this->assertEquals(1800.00, $salesReturn->grand_total);
        $this->assertEquals(1800.00, $salesReturn->refund_amount);
        $this->assertEquals(0.00, $salesReturn->adjustment_amount);
        $this->assertEquals('cash', $salesReturn->refund_method);

        // Stock restored from 30 to 40
        $this->batchA->refresh();
        $this->assertEquals(40, $this->batchA->quantity);

        // Stock movement
        $movement = StockMovement::where('store_id', $this->storeA->id)
            ->where('type', MovementType::SALES_RETURN_IN)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->quantity);
        $this->assertEquals(30, $movement->before_quantity);
        $this->assertEquals(40, $movement->after_quantity);

        // Refund ledger payment record created
        $payment = StorePayment::where('store_id', $this->storeA->id)
            ->where('type', StorePaymentType::SALE_REFUND)
            ->first();
        $this->assertNotNull($payment);
        $this->assertEquals(1800.00, $payment->amount);
        $this->assertEquals($salesReturn->return_number, $payment->reference_number);
    }

    public function test_partial_returns_track_max_returnable_quantities(): void
    {
        // First return 6 units
        $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'First partial return',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 6,
                ],
            ],
        ]);

        $this->assertEquals(14, $this->saleItemA->returnableQuantity());

        // Second return 10 units
        $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Second partial return',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 10,
                ],
            ],
        ]);

        $this->assertEquals(4, $this->saleItemA->returnableQuantity());

        // Attempt to return 5 units (exceeds remaining 4)
        $response = $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Excess return attempt',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->assertEquals(4, $this->saleItemA->returnableQuantity());
    }

    public function test_sales_return_adjusts_outstanding_due_on_unpaid_sale(): void
    {
        // Sale with outstanding due (Grand Total: 2000, Paid: 1200, Due: 800)
        $unpaidSale = Sale::create([
            'store_id' => $this->storeA->id,
            'customer_id' => $this->customerA->id,
            'customer_name' => 'Rahul Sharma',
            'invoice_number' => 'INV-2026-000101',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 2000.00,
            'grand_total' => 2000.00,
            'paid_amount' => 1200.00,
            'payment_status' => PaymentStatus::PARTIAL,
            'completed_at' => now(),
        ]);

        $unpaidItem = SaleItem::create([
            'sale_id' => $unpaidSale->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 10,
            'unit_price' => 200.00,
            'mrp' => 200.00,
            'line_total' => 2000.00,
        ]);

        $this->assertEquals(800.00, $unpaidSale->outstandingAmount());

        // Return 4 units (4 x 200 = 800)
        $response = $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $unpaidSale->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Clear dues with returned medicine',
            'items' => [
                [
                    'sale_item_id' => $unpaidItem->id,
                    'quantity' => 4,
                ],
            ],
        ]);

        $salesReturn = SalesReturn::where('sale_id', $unpaidSale->id)->first();
        $this->assertNotNull($salesReturn);
        $this->assertEquals(800.00, $salesReturn->grand_total);
        $this->assertEquals(800.00, $salesReturn->adjustment_amount); // Adjusted against due
        $this->assertEquals(0.00, $salesReturn->refund_amount); // No cash paid out

        // Sale outstanding is now 0 and status is PAID
        $unpaidSale->refresh();
        $this->assertEquals(0.00, $unpaidSale->outstandingAmount());
        $this->assertEquals(PaymentStatus::PAID, $unpaidSale->payment_status);

        // No SALE_REFUND payment record created since no cash/upi was paid out
        $refundPayment = StorePayment::where('reference_number', $salesReturn->return_number)->first();
        $this->assertNull($refundPayment);
    }

    public function test_split_due_adjustment_and_cash_refund(): void
    {
        // Sale of 1000, paid 700, due 300
        $sale = Sale::create([
            'store_id' => $this->storeA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV-2026-000102',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
            'paid_amount' => 700.00,
            'payment_status' => PaymentStatus::PARTIAL,
            'completed_at' => now(),
        ]);

        $item = SaleItem::create([
            'sale_id' => $sale->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 5,
            'unit_price' => 200.00,
            'mrp' => 220.00,
            'line_total' => 1000.00,
        ]);

        // Customer returns 3 units = 600
        // Due was 300. So 300 should adjust due, and 300 should be refunded.
        $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $sale->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Split return',
            'refund_method' => 'upi',
            'items' => [
                [
                    'sale_item_id' => $item->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $salesReturn = SalesReturn::where('sale_id', $sale->id)->first();
        $this->assertEquals(600.00, $salesReturn->grand_total);
        $this->assertEquals(300.00, $salesReturn->adjustment_amount);
        $this->assertEquals(300.00, $salesReturn->refund_amount);
        $this->assertEquals('upi', $salesReturn->refund_method);

        // A StorePayment refund was created for 300
        $payment = StorePayment::where('reference_number', $salesReturn->return_number)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(300.00, $payment->amount);

        // Sale is now completely settled
        $sale->refresh();
        $this->assertEquals(0.00, $sale->outstandingAmount());
        $this->assertEquals(PaymentStatus::PAID, $sale->payment_status);
    }

    public function test_printable_return_receipt_renders_successfully(): void
    {
        $salesReturn = SalesReturn::create([
            'store_id' => $this->storeA->id,
            'sale_id' => $this->saleA->id,
            'customer_id' => $this->customerA->id,
            'return_number' => 'SR-2026-000001',
            'return_date' => now()->toDateString(),
            'status' => ReturnStatus::COMPLETED,
            'subtotal' => 900.00,
            'tax' => 0.00,
            'grand_total' => 900.00,
            'refund_amount' => 900.00,
            'adjustment_amount' => 0.00,
            'refund_method' => 'cash',
            'refund_status' => 'completed',
            'reason' => 'Patient stopped therapy',
            'created_by' => $this->ownerA->id,
        ]);

        $salesReturn->items()->create([
            'sale_item_id' => $this->saleItemA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 5,
            'unit_price' => 180.00,
            'line_total' => 900.00,
            'reason' => 'Intact blister',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.sales-returns.receipt', $salesReturn->id));

        $response->assertStatus(200);
        $response->assertSee('SR-2026-000001');
        $response->assertSee('Apex Healthcare Pharmacy');
        $response->assertSee('Rahul Sharma');
        $response->assertSee('Augmentin 625 Duo');
        $response->assertSee('INV-2026-000100');
        $response->assertSee('900.00');
    }

    public function test_tenant_isolation_prevents_cross_store_access(): void
    {
        $salesReturnA = SalesReturn::create([
            'store_id' => $this->storeA->id,
            'sale_id' => $this->saleA->id,
            'return_number' => 'SR-2026-000002',
            'return_date' => now()->toDateString(),
            'status' => ReturnStatus::COMPLETED,
            'subtotal' => 500.00,
            'grand_total' => 500.00,
            'reason' => 'Store A return',
        ]);

        // Owner B tries to view Store A's return details
        $response = $this->actingAs($this->ownerB)->get(route('store.sales-returns.show', $salesReturnA->id));
        $response->assertStatus(404);

        // Owner B tries to view Store A's return receipt
        $responseReceipt = $this->actingAs($this->ownerB)->get(route('store.sales-returns.receipt', $salesReturnA->id));
        $responseReceipt->assertStatus(404);

        // Owner B tries to submit return against Store A's sale
        $responseStore = $this->actingAs($this->ownerB)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Malicious',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $responseStore->assertStatus(404);
    }

    public function test_feature_access_gate_blocks_returns_when_subscription_disallows_it(): void
    {
        // Switch Store A to restricted plan without 'sales_return'
        $sub = Subscription::where('store_id', $this->storeA->id)->first();
        $sub->update([
            'subscription_plan_id' => $this->restrictedPlan->id,
            'features' => $this->restrictedPlan->features,
        ]);

        $responseIndex = $this->actingAs($this->ownerA)->get(route('store.sales-returns.index'));
        $responseIndex->assertStatus(403);

        $responseCreate = $this->actingAs($this->ownerA)->get(route('store.sales-returns.create', ['sale_id' => $this->saleA->id]));
        $responseCreate->assertStatus(403);
    }
}
