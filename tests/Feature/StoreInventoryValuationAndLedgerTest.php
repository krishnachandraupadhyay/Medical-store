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
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreInventoryValuationAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medA;

    protected Medicine $medB;

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
            'limits' => ['max_medicines' => 50],
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

        $this->medA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Azithromycin 500mg',
            'slug' => 'azithromycin-500mg',
            'barcode' => 'MED-AZI-001',
            'is_active' => true,
        ]);

        $this->medB = Medicine::create([
            'store_id' => $this->storeB->id,
            'name' => 'Beta Medicine',
            'slug' => 'beta-medicine',
            'barcode' => 'MED-BET-001',
            'is_active' => true,
        ]);
    }

    public function test_valuation_computes_accurate_financial_metrics(): void
    {
        // Batch 1: 100 units @ buy 10, sell 15 (Exp: in 6 months)
        Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medA->id,
            'batch_number' => 'B-01',
            'expiry_date' => Carbon::today()->addMonths(6),
            'quantity' => 100,
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'status' => 'active',
        ]);

        // Batch 2: 50 units @ buy 20, sell 30 (Exp: expired 1 month ago)
        Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medA->id,
            'batch_number' => 'B-02',
            'expiry_date' => Carbon::today()->subMonth(),
            'quantity' => 50,
            'purchase_price' => 20.00,
            'selling_price' => 30.00,
            'status' => 'active',
        ]);

        // Store B batch: 200 units @ buy 100
        Batch::create([
            'store_id' => $this->storeB->id,
            'medicine_id' => $this->medB->id,
            'batch_number' => 'B-BETA',
            'expiry_date' => Carbon::today()->addMonths(6),
            'quantity' => 200,
            'purchase_price' => 100.00,
            'selling_price' => 150.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.inventory.valuation'));
        $response->assertStatus(200);

        // Expected Store A:
        // Purchase Val: (100 * 10) + (50 * 20) = 1000 + 1000 = 2000.00
        // Selling Val: (100 * 15) + (50 * 30) = 1500 + 1500 = 3000.00
        // Potential profit: 1000.00
        // Expired Val: 50 * 20 = 1000.00
        $response->assertSee('2,000.00');
        $response->assertSee('3,000.00');
        $response->assertSee('1,000.00');

        // Store B data (20,000.00) must NOT be present
        $response->assertDontSee('20,000.00');
    }

    public function test_stock_ledger_history_displays_and_filters_all_movement_types(): void
    {
        $batch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medA->id,
            'batch_number' => 'B-LEDGER',
            'expiry_date' => Carbon::today()->addMonths(6),
            'quantity' => 80,
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'status' => 'active',
        ]);

        StockMovement::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medA->id,
            'batch_id' => $batch->id,
            'type' => MovementType::STOCK_RECONCILIATION_IN,
            'quantity' => 10,
            'before_quantity' => 70,
            'after_quantity' => 80,
            'reason' => 'Audit surplus',
            'created_at' => now(),
        ]);

        StockMovement::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medA->id,
            'batch_id' => $batch->id,
            'type' => MovementType::DAMAGED_STOCK_OUT,
            'quantity' => 2,
            'before_quantity' => 80,
            'after_quantity' => 78,
            'reason' => 'Liquid spill',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.inventory.history', [
            'type' => 'DAMAGED_STOCK_OUT',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Liquid spill');
        $response->assertDontSee('Audit surplus');
    }
}
