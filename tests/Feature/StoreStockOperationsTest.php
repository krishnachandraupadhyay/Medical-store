<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreStockOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

    protected Batch $batchActive;

    protected Batch $batchExpired;

    protected Customer $customerA;

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
                'max_invoices' => -1,
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'advanced_inventory_control',
                'sales_management',
                'pos',
                'customer_management',
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
            'name' => 'Paracetamol 650mg',
            'slug' => 'paracetamol-650mg',
            'barcode' => 'MED-PCM-001',
            'is_active' => true,
        ]);

        $this->batchActive = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'PCM-ACT-01',
            'expiry_date' => Carbon::today()->addMonths(12),
            'quantity' => 100,
            'purchase_price' => 5.00,
            'selling_price' => 10.00,
            'status' => 'active',
        ]);

        $this->batchExpired = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'PCM-EXP-01',
            'expiry_date' => Carbon::today()->subMonths(2),
            'quantity' => 40,
            'purchase_price' => 5.00,
            'selling_price' => 10.00,
            'status' => 'active',
        ]);

        $this->customerA = Customer::create([
            'store_id' => $this->storeA->id,
            'customer_code' => Customer::generateCustomerCode($this->storeA->id),
            'name' => 'John Doe',
            'phone' => '9876543210',
            'status' => 'active',
        ]);
    }

    public function test_can_record_damaged_stock_write_off(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.damaged.store'), [
            'batch_id' => $this->batchActive->id,
            'quantity' => 5,
            'reason' => 'Broken glass strip',
            'notes' => 'Dropped during shelf rearrangement',
        ]);

        $response->assertRedirect(route('store.inventory.damaged.index'));

        $this->batchActive->refresh();
        $this->assertEquals(95, $this->batchActive->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $this->batchActive->id,
            'type' => MovementType::DAMAGED_STOCK_OUT->value,
            'quantity' => 5,
            'before_quantity' => 100,
            'after_quantity' => 95,
            'reason' => 'Broken glass strip',
        ]);
    }

    public function test_cannot_write_off_more_damaged_stock_than_available(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.damaged.store'), [
            'batch_id' => $this->batchActive->id,
            'quantity' => 150, // exceeds 100
            'reason' => 'Water damage',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(100, $this->batchActive->fresh()->quantity);
    }

    public function test_can_record_lost_stock_write_off(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.inventory.lost.store'), [
            'batch_id' => $this->batchActive->id,
            'quantity' => 3,
            'reason' => 'Missing from shelf stock',
            'notes' => 'Investigated during spot check',
        ]);

        $response->assertRedirect(route('store.inventory.lost.index'));

        $this->batchActive->refresh();
        $this->assertEquals(97, $this->batchActive->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $this->batchActive->id,
            'type' => MovementType::LOST_STOCK_OUT->value,
            'quantity' => 3,
            'before_quantity' => 100,
            'after_quantity' => 97,
        ]);
    }

    public function test_expired_stock_disposal_only_allowed_for_genuinely_expired_batches(): void
    {
        // Attempting to dispose active non-expired batch should fail validation
        $failResponse = $this->actingAs($this->ownerA)->post(route('store.inventory.expired.process'), [
            'batch_id' => $this->batchActive->id,
            'quantity' => 10,
            'reason' => 'Disposal attempt',
        ]);
        $failResponse->assertSessionHasErrors('expiry');
        $this->assertEquals(100, $this->batchActive->fresh()->quantity);

        // Disposing genuinely expired batch should succeed
        $successResponse = $this->actingAs($this->ownerA)->post(route('store.inventory.expired.process'), [
            'batch_id' => $this->batchExpired->id,
            'quantity' => 40,
            'reason' => 'Incineration disposal by biomedical agency',
            'notes' => 'Certificate #BMW-2026-99',
        ]);

        $successResponse->assertRedirect(route('store.inventory.expired.index'));

        $this->batchExpired->refresh();
        $this->assertEquals(0, $this->batchExpired->quantity);
        $this->assertEquals('expired', $this->batchExpired->status);

        $this->assertDatabaseHas('stock_movements', [
            'store_id' => $this->storeA->id,
            'batch_id' => $this->batchExpired->id,
            'type' => MovementType::EXPIRED_STOCK_OUT->value,
            'quantity' => 40,
            'before_quantity' => 40,
            'after_quantity' => 0,
        ]);
    }

    public function test_quarantined_or_blocked_batch_cannot_be_sold_or_checked_out(): void
    {
        // 1. Block the active batch
        $blockResponse = $this->actingAs($this->ownerA)->post(route('store.inventory.batches.status', $this->batchActive), [
            'status' => BatchStatus::BLOCKED->value,
            'reason' => 'Quality dispute under investigation',
        ]);
        $blockResponse->assertRedirect();

        $this->batchActive->refresh();
        $this->assertEquals(BatchStatus::BLOCKED->value, $this->batchActive->status);
        $this->assertFalse($this->batchActive->canBeSold());

        // 2. POS medicine search should NOT return the blocked batch
        $searchResponse = $this->actingAs($this->ownerA)->getJson(route('store.pos.search-medicines', ['q' => 'Paracetamol']));
        $searchResponse->assertStatus(200);
        $data = $searchResponse->json();
        $batchesFound = $data[0]['batches'] ?? [];
        $this->assertEmpty($batchesFound, 'Blocked batch must not appear in POS search');

        // 3. Attempting checkout sale with blocked batch throws error
        $saleResponse = $this->actingAs($this->ownerA)->post(route('store.pos.checkout'), [
            'customer_id' => $this->customerA->id,
            'sale_date' => Carbon::today()->toDateString(),
            'status' => 'completed',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchActive->id,
                    'quantity' => 5,
                    'unit_price' => 10.00,
                ],
            ],
            'paid_amount' => 50.00,
            'payment_method' => 'cash',
        ]);

        $saleResponse->assertSessionHasErrors('items');
        $this->assertEquals(100, $this->batchActive->fresh()->quantity, 'Stock must not be deducted for blocked batch sale attempt');
    }

    public function test_tenant_isolation_store_b_cannot_write_off_or_block_store_a_batch(): void
    {
        // Store B tries to write off Store A's batch
        $response = $this->actingAs($this->ownerB)->post(route('store.inventory.damaged.store'), [
            'batch_id' => $this->batchActive->id,
            'quantity' => 5,
            'reason' => 'Malicious write-off',
        ]);
        $response->assertStatus(404);

        // Store B tries to change status of Store A's batch
        $statusResponse = $this->actingAs($this->ownerB)->post(route('store.inventory.batches.status', $this->batchActive), [
            'status' => 'blocked',
            'reason' => 'Unauthorized block',
        ]);
        $statusResponse->assertStatus(403);
    }
}
