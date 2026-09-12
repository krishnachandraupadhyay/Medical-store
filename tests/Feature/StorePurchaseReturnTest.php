<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PlanStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReturnStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePurchaseReturnTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Medicine $medicineA;

    protected Batch $batchA;

    protected Supplier $supplierA;

    protected Purchase $purchaseA;

    protected PurchaseItem $purchaseItemA;

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
                'supplier_management',
                'purchase_management',
                'purchase_return',
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

        $this->supplierA = Supplier::create([
            'store_id' => $this->storeA->id,
            'name' => 'Apex Pharma Distributors',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Azithromycin 500mg',
            'status' => 'active',
        ]);

        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AZ-999',
            'expiry_date' => Carbon::now()->addMonths(8),
            'purchase_price' => 100.00,
            'selling_price' => 150.00,
            'mrp' => 170.00,
            'quantity' => 50,
            'status' => 'active',
        ]);

        $this->purchaseA = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $this->supplierA->id,
            'invoice_number' => 'PUR-2026-000005',
            'purchase_date' => now()->toDateString(),
            'status' => PurchaseStatus::COMPLETED,
            'subtotal' => 5000.00,
            'grand_total' => 5000.00,
            'paid_amount' => 5000.00,
            'completed_at' => now(),
        ]);

        $this->purchaseItemA = PurchaseItem::create([
            'purchase_id' => $this->purchaseA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_id' => $this->batchA->id,
            'batch_number' => 'AZ-999',
            'expiry_date' => Carbon::now()->addMonths(8),
            'quantity' => 50,
            'purchase_price' => 100.00,
            'selling_price' => 150.00,
            'mrp' => 170.00,
            'line_total' => 5000.00,
        ]);
    }

    public function test_purchase_return_deducts_stock_and_creates_ledger_entry(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('store.purchase-returns.store'), [
            'purchase_id' => $this->purchaseA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged packaging from distributor',
            'items' => [
                [
                    'purchase_item_id' => $this->purchaseItemA->id,
                    'quantity' => 10,
                ],
            ],
        ]);

        $purchaseReturn = PurchaseReturn::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($purchaseReturn);
        $response->assertRedirect(route('store.purchase-returns.show', $purchaseReturn->id));

        $this->assertEquals(ReturnStatus::COMPLETED, $purchaseReturn->status);
        $this->assertEquals(1000.00, $purchaseReturn->grand_total);

        // Stock deducted from 50 to 40
        $this->batchA->refresh();
        $this->assertEquals(40, $this->batchA->quantity);

        // Movement created
        $movement = StockMovement::where('store_id', $this->storeA->id)
            ->where('type', MovementType::PURCHASE_RETURN_OUT)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->quantity);
        $this->assertEquals(50, $movement->before_quantity);
        $this->assertEquals(40, $movement->after_quantity);
    }

    public function test_cannot_return_more_than_available_batch_stock(): void
    {
        // Suppose 45 units were already sold to customers, so only 5 remain in stock
        $this->batchA->update(['quantity' => 5]);

        $response = $this->actingAs($this->ownerA)->post(route('store.purchase-returns.store'), [
            'purchase_id' => $this->purchaseA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Excess return attempt',
            'items' => [
                [
                    'purchase_item_id' => $this->purchaseItemA->id,
                    'quantity' => 20, // Only 5 available!
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->batchA->refresh();
        $this->assertEquals(5, $this->batchA->quantity); // Stock untouched
    }

    public function test_cross_store_purchase_return_is_prevented(): void
    {
        $response = $this->actingAs($this->ownerB)->post(route('store.purchase-returns.store'), [
            'purchase_id' => $this->purchaseA->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Unauthorized cross-store return',
            'items' => [
                [
                    'purchase_item_id' => $this->purchaseItemA->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(404);
    }
}
