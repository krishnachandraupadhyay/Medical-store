<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\SaleStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSalesPosTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

    protected Batch $batchA;

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
            'limits' => [
                'max_medicines' => 100,
                'max_batches' => 200,
                'max_customers' => 50,
                'max_invoices' => 100,
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'customer_management',
                'pos',
                'sales_management',
                'reports',
            ],
        ]);

        // Store A
        $this->storeA = Store::create([
            'code' => 'CCP-001',
            'name' => 'City Care Pharmacy',
            'email' => 'care@citycare.test',
            'mobile' => '9876543210',
            'address' => '100 Medical Square',
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
            'limits' => $this->plan->limits,
        ]);

        // Store B
        $this->storeB = Store::create([
            'code' => 'MHC-002',
            'name' => 'Metro Health Chemist',
            'email' => 'metro@health.test',
            'mobile' => '9123456780',
            'address' => '200 Downtown Road',
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
            'limits' => $this->plan->limits,
        ]);

        // Initial inventory for Store A
        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Paracetamol 500mg',
            'generic_name' => 'Acetaminophen',
            'strength' => '500mg',
            'reorder_level' => 10,
            'status' => 'active',
        ]);

        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'BATCH-P500-01',
            'expiry_date' => Carbon::now()->addMonths(12),
            'purchase_price' => 10.00,
            'selling_price' => 25.00,
            'mrp' => 30.00,
            'quantity' => 100,
            'status' => 'active',
        ]);
    }

    public function test_customer_can_be_created_and_store_isolated(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.customers.store'), [
            'name' => 'John Doe',
            'phone' => '9811122233',
            'email' => 'john@doe.test',
            'city' => 'Mumbai',
        ]);

        $response->assertRedirect(route('store.customers.index'));
        $this->assertDatabaseHas('customers', [
            'store_id' => $this->storeA->id,
            'name' => 'John Doe',
        ]);

        $customerA = Customer::where('store_id', $this->storeA->id)->first();

        // Store B owner cannot access Store A's customer
        $crossResponse = $this->actingAs($this->ownerB)->get(route('store.customers.show', $customerA->id));
        $crossResponse->assertStatus(404);
    }

    public function test_pos_search_medicines_returns_only_current_store_batches(): void
    {
        // Medicine for Store B
        $medB = Medicine::create([
            'store_id' => $this->storeB->id,
            'name' => 'Paracetamol 650mg',
            'status' => 'active',
        ]);
        Batch::create([
            'store_id' => $this->storeB->id,
            'medicine_id' => $medB->id,
            'batch_number' => 'BATCH-BETA-01',
            'expiry_date' => Carbon::now()->addMonths(6),
            'purchase_price' => 15.00,
            'selling_price' => 35.00,
            'quantity' => 50,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.pos.search-medicines', ['q' => 'Paracetamol']));
        $response->assertOk();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals($this->medicineA->id, $data[0]['id']);
        $this->assertEquals('BATCH-P500-01', $data[0]['batches'][0]['batch_number']);
    }

    public function test_completed_sale_deducts_stock_and_creates_movement(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.pos.checkout'), [
            'customer_name' => 'Jane Smith',
            'customer_phone' => '9800011122',
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 20,
                    'unit_price' => 25.00,
                    'discount' => 0.00,
                    'gst_rate' => 0.00,
                ],
            ],
            'paid_amount' => 500.00,
            'payment_method' => 'cash',
        ]);

        $sale = Sale::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($sale);
        $response->assertRedirect(route('store.sales.show', $sale->id));

        $this->assertEquals(SaleStatus::COMPLETED, $sale->status);
        $this->assertEquals(500.00, $sale->grand_total);
        $this->assertEquals(500.00, $sale->paid_amount);

        // Verify stock deducted
        $this->batchA->refresh();
        $this->assertEquals(80, $this->batchA->quantity);

        // Verify StockMovement recorded
        $movement = StockMovement::where('store_id', $this->storeA->id)
            ->where('type', MovementType::SALE_OUT)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(20, $movement->quantity);
        $this->assertEquals(100, $movement->before_quantity);
        $this->assertEquals(80, $movement->after_quantity);
    }

    public function test_sale_fails_if_insufficient_stock(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.pos.checkout'), [
            'customer_name' => 'Jane Smith',
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 150, // Available is only 100
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->batchA->refresh();
        $this->assertEquals(100, $this->batchA->quantity); // Stock untouched
    }

    public function test_draft_sale_does_not_deduct_stock_until_completed(): void
    {
        $this->actingAs($this->ownerA)->post(route('store.pos.checkout'), [
            'customer_name' => 'Draft Customer',
            'sale_date' => now()->toDateString(),
            'status' => 'draft',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 10,
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        $sale = Sale::where('store_id', $this->storeA->id)->first();
        $this->assertEquals(SaleStatus::DRAFT, $sale->status);

        // Stock must still be 100
        $this->batchA->refresh();
        $this->assertEquals(100, $this->batchA->quantity);

        // Now complete the draft
        $this->actingAs($this->ownerA)->post(route('store.sales.complete', $sale->id), [
            'paid_amount' => 250.00,
            'payment_method' => 'cash',
        ]);

        $sale->refresh();
        $this->assertEquals(SaleStatus::COMPLETED, $sale->status);
        $this->batchA->refresh();
        $this->assertEquals(90, $this->batchA->quantity);
    }

    public function test_cross_store_sale_access_is_forbidden(): void
    {
        $sale = Sale::create([
            'store_id' => $this->storeA->id,
            'invoice_number' => 'INV-2026-000001',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 100.00,
            'grand_total' => 100.00,
            'paid_amount' => 100.00,
        ]);

        $response = $this->actingAs($this->ownerB)->get(route('store.sales.show', $sale->id));
        $response->assertStatus(404);
    }
}
