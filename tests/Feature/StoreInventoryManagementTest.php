<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

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
                'max_batches' => 3, // Set limit to 3 for testing batch quota
                'max_suppliers' => 10,
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
            'name' => 'Paracetamol 500mg',
            'generic_name' => 'Paracetamol',
            'reorder_level' => 10,
            'gst_rate' => 12.00,
            'status' => 'active',
        ]);
    }

    public function test_store_owner_can_view_inventory_index(): void
    {
        Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'BATCH-001',
            'expiry_date' => Carbon::today()->addYear(),
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'mrp' => 15.00,
            'quantity' => 100,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.inventory.index'));

        $response->assertOk();
        $response->assertSee('BATCH-001');
        $response->assertSee('Paracetamol 500mg');
        $response->assertSee('100');
    }

    public function test_store_owner_can_create_batch_with_opening_stock(): void
    {
        $payload = [
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-2026-INIT',
            'manufacturing_date' => Carbon::today()->subMonths(2)->toDateString(),
            'expiry_date' => Carbon::today()->addYear()->toDateString(),
            'purchase_price' => '25.00',
            'mrp' => '40.00',
            'selling_price' => '35.00',
            'opening_stock' => 50,
            'status' => 'active',
            'notes' => 'Initial batch from old pharmacy',
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-2026-INIT',
            'quantity' => 50,
            'purchase_price' => 25.00,
        ]);

        // Verify opening stock movement ledger
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'type' => MovementType::OPENING_STOCK->value,
            'quantity' => 50,
            'before_quantity' => 0,
            'after_quantity' => 50,
        ]);
    }

    public function test_batch_limit_quota_enforced(): void
    {
        // Limit is set to 3 in setUp
        for ($i = 1; $i <= 3; $i++) {
            Batch::create([
                'store_id' => $this->storeA->id,
                'medicine_id' => $this->medicineA->id,
                'batch_number' => "BATCH-00{$i}",
                'expiry_date' => Carbon::today()->addMonths(6),
                'purchase_price' => 10.00,
                'selling_price' => 15.00,
                'mrp' => 15.00,
                'quantity' => 20,
                'status' => 'active',
            ]);
        }

        // Attempting to create 4th batch should be blocked by quota
        $payload = [
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'BATCH-OVERFLOW',
            'expiry_date' => Carbon::today()->addMonths(6)->toDateString(),
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'mrp' => 15.00,
            'opening_stock' => 5,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.store'), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('batches', [
            'batch_number' => 'BATCH-OVERFLOW',
        ]);
    }

    public function test_store_owner_can_update_batch_metadata_without_altering_quantity(): void
    {
        $batch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-EDIT-01',
            'expiry_date' => Carbon::today()->addMonths(6),
            'purchase_price' => 12.00,
            'selling_price' => 18.00,
            'mrp' => 20.00,
            'quantity' => 45,
            'status' => 'active',
        ]);

        $updatePayload = [
            'batch_number' => 'B-EDIT-01-REVISED',
            'expiry_date' => Carbon::today()->addMonths(9)->toDateString(),
            'purchase_price' => 14.00,
            'selling_price' => 19.50,
            'mrp' => 22.00,
            'status' => 'active',
            'notes' => 'Price updated by supplier revision',
            // Attempting to send quantity should be ignored by FormRequest
            'quantity' => 999,
        ];

        $response = $this->actingAs($this->ownerA)->put(route('store.inventory.update', $batch), $updatePayload);

        $response->assertRedirect(route('store.inventory.show', $batch));

        $freshBatch = $batch->fresh();
        $this->assertEquals('B-EDIT-01-REVISED', $freshBatch->batch_number);
        $this->assertEquals(14.00, $freshBatch->purchase_price);
        $this->assertEquals(19.50, $freshBatch->selling_price);
        // Quantity MUST remain untouched
        $this->assertEquals(45, $freshBatch->quantity);
    }

    public function test_store_owner_can_adjust_stock_in(): void
    {
        $batch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-ADJ-IN',
            'expiry_date' => Carbon::today()->addMonths(6),
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'mrp' => 15.00,
            'quantity' => 20,
            'status' => 'active',
        ]);

        $payload = [
            'type' => MovementType::ADJUSTMENT_IN->value,
            'quantity' => 15,
            'reason' => 'Inventory audit surplus found in backroom',
            'notes' => 'Counted 15 extra units during monthly audit',
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.adjust', $batch), $payload);

        $response->assertRedirect(route('store.inventory.show', $batch));

        $this->assertEquals(35, $batch->fresh()->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $batch->id,
            'type' => MovementType::ADJUSTMENT_IN->value,
            'quantity' => 15,
            'before_quantity' => 20,
            'after_quantity' => 35,
            'reason' => 'Inventory audit surplus found in backroom',
        ]);
    }

    public function test_store_owner_can_adjust_stock_out(): void
    {
        $batch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-ADJ-OUT',
            'expiry_date' => Carbon::today()->addMonths(6),
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'mrp' => 15.00,
            'quantity' => 50,
            'status' => 'active',
        ]);

        $payload = [
            'type' => MovementType::ADJUSTMENT_OUT->value,
            'quantity' => 10,
            'reason' => 'Damaged during water leak',
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.adjust', $batch), $payload);

        $response->assertRedirect(route('store.inventory.show', $batch));

        $this->assertEquals(40, $batch->fresh()->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $batch->id,
            'type' => MovementType::ADJUSTMENT_OUT->value,
            'quantity' => 10,
            'before_quantity' => 50,
            'after_quantity' => 40,
            'reason' => 'Damaged during water leak',
        ]);
    }

    public function test_stock_adjustment_out_fails_if_quantity_exceeds_available_balance(): void
    {
        $batch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'B-ADJ-EXCEED',
            'expiry_date' => Carbon::today()->addMonths(6),
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'mrp' => 15.00,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $payload = [
            'type' => MovementType::ADJUSTMENT_OUT->value,
            'quantity' => 10, // Exceeds available stock of 5
            'reason' => 'Attempting excessive reduction',
        ];

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.adjust', $batch), $payload);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(5, $batch->fresh()->quantity);
    }

    public function test_multi_tenant_isolation_for_inventory(): void
    {
        $batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'BATCH-STORE-A',
            'expiry_date' => Carbon::today()->addMonths(6),
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'mrp' => 15.00,
            'quantity' => 50,
            'status' => 'active',
        ]);

        // Owner B attempts to view Batch A
        $response = $this->actingAs($this->ownerB)->get(route('store.inventory.show', $batchA));
        $response->assertNotFound();

        // Owner B attempts to adjust Batch A
        $adjustResponse = $this->actingAs($this->ownerB)->post(route('store.inventory.adjust', $batchA), [
            'type' => MovementType::ADJUSTMENT_IN->value,
            'quantity' => 5,
            'reason' => 'Unauthorized adjustment attempt',
        ]);
        $adjustResponse->assertNotFound();

        // Owner B view index does not see Batch A
        $indexResponse = $this->actingAs($this->ownerB)->get(route('store.inventory.index'));
        $indexResponse->assertDontSee('BATCH-STORE-A');
    }

    public function test_inventory_route_forbidden_without_feature(): void
    {
        // Plan without inventory_management
        $restrictedPlan = SubscriptionPlan::create([
            'name' => 'Basic Tier Without Inventory',
            'slug' => 'basic-no-inventory',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'status' => PlanStatus::ACTIVE,
            'features' => ['medicine_management'], // No inventory_management
        ]);

        Subscription::where('store_id', $this->storeA->id)->update([
            'subscription_plan_id' => $restrictedPlan->id,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.inventory.index'));

        $response->assertForbidden();
    }
}
