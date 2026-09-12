<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSalesReturnTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

    protected Batch $batchA;

    protected Sale $saleA;

    protected SaleItem $saleItemA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = SubscriptionPlan::create([
            'name' => 'All Inclusive Tier',
            'slug' => 'all-inclusive-tier',
            'price' => 1999.00,
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

        $this->storeA = Store::create([
            'code' => 'CCP-001',
            'name' => 'City Care Pharmacy',
            'email' => 'care@citycare.test',
            'mobile' => '9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'alpha@citycare.test',
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
            'code' => 'MHC-002',
            'name' => 'Metro Health Chemist',
            'email' => 'metro@health.test',
            'mobile' => '9123456780',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'beta@metro.test',
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

        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Amoxicillin 500mg',
            'status' => 'active',
        ]);

        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AMX-001',
            'expiry_date' => Carbon::now()->addMonths(10),
            'purchase_price' => 50.00,
            'selling_price' => 80.00,
            'mrp' => 90.00,
            'quantity' => 70, // after selling 30
            'status' => 'active',
        ]);

        $this->saleA = Sale::create([
            'store_id' => $this->storeA->id,
            'invoice_number' => 'INV-2026-000010',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 2400.00,
            'grand_total' => 2400.00,
            'paid_amount' => 2400.00,
            'completed_at' => now(),
        ]);

        $this->saleItemA = SaleItem::create([
            'sale_id' => $this->saleA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 30,
            'unit_price' => 80.00,
            'mrp' => 90.00,
            'line_total' => 2400.00,
        ]);
    }

    public function test_sales_return_restores_stock_and_creates_ledger_entry(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Doctor adjusted dose',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 10,
                    'reason' => 'Unopened blister',
                ],
            ],
        ]);

        $salesReturn = SalesReturn::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($salesReturn);
        $response->assertRedirect(route('store.sales-returns.show', $salesReturn->id));

        $this->assertEquals(ReturnStatus::COMPLETED, $salesReturn->status);
        $this->assertEquals(800.00, $salesReturn->grand_total);

        // Stock restored from 70 to 80
        $this->batchA->refresh();
        $this->assertEquals(80, $this->batchA->quantity);

        // Movement created
        $movement = StockMovement::where('store_id', $this->storeA->id)
            ->where('type', MovementType::SALES_RETURN_IN)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->quantity);
        $this->assertEquals(70, $movement->before_quantity);
        $this->assertEquals(80, $movement->after_quantity);
    }

    public function test_cannot_return_more_than_sold_quantity(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Over return test',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 35, // Sold only 30
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->batchA->refresh();
        $this->assertEquals(70, $this->batchA->quantity); // untouched
    }

    public function test_cross_store_sales_return_is_prevented(): void
    {
        $response = $this->actingAs($this->ownerB)->post(route('store.sales-returns.store'), [
            'sale_id' => $this->saleA->id, // Belongs to Store A
            'return_date' => now()->toDateString(),
            'reason' => 'Malicious return',
            'items' => [
                [
                    'sale_item_id' => $this->saleItemA->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(404);
    }
}
