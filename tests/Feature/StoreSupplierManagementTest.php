<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PaymentMethod;
use App\Enums\PlanStatus;
use App\Enums\StorePaymentType;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\StorePayment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSupplierManagementTest extends TestCase
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
            'name' => 'Premium Plan',
            'slug' => 'premium-plan',
            'price' => 2499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_medicines' => 500,
                'max_batches' => 1000,
                'max_suppliers' => 100,
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'supplier_management',
                'purchase_management',
                'customer_payments',
                'supplier_payments',
            ],
        ]);

        // Store A
        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'City Care Pharmacy',
            'email' => 'citycare@example.com',
            'mobile' => '9876543201',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'owner.alpha@example.com',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        // Store B
        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Metro Drugs',
            'email' => 'metro@example.com',
            'mobile' => '9876543202',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'owner.beta@example.com',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);
    }

    public function test_can_create_supplier_with_auto_generated_code_pan_and_opening_balance(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->post(route('store.suppliers.store'), [
                'name' => 'Apex Healthcare Distributors',
                'company_name' => 'Apex Healthcare Pvt Ltd',
                'contact_person' => 'Rajesh Sharma',
                'phone' => '9820098200',
                'email' => 'contact@apexhealth.test',
                'gst_number' => '27ABCDE1234F1Z5',
                'pan_number' => 'ABCDE1234F',
                'opening_balance' => '5000.00',
                'opening_balance_type' => 'payable',
                'credit_limit' => '50000.00',
                'payment_terms' => 'Net 30',
                'status' => 'active',
            ]);

        $response->assertRedirect();

        $supplier = Supplier::where('store_id', $this->storeA->id)
            ->where('name', 'Apex Healthcare Distributors')
            ->first();

        $this->assertNotNull($supplier);
        $this->assertEquals('SUP-000001', $supplier->supplier_code);
        $this->assertEquals('ABCDE1234F', $supplier->pan_number);
        $this->assertEquals(5000.00, (float) $supplier->opening_balance);
        $this->assertEquals('payable', $supplier->opening_balance_type);
        $this->assertEquals(50000.00, (float) $supplier->credit_limit);
        $this->assertEquals(5000.00, $supplier->outstandingAmount());
    }

    public function test_second_supplier_gets_incremented_code(): void
    {
        Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Vendor 1',
            'phone' => '9820011111',
            'status' => 'active',
        ]);

        $code = Supplier::generateSupplierCode($this->storeA->id);
        $this->assertEquals('SUP-000002', $code);

        // Store B's first supplier should still start with SUP-000001
        $codeB = Supplier::generateSupplierCode($this->storeB->id);
        $this->assertEquals('SUP-000001', $codeB);
    }

    public function test_can_toggle_supplier_status(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Pharma Life',
            'phone' => '9820022222',
            'status' => 'active',
        ]);

        $this->actingAs($this->ownerA)
            ->patch(route('store.suppliers.toggle-status', $supplier))
            ->assertRedirect();

        $this->assertEquals('inactive', $supplier->fresh()->status);

        $this->actingAs($this->ownerA)
            ->patch(route('store.suppliers.toggle-status', $supplier))
            ->assertRedirect();

        $this->assertEquals('active', $supplier->fresh()->status);
    }

    public function test_cannot_delete_supplier_with_purchase_history(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Med Supply Hub',
            'phone' => '9820033333',
            'status' => 'active',
        ]);

        Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'INV-PUR-001',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 1000.00,
            'tax_amount' => 120.00,
            'grand_total' => 1120.00,
            'paid_amount' => 0.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->delete(route('store.suppliers.destroy', $supplier));

        $response->assertRedirect(route('store.suppliers.show', $supplier));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_can_delete_supplier_without_any_transactions(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Empty Vendor',
            'phone' => '9820044444',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->delete(route('store.suppliers.destroy', $supplier));

        $response->assertRedirect(route('store.suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_can_record_direct_supplier_payment_and_reduces_outstanding(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Direct Vendor',
            'phone' => '9820055555',
            'opening_balance' => '10000.00',
            'opening_balance_type' => 'payable',
            'status' => 'active',
        ]);

        $this->assertEquals(10000.00, $supplier->outstandingAmount());

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.suppliers.record-payment', $supplier), [
                'amount' => '4000.00',
                'payment_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
                'reference_number' => 'NEFT-998877',
                'notes' => 'Part payment of opening balance',
            ]);

        $response->assertRedirect(route('store.suppliers.show', $supplier));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('store_payments', [
            'store_id' => $this->storeA->id,
            'supplier_id' => $supplier->id,
            'type' => StorePaymentType::PURCHASE_PAYMENT->value,
            'amount' => 4000.00,
            'reference_number' => 'NEFT-998877',
        ]);

        $this->assertEquals(4000.00, $supplier->totalPaid());
        $this->assertEquals(6000.00, $supplier->outstandingAmount());
    }

    public function test_can_record_payment_allocated_to_purchase_invoice(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Invoice Vendor',
            'phone' => '9820066666',
            'opening_balance' => '0.00',
            'opening_balance_type' => 'payable',
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'BILL-2026-001',
            'purchase_date' => now()->toDateString(),
            'subtotal' => 2000.00,
            'tax_amount' => 0.00,
            'grand_total' => 2000.00,
            'paid_amount' => 0.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.suppliers.record-payment', $supplier), [
                'amount' => '2000.00',
                'payment_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::UPI->value,
                'purchase_id' => $purchase->id,
                'reference_number' => 'UPI-112233',
            ]);

        $response->assertRedirect(route('store.suppliers.show', $supplier));

        $purchase->refresh();
        $this->assertEquals(2000.00, (float) $purchase->paid_amount);
        $this->assertEquals('paid', $purchase->payment_status instanceof \BackedEnum ? $purchase->payment_status->value : $purchase->payment_status);
        $this->assertEquals(0.00, $supplier->outstandingAmount());
    }

    public function test_supplier_ledger_computes_running_balance_accurately(): void
    {
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Ledger Vendor',
            'phone' => '9820077777',
            'opening_balance' => '1000.00',
            'opening_balance_type' => 'payable',
            'status' => 'active',
        ]);

        // Purchase 1: ₹3000
        Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'PUR-001',
            'purchase_date' => Carbon::now()->subDays(3)->toDateString(),
            'subtotal' => 3000.00,
            'tax_amount' => 0.00,
            'grand_total' => 3000.00,
            'paid_amount' => 0.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        // Payment 1: ₹1500
        StorePayment::create([
            'store_id' => $this->storeA->id,
            'payment_number' => StorePayment::generatePaymentNumber($this->storeA->id),
            'type' => StorePaymentType::PURCHASE_PAYMENT,
            'payment_date' => Carbon::now()->subDays(2)->toDateString(),
            'amount' => 1500.00,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'supplier_id' => $supplier->id,
            'status' => 'completed',
        ]);

        // Net expected: Opening(1000) + Purchase(3000) - Payment(1500) = 2500
        $response = $this->actingAs($this->ownerA)
            ->get(route('store.suppliers.ledger', $supplier));

        $response->assertOk();
        $response->assertViewHas('periodOpeningBalance', 1000.00);
        $response->assertViewHas('totalDebits', 3000.00);
        $response->assertViewHas('totalCredits', 1500.00);
        $response->assertViewHas('closingBalance', 2500.00);

        // Also test print ledger view
        $printResponse = $this->actingAs($this->ownerA)
            ->get(route('store.suppliers.ledger.print', $supplier));
        $printResponse->assertOk();
        $printResponse->assertSee('Supplier Ledger Statement');
        $printResponse->assertSee('Ledger Vendor');
    }

    public function test_outstanding_report_displays_store_suppliers_and_filters(): void
    {
        $supplier1 = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Supplier Due',
            'phone' => '9820088888',
            'opening_balance' => '5000.00',
            'opening_balance_type' => 'payable',
            'credit_limit' => '3000.00', // Exceeds limit
            'status' => 'active',
        ]);

        $supplier2 = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000002',
            'name' => 'Supplier Advance',
            'phone' => '9820099999',
            'opening_balance' => '2000.00',
            'opening_balance_type' => 'advance', // Advance
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get(route('store.suppliers.outstanding'));

        $response->assertOk();
        $response->assertViewHas('totalPayable', 5000.00);
        $response->assertViewHas('totalAdvance', 2000.00);
        $response->assertViewHas('netPayable', 3000.00);
        $response->assertViewHas('suppliersWithDueCount', 1);
        $response->assertViewHas('suppliersOverLimitCount', 1);

        // Filter: has_due
        $dueResponse = $this->actingAs($this->ownerA)
            ->get(route('store.suppliers.outstanding', ['filter' => 'has_due']));
        $dueResponse->assertOk();
        $dueResponse->assertSee('Supplier Due');
        $dueResponse->assertDontSee('Supplier Advance');

        // Filter: over_limit
        $limitResponse = $this->actingAs($this->ownerA)
            ->get(route('store.suppliers.outstanding', ['filter' => 'over_limit']));
        $limitResponse->assertOk();
        $limitResponse->assertSee('Supplier Due');
    }

    public function test_multi_tenant_isolation_store_b_cannot_access_store_a_supplier(): void
    {
        $supplierA = Supplier::create([
            'store_id' => $this->storeA->id,
            'supplier_code' => 'SUP-000001',
            'name' => 'Confidential Vendor A',
            'phone' => '9820012345',
            'status' => 'active',
        ]);

        // Store B owner cannot view show page
        $this->actingAs($this->ownerB)
            ->get(route('store.suppliers.show', $supplierA))
            ->assertNotFound();

        // Store B owner cannot view ledger
        $this->actingAs($this->ownerB)
            ->get(route('store.suppliers.ledger', $supplierA))
            ->assertNotFound();

        // Store B owner cannot record payment
        $this->actingAs($this->ownerB)
            ->post(route('store.suppliers.record-payment', $supplierA), [
                'amount' => '500.00',
                'payment_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::CASH->value,
            ])
            ->assertNotFound();

        // Store B owner index does not list Store A's supplier
        $this->actingAs($this->ownerB)
            ->get(route('store.suppliers.index'))
            ->assertOk()
            ->assertDontSee('Confidential Vendor A');
    }
}
