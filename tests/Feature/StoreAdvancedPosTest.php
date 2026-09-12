<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\SaleStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Store;
use App\Models\StorePayment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAdvancedPosTest extends TestCase
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
            'name' => 'Enterprise POS Tier',
            'slug' => 'enterprise-pos-tier',
            'price' => 2999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_medicines' => 200,
                'max_batches' => 500,
                'max_customers' => 100,
                'max_invoices' => 500,
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'customer_management',
                'pos',
                'advanced_pos',
                'sales_management',
                'reports',
            ],
        ]);

        // Store A
        $this->storeA = Store::create([
            'code' => 'APOS-001',
            'name' => 'Metro Medico Care',
            'email' => 'metro@care.test',
            'mobile' => '9876543210',
            'address' => '100 Ring Road',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'tax_number' => '07AAAAA0000A1Z5',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::create([
            'name' => 'Metro Pharmacist',
            'email' => 'pharmacist@metro.test',
            'password' => bcrypt('Password123!'),
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeA->id,
            'is_active' => true,
        ]);

        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'features' => $this->plan->features,
            'limits' => $this->plan->limits,
        ]);

        // Store B (Tenant Isolation)
        $this->storeB = Store::create([
            'code' => 'APOS-002',
            'name' => 'Alpha Medicos',
            'email' => 'alpha@care.test',
            'mobile' => '9876543211',
            'address' => '200 Mall Road',
            'city' => 'Noida',
            'state' => 'Uttar Pradesh',
            'pincode' => '201301',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::create([
            'name' => 'Alpha Owner',
            'email' => 'owner@alpha.test',
            'password' => bcrypt('Password123!'),
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeB->id,
            'is_active' => true,
        ]);

        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'features' => $this->plan->features,
            'limits' => $this->plan->limits,
        ]);

        // Medicine and Batch in Store A
        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Augmentin 625 Duo',
            'generic_name' => 'Amoxicillin + Clavulanic Acid',
            'brand_name' => 'GSK',
            'hsn_code' => '30049099',
            'gst_rate' => 12.00,
            'status' => 'active',
        ]);

        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AUG-991',
            'barcode' => '8901234567890',
            'secondary_barcode' => 'SKU-AUG-625',
            'expiry_date' => Carbon::now()->addMonths(12)->toDateString(),
            'purchase_price' => 120.00,
            'mrp' => 200.00,
            'selling_price' => 180.00,
            'quantity' => 50,
            'reserved_quantity' => 0,
            'status' => 'active',
        ]);
    }

    public function test_barcode_search_finds_active_batch_by_primary_and_secondary_barcode(): void
    {
        $this->actingAs($this->ownerA);

        // Test primary barcode
        $res1 = $this->getJson(route('store.pos.barcode', ['code' => '8901234567890']));
        $res1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('medicine.name', 'Augmentin 625 Duo')
            ->assertJsonPath('medicine.hsn_code', '30049099')
            ->assertJsonPath('medicine.gst_rate', 12)
            ->assertJsonPath('batch.batch_number', 'AUG-991')
            ->assertJsonPath('batch.quantity', 50)
            ->assertJsonPath('batch.selling_price', 180);

        // Test secondary barcode
        $res2 = $this->getJson(route('store.pos.barcode', ['code' => 'SKU-AUG-625']));
        $res2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('batch.batch_number', 'AUG-991');
    }

    public function test_barcode_search_rejects_expired_or_empty_batches(): void
    {
        $this->actingAs($this->ownerA);

        // Expired batch
        $expiredBatch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'EXP-001',
            'barcode' => '999000111222',
            'expiry_date' => Carbon::yesterday()->toDateString(),
            'purchase_price' => 50,
            'selling_price' => 100,
            'quantity' => 10,
            'status' => 'active',
        ]);

        $resExpired = $this->getJson(route('store.pos.barcode', ['code' => '999000111222']));
        $resExpired->assertStatus(422)
            ->assertJsonPath('success', false);

        // Out of stock batch
        $oosBatch = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'OOS-001',
            'barcode' => '999000111333',
            'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
            'purchase_price' => 50,
            'selling_price' => 100,
            'quantity' => 0,
            'status' => 'active',
        ]);

        $resOos = $this->getJson(route('store.pos.barcode', ['code' => '999000111333']));
        $resOos->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_barcode_search_prevents_tenant_isolation_leak(): void
    {
        $this->actingAs($this->ownerB);

        // Store B user searches Store A's barcode
        $res = $this->getJson(route('store.pos.barcode', ['code' => '8901234567890']));
        $res->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_hold_bill_saves_named_draft_without_deducting_stock(): void
    {
        $this->actingAs($this->ownerA);

        $initialStock = $this->batchA->quantity; // 50

        $holdData = [
            'customer_name' => 'Walk-in John',
            'sale_date' => now()->toDateString(),
            'status' => 'draft',
            'hold_reference' => 'HOLD-COUNTER-1',
            'discount' => 10.00,
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 3,
                    'unit_price' => 180.00,
                    'discount' => 5.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ];

        $res = $this->postJson(route('store.pos.hold'), $holdData);
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sale.hold_reference', 'HOLD-COUNTER-1');

        // Verify sale saved as DRAFT in database
        $sale = Sale::where('hold_reference', 'HOLD-COUNTER-1')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(SaleStatus::DRAFT, $sale->status);
        $this->assertTrue($sale->isHeld());

        // CRITICAL: Verify stock was NOT deducted during hold
        $this->batchA->refresh();
        $this->assertEquals($initialStock, $this->batchA->quantity);
    }

    public function test_held_bills_list_and_resume_endpoint(): void
    {
        $this->actingAs($this->ownerA);

        // Hold a bill
        $this->postJson(route('store.pos.hold'), [
            'customer_name' => 'Patient Sara',
            'sale_date' => now()->toDateString(),
            'status' => 'draft',
            'hold_reference' => 'HOLD-SARA',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 2,
                    'unit_price' => 180.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ])->assertOk();

        // Check held bills list
        $listRes = $this->getJson(route('store.pos.held-bills'));
        $listRes->assertOk();
        $this->assertCount(1, $listRes->json());
        $this->assertEquals('HOLD-SARA', $listRes->json()[0]['hold_reference']);

        $saleId = $listRes->json()[0]['id'];

        // Resume the bill
        $resumeRes = $this->getJson(route('store.pos.resume', $saleId));
        $resumeRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sale.customer_name', 'Patient Sara')
            ->assertJsonPath('sale.items.0.quantity', 2);
    }

    public function test_discard_held_bill_cancels_draft(): void
    {
        $this->actingAs($this->ownerA);

        $holdRes = $this->postJson(route('store.pos.hold'), [
            'customer_name' => 'To Be Discarded',
            'sale_date' => now()->toDateString(),
            'status' => 'draft',
            'hold_reference' => 'HOLD-DISCARD-ME',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 1,
                    'unit_price' => 180.00,
                ],
            ],
        ]);
        $saleId = $holdRes->json('sale.id');
        $this->assertNotNull($saleId);

        $discardRes = $this->deleteJson(route('store.pos.discard-held', ['sale' => $saleId]));
        $discardRes->assertOk()
            ->assertJsonPath('success', true);

        $sale = Sale::find($saleId);
        $this->assertEquals(SaleStatus::CANCELLED, $sale->status);
    }

    public function test_checkout_with_split_payments_creates_multiple_store_payments(): void
    {
        $this->actingAs($this->ownerA);

        $initialStock = $this->batchA->quantity; // 50

        // Grand total: 2 * 180 = 360. Tax (12%): 43.20. Total: 403.20.
        // Split: Cash 200 + UPI 203.20 = 403.20
        $checkoutData = [
            'customer_name' => 'Multi Pay Customer',
            'customer_phone' => '9988776655',
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'discount' => 0.00,
            'paid_amount' => 403.20,
            'payment_method' => 'cash',
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 200.00,
                    'reference' => 'CASH-COUNTER',
                ],
                [
                    'method' => 'upi',
                    'amount' => 203.20,
                    'reference' => 'UPI-REF-987654321',
                ],
            ],
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 2,
                    'unit_price' => 180.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ];

        $res = $this->post(route('store.pos.checkout'), $checkoutData);
        $res->assertRedirect();

        $sale = Sale::where('customer_name', 'Multi Pay Customer')->latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals(SaleStatus::COMPLETED, $sale->status);

        // Stock deduction verified
        $this->batchA->refresh();
        $this->assertEquals($initialStock - 2, $this->batchA->quantity);

        // Verify split payments created
        $payments = StorePayment::where('sale_id', $sale->id)->get();
        $this->assertCount(2, $payments);
        $this->assertEquals(200.00, (float) $payments->firstWhere('payment_method', 'cash')->amount);
        $this->assertEquals(203.20, (float) $payments->firstWhere('payment_method', 'upi')->amount);
    }

    public function test_invoice_view_renders_successfully(): void
    {
        $this->actingAs($this->ownerA);

        // Complete a sale
        $this->post(route('store.pos.checkout'), [
            'customer_name' => 'Invoice Test Customer',
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'paid_amount' => 180.00,
            'payment_method' => 'cash',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 1,
                    'unit_price' => 180.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ]);

        $sale = Sale::where('customer_name', 'Invoice Test Customer')->first();

        // Standard Invoice
        $invRes = $this->get(route('store.sales.invoice', $sale->id));
        $invRes->assertOk()
            ->assertSee('Metro Medico Care')
            ->assertSee($sale->invoice_number)
            ->assertSee('30049099') // HSN
            ->assertSee('AUG-991');  // Batch
    }
}
