<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\StockCountStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\StockCount;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreStockReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

    protected Batch $batchA1;

    protected Batch $batchA2;

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
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'advanced_inventory_control',
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
            'slug' => 'amoxicillin-500mg',
            'barcode' => 'MED-AMX-001',
            'is_active' => true,
        ]);

        $this->batchA1 = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AMX-B1',
            'expiry_date' => Carbon::today()->addMonths(12),
            'quantity' => 100,
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'status' => 'active',
        ]);

        $this->batchA2 = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AMX-B2',
            'expiry_date' => Carbon::today()->addMonths(8),
            'quantity' => 50,
            'purchase_price' => 20.00,
            'selling_price' => 30.00,
            'status' => 'active',
        ]);
    }

    public function test_can_view_stock_count_index_and_create_draft(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('store.inventory.stock-counts.index'));
        $response->assertStatus(200);
        $response->assertSee('Stock Count & Reconciliation');

        $createResponse = $this->actingAs($this->ownerA)->get(route('store.inventory.stock-counts.create'));
        $createResponse->assertStatus(200);

        $storeResponse = $this->actingAs($this->ownerA)->post(route('store.inventory.stock-counts.store'), [
            'count_date' => Carbon::today()->toDateString(),
            'scope' => 'full',
            'notes' => 'Quarterly physical audit',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('stock_counts', [
            'store_id' => $this->storeA->id,
            'status' => StockCountStatus::DRAFT->value,
            'notes' => 'Quarterly physical audit',
        ]);
    }

    public function test_can_add_item_to_draft_stock_count(): void
    {
        $stockCount = StockCount::create([
            'store_id' => $this->storeA->id,
            'count_number' => 'SC-202609-0001',
            'count_date' => Carbon::today()->toDateString(),
            'status' => StockCountStatus::DRAFT,
            'scope' => 'full',
            'created_by' => $this->ownerA->id,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.stock-counts.items.add', $stockCount), [
            'batch_id' => $this->batchA1->id,
            'physical_quantity' => 95, // 5 shortage
            'notes' => 'Counted shelf row 1',
        ]);

        $response->assertRedirect(route('store.inventory.stock-counts.show', $stockCount));

        $this->assertDatabaseHas('stock_count_items', [
            'stock_count_id' => $stockCount->id,
            'batch_id' => $this->batchA1->id,
            'system_quantity' => 100,
            'physical_quantity' => 95,
            'variance_quantity' => -5,
            'variance_cost' => -50.00,
        ]);

        $stockCount->refresh();
        $this->assertEquals(1, $stockCount->total_items);
        $this->assertEquals(-5, $stockCount->total_variance_units);
        $this->assertEquals(-50.00, (float) $stockCount->total_variance_cost);
    }

    public function test_complete_stock_count_atomically_reconciles_shortages_and_surpluses(): void
    {
        $stockCount = StockCount::create([
            'store_id' => $this->storeA->id,
            'count_number' => 'SC-202609-0002',
            'count_date' => Carbon::today()->toDateString(),
            'status' => StockCountStatus::DRAFT,
            'scope' => 'full',
            'created_by' => $this->ownerA->id,
        ]);

        // Add item 1: shortage of 10 (was 100, physical 90)
        $this->actingAs($this->ownerA)->post(route('store.inventory.stock-counts.items.add', $stockCount), [
            'batch_id' => $this->batchA1->id,
            'physical_quantity' => 90,
        ]);

        // Add item 2: surplus of 5 (was 50, physical 55)
        $this->actingAs($this->ownerA)->post(route('store.inventory.stock-counts.items.add', $stockCount), [
            'batch_id' => $this->batchA2->id,
            'physical_quantity' => 55,
        ]);

        // Complete reconciliation
        $completeResponse = $this->actingAs($this->ownerA)->post(route('store.inventory.stock-counts.complete', $stockCount));
        $completeResponse->assertRedirect(route('store.inventory.stock-counts.show', $stockCount));

        $stockCount->refresh();
        $this->assertEquals(StockCountStatus::COMPLETED, $stockCount->status);
        $this->assertNotNull($stockCount->completed_at);

        // Verify batch quantities updated
        $this->batchA1->refresh();
        $this->assertEquals(90, $this->batchA1->quantity);

        $this->batchA2->refresh();
        $this->assertEquals(55, $this->batchA2->quantity);

        // Verify StockMovements created
        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $this->batchA1->id,
            'type' => MovementType::STOCK_RECONCILIATION_OUT->value,
            'quantity' => 10,
            'before_quantity' => 100,
            'after_quantity' => 90,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $this->batchA2->id,
            'type' => MovementType::STOCK_RECONCILIATION_IN->value,
            'quantity' => 5,
            'before_quantity' => 50,
            'after_quantity' => 55,
        ]);

        // Verify notification dispatched
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->storeA->id,
            'title' => 'Stock Reconciliation Completed',
        ]);
    }

    public function test_can_cancel_draft_stock_count(): void
    {
        $stockCount = StockCount::create([
            'store_id' => $this->storeA->id,
            'count_number' => 'SC-202609-0003',
            'count_date' => Carbon::today()->toDateString(),
            'status' => StockCountStatus::DRAFT,
            'created_by' => $this->ownerA->id,
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.stock-counts.cancel', $stockCount), [
            'reason' => 'Inventory interrupted by audit team',
        ]);

        $response->assertRedirect(route('store.inventory.stock-counts.show', $stockCount));

        $stockCount->refresh();
        $this->assertEquals(StockCountStatus::CANCELLED, $stockCount->status);
        $this->assertStringContainsString('Inventory interrupted', $stockCount->notes);
    }

    public function test_tenant_isolation_store_b_cannot_access_or_reconcile_store_a_counts(): void
    {
        $stockCountA = StockCount::create([
            'store_id' => $this->storeA->id,
            'count_number' => 'SC-202609-0004',
            'count_date' => Carbon::today()->toDateString(),
            'status' => StockCountStatus::DRAFT,
            'created_by' => $this->ownerA->id,
        ]);

        // Owner B attempts to view Store A's count -> 403
        $response = $this->actingAs($this->ownerB)->get(route('store.inventory.stock-counts.show', $stockCountA));
        $response->assertStatus(403);

        // Owner B attempts to complete Store A's count -> 403
        $completeResponse = $this->actingAs($this->ownerB)->post(route('store.inventory.stock-counts.complete', $stockCountA));
        $completeResponse->assertStatus(403);
    }
}
