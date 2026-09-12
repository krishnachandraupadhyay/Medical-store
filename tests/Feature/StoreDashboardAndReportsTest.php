<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $fullPlan;

    protected SubscriptionPlan $restrictedPlan;

    protected Medicine $medA;

    protected Batch $batchA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fullPlan = SubscriptionPlan::create([
            'name' => 'Enterprise Plan',
            'slug' => 'enterprise-plan',
            'price' => 4999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'features' => [
                'medicine_management',
                'inventory_management',
                'purchase_management',
                'sales_management',
                'sales_return',
                'purchase_return',
                'expense_management',
                'customer_management',
                'supplier_management',
                'reports',
            ],
            'limits' => [],
        ]);

        $this->restrictedPlan = SubscriptionPlan::create([
            'name' => 'Starter Plan',
            'slug' => 'starter-plan',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'features' => [
                'medicine_management',
            ],
            'limits' => [],
        ]);

        // Store A with Full Plan
        $this->storeA = Store::create([
            'code' => 'DASH-001',
            'name' => 'Apollo Care Chemist',
            'email' => 'apollo@chemist.test',
            'mobile' => '9811122233',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'alpha@apollo.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->fullPlan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->fullPlan->features,
        ]);

        // Store B with Full Plan
        $this->storeB = Store::create([
            'code' => 'DASH-002',
            'name' => 'MedPlus Store',
            'email' => 'medplus@store.test',
            'mobile' => '9844455566',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560002',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'beta@medplus.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->fullPlan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->fullPlan->features,
        ]);

        // Seed Store A inventory
        $this->medA = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Paracetamol 650mg',
            'generic_name' => 'Paracetamol',
            'status' => 'active',
            'reorder_level' => 15,
        ]);

        $this->batchA = Batch::create([
            'store_id' => $this->storeA->id,
            'medicine_id' => $this->medA->id,
            'batch_number' => 'PARA-650-01',
            'expiry_date' => Carbon::now()->addMonths(6),
            'purchase_price' => 10.00,
            'selling_price' => 20.00,
            'mrp' => 25.00,
            'quantity' => 10, // Below min_stock_level (15) => triggers Low Stock
            'status' => 'active',
        ]);
    }

    public function test_dashboard_loads_with_correct_store_aggregates(): void
    {
        // Completed Sale for Store A
        $sale = Sale::create([
            'store_id' => $this->storeA->id,
            'invoice_number' => 'INV-001',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
            'paid_amount' => 700.00,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'medicine_id' => $this->medA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 50,
            'unit_price' => 20.00,
            'mrp' => 25.00,
            'line_total' => 1000.00,
        ]);

        // Completed Purchase for Store A
        $supplier = Supplier::create([
            'store_id' => $this->storeA->id,
            'name' => 'Apex Pharma',
            'phone' => '9888811111',
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'store_id' => $this->storeA->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'PO-001',
            'purchase_date' => now()->toDateString(),
            'status' => PurchaseStatus::COMPLETED,
            'subtotal' => 2000.00,
            'grand_total' => 2000.00,
            'paid_amount' => 1200.00,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        // Paid Expense for Store A
        $category = ExpenseCategory::create([
            'store_id' => $this->storeA->id,
            'name' => 'Rent',
        ]);

        Expense::create([
            'store_id' => $this->storeA->id,
            'expense_category_id' => $category->id,
            'expense_number' => 'EXP-001',
            'title' => 'Shop Rent',
            'amount' => 500.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::UPI,
            'status' => ExpenseStatus::PAID,
        ]);

        // Sale & Purchase for Store B (Should NOT be visible to Store A)
        Sale::create([
            'store_id' => $this->storeB->id,
            'invoice_number' => 'INV-B-999',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 99999.00,
            'grand_total' => 99999.00,
            'paid_amount' => 99999.00,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.dashboard'));
        $response->assertOk();

        // Check view data variables
        $response->assertViewHas('summaryCards', function ($cards) {
            return $cards['total_sales'] == 1000.00
                && $cards['total_purchases'] == 2000.00
                && $cards['total_expenses'] == 500.00
                && $cards['receivables'] == 300.00 // 1000 - 700
                && $cards['payables'] == 800.00;    // 2000 - 1200
        });

        // Top medicines must include Paracetamol 650mg
        $response->assertViewHas('topMedicines', function ($top) {
            return count($top) >= 1 && $top[0]->name === 'Paracetamol 650mg';
        });

        // Low stock medicines must include Paracetamol 650mg
        $response->assertViewHas('lowStock', function ($low) {
            return collect($low)->contains('name', 'Paracetamol 650mg');
        });
    }

    public function test_dashboard_date_filter_validation(): void
    {
        // Invalid range: from_date > to_date
        $response = $this->actingAs($this->ownerA)->get(route('store.dashboard', [
            'period' => 'custom',
            'from_date' => '2026-09-15',
            'to_date' => '2026-09-10',
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['to_date']);
    }

    public function test_reports_access_denied_without_subscription_feature(): void
    {
        // Assign restricted plan (without 'reports') to Store A
        Subscription::where('store_id', $this->storeA->id)->delete();
        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->restrictedPlan->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => $this->restrictedPlan->features,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.reports.index'));
        $response->assertStatus(403);
    }

    public function test_sales_report_filters_and_export(): void
    {
        $customer = Customer::create([
            'store_id' => $this->storeA->id,
            'name' => 'Alice Wonder',
            'phone' => '9900011122',
            'customer_code' => 'CUST-ALICE',
            'status' => 'active',
        ]);

        Sale::create([
            'store_id' => $this->storeA->id,
            'customer_id' => $customer->id,
            'customer_name' => 'Alice Wonder',
            'invoice_number' => 'INV-ALICE-1',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 450.00,
            'grand_total' => 450.00,
            'paid_amount' => 450.00,
            'payment_status' => PaymentStatus::PAID,
        ]);

        // Filter by customer
        $response = $this->actingAs($this->ownerA)->get(route('store.reports.sales', [
            'customer_id' => $customer->id,
        ]));
        $response->assertOk();
        $response->assertViewHas('sales', function ($sales) {
            return $sales->total() === 1 && $sales->first()->invoice_number === 'INV-ALICE-1';
        });

        // CSV Export
        $exportResponse = $this->actingAs($this->ownerA)->get(route('store.reports.sales', [
            'export' => 'csv',
        ]));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('content-type'));
        $this->assertStringContainsString('INV-ALICE-1', $exportResponse->streamedContent());
    }

    public function test_cross_store_report_isolation(): void
    {
        // Store B Sale
        Sale::create([
            'store_id' => $this->storeB->id,
            'invoice_number' => 'INV-STORE-B-SECRET',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 8888.00,
            'grand_total' => 8888.00,
            'paid_amount' => 8888.00,
            'payment_status' => PaymentStatus::PAID,
        ]);

        // Store A requests Sales Report
        $response = $this->actingAs($this->ownerA)->get(route('store.reports.sales'));
        $response->assertOk();
        $response->assertDontSee('INV-STORE-B-SECRET');

        // Store A requests CSV Export
        $exportResponse = $this->actingAs($this->ownerA)->get(route('store.reports.sales', ['export' => 'csv']));
        $this->assertStringNotContainsString('INV-STORE-B-SECRET', $exportResponse->streamedContent());
    }

    public function test_medicine_sales_report_net_quantity_calculation(): void
    {
        $sale = Sale::create([
            'store_id' => $this->storeA->id,
            'invoice_number' => 'INV-MED-01',
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 400.00,
            'grand_total' => 400.00,
            'paid_amount' => 400.00,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'medicine_id' => $this->medA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 20,
            'unit_price' => 20.00,
            'mrp' => 25.00,
            'line_total' => 400.00,
        ]);

        // Sales Return of 5 units
        $salesReturn = SalesReturn::create([
            'store_id' => $this->storeA->id,
            'sale_id' => $sale->id,
            'return_number' => 'RET-001',
            'return_date' => now()->toDateString(),
            'total_amount' => 100.00,
            'status' => ReturnStatus::COMPLETED,
        ]);

        SalesReturnItem::create([
            'sales_return_id' => $salesReturn->id,
            'sale_item_id' => $saleItem->id,
            'medicine_id' => $this->medA->id,
            'batch_id' => $this->batchA->id,
            'quantity' => 5,
            'unit_price' => 20.00,
            'line_total' => 100.00,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.reports.medicine-sales'));
        $response->assertOk();

        $response->assertViewHas('medicinesReport', function ($report) {
            $item = collect($report->items())->firstWhere('id', $this->medA->id);

            return $item !== null
                && (int) $item->sold_quantity === 20
                && (int) $item->return_quantity === 5
                && (int) $item->net_quantity === 15;
        });
    }
}
