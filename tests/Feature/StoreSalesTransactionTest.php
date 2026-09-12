<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\MovementType;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\SaleStatus;
use App\Enums\StorePaymentType;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StorePayment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSalesTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $plan;

    protected Customer $customerA;

    protected Customer $customerB;

    protected Medicine $medicineA;

    protected Batch $batchA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = SubscriptionPlan::create([
            'name' => 'Professional Pharmacy Plan',
            'slug' => 'pro-pharmacy-plan',
            'price' => 2499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_medicines' => 200,
                'max_batches' => 500,
                'max_customers' => 200,
                'max_invoices' => 1000,
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
            'code' => 'RXA-001',
            'name' => 'Apex Healthcare Chemist',
            'email' => 'apex@chemist.test',
            'mobile' => '9811122233',
            'address' => '45 Hospital Road',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400012',
            'tax_number' => '27ABCDE1234F1Z5',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::create([
            'name' => 'Apex Pharmacist',
            'email' => 'pharmacist@apex.test',
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

        // Store B
        $this->storeB = Store::create([
            'code' => 'RXB-002',
            'name' => 'Beacon Chemist',
            'email' => 'beacon@chemist.test',
            'mobile' => '9822233344',
            'address' => '78 Market Street',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411002',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::create([
            'name' => 'Beacon Pharmacist',
            'email' => 'pharmacist@beacon.test',
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

        // Customers
        $this->customerA = Customer::create([
            'store_id' => $this->storeA->id,
            'customer_code' => 'CUS-000001',
            'name' => 'Rajesh Sharma',
            'phone' => '9876500001',
            'email' => 'rajesh@example.test',
            'address' => 'Flat 101, Lake View',
            'city' => 'Mumbai',
            'doctor_name' => 'Verma',
            'status' => 'active',
            'loyalty_tier' => 'regular',
            'visit_count' => 0,
        ]);

        $this->customerB = Customer::create([
            'store_id' => $this->storeB->id,
            'customer_code' => 'CUS-000002',
            'name' => 'Sneha Patil',
            'phone' => '9876500002',
            'status' => 'active',
        ]);

        // Medicine & Batch in Store A
        $this->medicineA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Azithromycin 500mg',
            'generic_name' => 'Azithromycin',
            'brand_name' => 'Azee',
            'hsn_code' => '30042010',
            'gst_rate' => 12.00,
            'status' => 'active',
        ]);

        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medicineA->id,
            'batch_number' => 'AZ-2026-01',
            'barcode' => '8901112223334',
            'expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
            'purchase_price' => 50.00,
            'mrp' => 120.00,
            'selling_price' => 100.00,
            'quantity' => 50,
            'status' => 'active',
        ]);
    }

    public function test_pos_medicine_search_matches_by_name_generic_hsn_and_batch_number(): void
    {
        $this->actingAs($this->ownerA);

        // 1. Search by Medicine name
        $resName = $this->getJson(route('store.pos.search-medicines', ['q' => 'Azithromycin']));
        $resName->assertOk();
        $this->assertCount(1, $resName->json());
        $this->assertEquals('Azithromycin 500mg', $resName->json()[0]['name']);

        // 2. Search by Generic name
        $resGen = $this->getJson(route('store.pos.search-medicines', ['q' => 'Azithro']));
        $resGen->assertOk();
        $this->assertCount(1, $resGen->json());

        // 3. Search by HSN Code
        $resHsn = $this->getJson(route('store.pos.search-medicines', ['q' => '30042010']));
        $resHsn->assertOk();
        $this->assertCount(1, $resHsn->json());

        // 4. Search by Batch Number
        $resBatch = $this->getJson(route('store.pos.search-medicines', ['q' => 'AZ-2026']));
        $resBatch->assertOk();
        $this->assertCount(1, $resBatch->json());
    }

    public function test_pos_customer_search_endpoint_returns_scoped_customers(): void
    {
        $this->actingAs($this->ownerA);

        $res = $this->getJson(route('store.pos.search-customers', ['q' => 'Rajesh']));
        $res->assertOk();
        $this->assertCount(1, $res->json());
        $this->assertEquals('Rajesh Sharma', $res->json()[0]['name']);
        $this->assertEquals('CUS-000001', $res->json()[0]['code']);

        // Ensure Store B customers are NOT returned
        $resTenant = $this->getJson(route('store.pos.search-customers', ['q' => 'Sneha']));
        $resTenant->assertOk();
        $this->assertCount(0, $resTenant->json());
    }

    public function test_sale_creation_fails_if_customer_belongs_to_another_store(): void
    {
        $this->actingAs($this->ownerA);

        $res = $this->post(route('store.pos.checkout'), [
            'customer_id' => $this->customerB->id, // Store B's customer
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'paid_amount' => 112.00,
            'payment_method' => 'cash',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ]);

        $res->assertSessionHasErrors(['customer_id']);
        $this->assertEquals(0, Sale::count());
    }

    public function test_complete_walkin_sale_deducts_stock_and_creates_payment_record(): void
    {
        $this->actingAs($this->ownerA);

        $initialStock = $this->batchA->quantity; // 50

        // 2 units * 100 = 200. Tax 12% = 24. Grand Total = 224.
        $res = $this->post(route('store.pos.checkout'), [
            'customer_name' => 'Walk-in Customer',
            'customer_phone' => '9900011122',
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'paid_amount' => 224.00,
            'payment_method' => 'cash',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 2,
                    'unit_price' => 100.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ]);

        $sale = Sale::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($sale);
        $res->assertRedirect(route('store.sales.show', $sale->id));

        $this->assertEquals(SaleStatus::COMPLETED, $sale->status);
        $this->assertEquals(PaymentStatus::PAID, $sale->payment_status);
        $this->assertEquals(224.00, (float) $sale->grand_total);
        $this->assertEquals(224.00, (float) $sale->paid_amount);
        $this->assertEquals(0.00, $sale->outstandingAmount());

        // Stock decreased atomically
        $this->batchA->refresh();
        $this->assertEquals($initialStock - 2, $this->batchA->quantity);

        // Stock movement recorded
        $movement = StockMovement::where('store_id', $this->storeA->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals(MovementType::SALE_OUT, $movement->type);
        $this->assertEquals(2, $movement->quantity);

        // Payment record created in StorePayment ledger
        $payment = StorePayment::where('sale_id', $sale->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(StorePaymentType::SALE_PAYMENT, $payment->type);
        $this->assertEquals(224.00, (float) $payment->amount);
    }

    public function test_partial_payment_sale_updates_customer_outstanding_balance(): void
    {
        $this->actingAs($this->ownerA);

        // 5 units * 100 = 500. Tax 12% = 60. Grand Total = 560.
        // Paid = 200, Due = 360.
        $this->post(route('store.pos.checkout'), [
            'customer_id' => $this->customerA->id,
            'customer_name' => $this->customerA->name,
            'customer_phone' => $this->customerA->phone,
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'paid_amount' => 200.00,
            'payment_method' => 'card',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ]);

        $sale = Sale::where('customer_id', $this->customerA->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(PaymentStatus::PARTIAL, $sale->payment_status);
        $this->assertEquals(560.00, (float) $sale->grand_total);
        $this->assertEquals(200.00, (float) $sale->paid_amount);
        $this->assertEquals(360.00, (float) $sale->outstandingAmount());

        // Verify Customer Outstanding reflects exactly ₹360.00
        $this->customerA->refresh();
        $this->assertEquals(360.00, (float) $this->customerA->outstandingAmount());
        $this->assertEquals(1, $this->customerA->visit_count);

        // Record partial settlement payment
        $this->post(route('store.sales.record-payment', $sale->id), [
            'amount' => 160.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'upi',
            'reference_number' => 'UPI-SETTLE-001',
        ]);

        $sale->refresh();
        $this->assertEquals(360.00, (float) $sale->paid_amount);
        $this->assertEquals(200.00, (float) $sale->outstandingAmount());

        $this->customerA->refresh();
        $this->assertEquals(200.00, (float) $this->customerA->outstandingAmount());
    }

    public function test_sales_history_filters_by_search_customer_dates_and_payment_status(): void
    {
        $this->actingAs($this->ownerA);

        // Sale 1: Completed & Paid
        $sale1 = Sale::create([
            'store_id' => $this->storeA->id,
            'customer_id' => $this->customerA->id,
            'customer_name' => 'Rajesh Sharma',
            'invoice_number' => 'INV-2026-000101',
            'sale_date' => Carbon::parse('2026-09-01'),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 200.00,
            'grand_total' => 200.00,
            'paid_amount' => 200.00,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => 'cash',
        ]);

        // Sale 2: Completed & Unpaid
        $sale2 = Sale::create([
            'store_id' => $this->storeA->id,
            'customer_name' => 'Walk-in Anita',
            'invoice_number' => 'INV-2026-000102',
            'sale_date' => Carbon::parse('2026-09-10'),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 500.00,
            'grand_total' => 500.00,
            'paid_amount' => 0.00,
            'payment_status' => PaymentStatus::UNPAID,
            'payment_method' => 'cash',
        ]);

        // Filter by search query (invoice number)
        $resSearch = $this->get(route('store.sales.index', ['search' => 'INV-2026-000101']));
        $resSearch->assertOk()
            ->assertSee('INV-2026-000101')
            ->assertDontSee('INV-2026-000102');

        // Filter by customer_id
        $resCust = $this->get(route('store.sales.index', ['customer_id' => $this->customerA->id]));
        $resCust->assertOk()
            ->assertSee('INV-2026-000101')
            ->assertDontSee('INV-2026-000102');

        // Filter by date range
        $resDate = $this->get(route('store.sales.index', [
            'from_date' => '2026-09-08',
            'to_date' => '2026-09-12',
        ]));
        $resDate->assertOk()
            ->assertSee('INV-2026-000102')
            ->assertDontSee('INV-2026-000101');

        // Filter by payment status
        $resPay = $this->get(route('store.sales.index', ['payment_status' => 'unpaid']));
        $resPay->assertOk()
            ->assertSee('INV-2026-000102')
            ->assertDontSee('INV-2026-000101');
    }

    public function test_sale_details_and_invoice_views_render_store_and_customer_details(): void
    {
        $this->actingAs($this->ownerA);

        $this->post(route('store.pos.checkout'), [
            'customer_id' => $this->customerA->id,
            'customer_name' => $this->customerA->name,
            'customer_phone' => $this->customerA->phone,
            'sale_date' => now()->toDateString(),
            'status' => 'completed',
            'paid_amount' => 112.00,
            'payment_method' => 'cash',
            'items' => [
                [
                    'medicine_id' => $this->medicineA->id,
                    'batch_id' => $this->batchA->id,
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'discount' => 0.00,
                    'gst_rate' => 12.00,
                ],
            ],
        ]);

        $sale = Sale::where('customer_id', $this->customerA->id)->first();

        // 1. Sale Details page
        $resShow = $this->get(route('store.sales.show', $sale->id));
        $resShow->assertOk()
            ->assertSee($sale->invoice_number)
            ->assertSee('Apex Healthcare Chemist')
            ->assertSee('Rajesh Sharma')
            ->assertSee('CUS-000001')
            ->assertSee('Dr. Verma')
            ->assertSee('AZ-2026-01');

        // 2. Printable Invoice view
        $resInv = $this->get(route('store.sales.invoice', $sale->id));
        $resInv->assertOk()
            ->assertSee('Apex Healthcare Chemist')
            ->assertSee('27ABCDE1234F1Z5') // GSTIN
            ->assertSee($sale->invoice_number)
            ->assertSee('Rajesh Sharma')
            ->assertSee('CUS-000001')
            ->assertSee('Dr. Verma')
            ->assertSee('Azithromycin 500mg')
            ->assertSee('30042010'); // HSN
    }

    public function test_cross_store_sale_and_invoice_access_is_forbidden(): void
    {
        $saleA = Sale::create([
            'store_id' => $this->storeA->id,
            'invoice_number' => 'INV-2026-999999',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 100.00,
            'grand_total' => 100.00,
            'paid_amount' => 100.00,
        ]);

        $this->actingAs($this->ownerB);

        // Store B owner cannot view Store A's sale details
        $resShow = $this->get(route('store.sales.show', $saleA->id));
        $resShow->assertStatus(404);

        // Store B owner cannot access Store A's printable invoice
        $resInv = $this->get(route('store.sales.invoice', $saleA->id));
        $resInv->assertStatus(404);
    }

    public function test_sales_and_pos_routes_respect_subscription_feature_access(): void
    {
        // Remove pos and sales_management features from plan
        $this->plan->update([
            'features' => ['medicine_management', 'inventory_management'],
        ]);

        $this->actingAs($this->ownerA);

        // POS checkout blocked
        $posRes = $this->get(route('store.pos.index'));
        $posRes->assertStatus(403);

        // Sales history blocked
        $salesRes = $this->get(route('store.sales.index'));
        $salesRes->assertStatus(403);
    }
}
