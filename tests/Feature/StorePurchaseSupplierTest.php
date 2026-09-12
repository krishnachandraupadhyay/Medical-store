<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\PurchaseStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePurchaseSupplierTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

    protected Supplier $supplierA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = SubscriptionPlan::create([
            'name' => 'Complete Tier',
            'slug' => 'complete-tier',
            'price' => 1499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_medicines' => 50,
                'max_batches' => 100,
                'max_suppliers' => 2, // Quota of 2 suppliers for limit test
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'supplier_management',
                'purchase_management',
            ],
        ]);

        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'Store Alpha',
            'email' => 'alpha@medistore.test',
            'mobile' => '9876543201',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'alpha@owner.test',
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

        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Store Beta',
            'email' => 'beta@medistore.test',
            'mobile' => '9876543202',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'beta@owner.test',
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

        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Amoxicillin 500mg',
            'generic_name' => 'Amoxicillin',
            'reorder_level' => 15,
            'gst_rate' => 12.00,
            'status' => 'active',
        ]);

        $this->supplierA = Supplier::create([
            'store_id' => $this->storeA->id,
            'name' => 'MedSupply India Ltd',
            'phone' => '9988776655',
            'email' => 'orders@medsupply.test',
            'city' => 'Mumbai',
            'status' => 'active',
        ]);
    }

    public function test_store_owner_can_list_and_create_supplier(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('store.suppliers.index'));
        $response->assertOk();
        $response->assertSee('MedSupply India Ltd');

        $payload = [
            'name' => 'Apollo Pharma Wholesaler',
            'company_name' => 'Apollo Distribution LLP',
            'phone' => '9822334455',
            'email' => 'apollo@distributors.test',
            'gst_number' => '27ABCDE1234F1Z5',
            'status' => 'active',
        ];

        $createResponse = $this->actingAs($this->ownerA)->post(route('store.suppliers.store'), $payload);
        $createResponse->assertRedirect();

        $this->assertDatabaseHas('suppliers', [
            'store_id' => $this->storeA->id,
            'name' => 'Apollo Pharma Wholesaler',
            'gst_number' => '27ABCDE1234F1Z5',
        ]);
    }

    public function test_supplier_quota_limit_enforced(): void
    {
        // Limit is 2 in setUp. We currently have 1 ($this->supplierA).
        // Let's add the 2nd supplier:
        Supplier::create([
            'store_id' => $this->storeA->id,
            'name' => 'Second Supplier',
            'phone' => '9900112233',
            'status' => 'active',
        ]);

        // Now creating a 3rd supplier must be blocked by quota
        $payload = [
            'name' => 'Third Supplier Overflow',
            'phone' => '9911223344',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.suppliers.store'), $payload);
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('suppliers', [
            'name' => 'Third Supplier Overflow',
        ]);
    }

    public function test_store_owner_can_update_supplier(): void
    {
        $payload = [
            'name' => 'MedSupply Global Ltd',
            'phone' => '9988776600',
            'email' => 'updated@medsupply.test',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->put(route('store.suppliers.update', $this->supplierA), $payload);
        $response->assertRedirect(route('store.suppliers.show', $this->supplierA));

        $this->assertEquals('MedSupply Global Ltd', $this->supplierA->fresh()->name);
        $this->assertEquals('9988776600', $this->supplierA->fresh()->phone);
    }

    public function test_supplier_with_purchases_cannot_be_deleted(): void
    {
        Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-TEST-DELETE',
            'purchase_date' => Carbon::today(),
            'status' => PurchaseStatus::DRAFT,
            'grand_total' => 500.00,
        ]);

        $response = $this->actingAs($this->ownerA)->delete(route('store.suppliers.destroy', $this->supplierA));

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted($this->supplierA);
    }

    public function test_store_owner_can_create_purchase_as_draft_without_affecting_stock(): void
    {
        $payload = [
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-DRAFT-101',
            'purchase_date' => Carbon::today()->toDateString(),
            'status' => 'draft',
            'discount' => '50.00',
            'notes' => 'Draft invoice awaiting warehouse arrival',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_number' => 'BATCH-AMOX-01',
                    'expiry_date' => Carbon::today()->addYear()->toDateString(),
                    'quantity' => 100,
                    'free_quantity' => 10,
                    'purchase_price' => '20.00',
                    'mrp' => '30.00',
                    'selling_price' => '28.00',
                    'gst_rate' => '12.00',
                ],
            ],
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.purchases.store'), $payload);
        $response->assertRedirect();

        $purchase = Purchase::where('invoice_number', 'INV-DRAFT-101')->firstOrFail();
        $this->assertEquals(PurchaseStatus::DRAFT, $purchase->status);
        $this->assertCount(1, $purchase->items);

        // Crucial verification: draft purchase MUST NOT create active batch stock or movement
        $this->assertDatabaseMissing('batches', [
            'store_id' => $this->storeA->id,
            'batch_number' => 'BATCH-AMOX-01',
        ]);
        $this->assertDatabaseMissing('stock_movements', [
            'store_id' => $this->storeA->id,
            'type' => MovementType::PURCHASE_IN->value,
        ]);
    }

    public function test_store_owner_can_update_draft_purchase_items(): void
    {
        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-DRAFT-EDIT',
            'purchase_date' => Carbon::today(),
            'status' => PurchaseStatus::DRAFT,
            'subtotal' => 200.00,
            'grand_total' => 200.00,
        ]);

        $purchase->items()->create([
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-OLD',
            'expiry_date' => Carbon::today()->addMonths(6),
            'quantity' => 10,
            'purchase_price' => 20.00,
            'mrp' => 25.00,
            'line_total' => 200.00,
        ]);

        $updatePayload = [
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-DRAFT-EDIT',
            'purchase_date' => Carbon::today()->toDateString(),
            'discount' => '0.00',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_number' => 'B-NEW-REVISED',
                    'expiry_date' => Carbon::today()->addMonths(12)->toDateString(),
                    'quantity' => 20,
                    'free_quantity' => 2,
                    'purchase_price' => '22.00',
                    'mrp' => '30.00',
                    'gst_rate' => '0.00',
                ],
            ],
        ];

        $response = $this->actingAs($this->ownerA)->put(route('store.purchases.update', $purchase), $updatePayload);
        $response->assertRedirect(route('store.purchases.show', $purchase));

        $fresh = $purchase->fresh();
        $this->assertCount(1, $fresh->items);
        $this->assertEquals('B-NEW-REVISED', $fresh->items->first()->batch_number);
        $this->assertEquals(20, $fresh->items->first()->quantity);
        $this->assertEquals(440.00, (float) $fresh->grand_total);
    }

    public function test_completing_draft_purchase_ingests_stock_and_creates_stock_movements(): void
    {
        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-COMPLETE-01',
            'purchase_date' => Carbon::today(),
            'status' => PurchaseStatus::DRAFT,
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
        ]);

        $purchase->items()->create([
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'BATCH-COMPL-A',
            'expiry_date' => Carbon::today()->addYear(),
            'quantity' => 50,
            'free_quantity' => 5, // Total 55 units
            'purchase_price' => 20.00,
            'mrp' => 30.00,
            'selling_price' => 28.00,
            'line_total' => 1000.00,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.purchases.complete', $purchase));
        $response->assertRedirect(route('store.purchases.show', $purchase));

        $freshPurchase = $purchase->fresh();
        $this->assertEquals(PurchaseStatus::COMPLETED, $freshPurchase->status);
        $this->assertNotNull($freshPurchase->completed_at);

        // Assert Batch record was created with total received quantity (50 + 5 = 55)
        $batch = Batch::where('store_id', $this->storeA->id)
            ->where('batch_number', 'BATCH-COMPL-A')
            ->first();

        $this->assertNotNull($batch);
        $this->assertEquals(55, $batch->quantity);
        $this->assertEquals(20.00, $batch->purchase_price);

        // Assert Stock Movement ledger record
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $batch->id,
            'type' => MovementType::PURCHASE_IN->value,
            'quantity' => 55,
            'before_quantity' => 0,
            'after_quantity' => 55,
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
        ]);
    }

    public function test_store_owner_can_create_purchase_directly_as_completed(): void
    {
        $payload = [
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-DIRECT-COMPL',
            'purchase_date' => Carbon::today()->toDateString(),
            'status' => 'completed',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_number' => 'BATCH-DIRECT-1',
                    'expiry_date' => Carbon::today()->addMonths(18)->toDateString(),
                    'quantity' => 25,
                    'free_quantity' => 0,
                    'purchase_price' => '15.00',
                    'mrp' => '25.00',
                ],
            ],
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.purchases.store'), $payload);
        $response->assertRedirect();

        $purchase = Purchase::where('invoice_number', 'INV-DIRECT-COMPL')->firstOrFail();
        $this->assertEquals(PurchaseStatus::COMPLETED, $purchase->status);

        $batch = Batch::where('store_id', $this->storeA->id)->where('batch_number', 'BATCH-DIRECT-1')->firstOrFail();
        $this->assertEquals(25, $batch->quantity);
    }

    public function test_store_owner_can_cancel_draft_purchase(): void
    {
        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-TO-CANCEL',
            'purchase_date' => Carbon::today(),
            'status' => PurchaseStatus::DRAFT,
            'grand_total' => 100.00,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.purchases.cancel', $purchase), [
            'reason' => 'Vendor cancelled delivery',
        ]);

        $response->assertRedirect(route('store.purchases.show', $purchase));
        $this->assertEquals(PurchaseStatus::CANCELLED, $purchase->fresh()->status);
        $this->assertStringContainsString('Vendor cancelled delivery', $purchase->fresh()->notes);
    }

    public function test_completed_purchase_cannot_be_cancelled(): void
    {
        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-LOCKED-COMPL',
            'purchase_date' => Carbon::today(),
            'status' => PurchaseStatus::COMPLETED,
            'grand_total' => 100.00,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.purchases.cancel', $purchase), [
            'reason' => 'Should fail',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(PurchaseStatus::COMPLETED, $purchase->fresh()->status);
    }

    public function test_multi_tenant_isolation_for_suppliers_and_purchases(): void
    {
        $purchaseA = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'INV-STORE-A-ONLY',
            'purchase_date' => Carbon::today(),
            'status' => PurchaseStatus::DRAFT,
            'grand_total' => 300.00,
        ]);

        // Owner B attempts to view Supplier A
        $responseSupplier = $this->actingAs($this->ownerB)->get(route('store.suppliers.show', $this->supplierA));
        $responseSupplier->assertNotFound();

        // Owner B attempts to view Purchase A
        $responsePurchase = $this->actingAs($this->ownerB)->get(route('store.purchases.show', $purchaseA));
        $responsePurchase->assertNotFound();

        // Owner B purchase index does not see Store A's invoices
        $responseIndex = $this->actingAs($this->ownerB)->get(route('store.purchases.index'));
        $responseIndex->assertDontSee('INV-STORE-A-ONLY');
    }

    public function test_supplier_and_purchase_routes_forbidden_without_feature_subscription(): void
    {
        $restrictedPlan = SubscriptionPlan::create([
            'name' => 'Plan No Purchases',
            'slug' => 'plan-no-purchases',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'status' => PlanStatus::ACTIVE,
            'features' => ['medicine_management'], // No supplier_management or purchase_management
        ]);

        Subscription::where('store_id', $this->storeA->id)->update([
            'subscription_plan_id' => $restrictedPlan->id,
        ]);

        // Attempting to access suppliers
        $resSupplier = $this->actingAs($this->ownerA)->get(route('store.suppliers.index'));
        $resSupplier->assertForbidden();

        // Attempting to access purchases
        $resPurchase = $this->actingAs($this->ownerA)->get(route('store.purchases.index'));
        $resPurchase->assertForbidden();
    }
}
