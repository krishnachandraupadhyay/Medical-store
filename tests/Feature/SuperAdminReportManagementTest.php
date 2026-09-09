<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminReportManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected Store $store1;

    protected Store $store2;

    protected SubscriptionPlan $basicPlan;

    protected SubscriptionPlan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();

        // Super Admin
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Stores
        $this->store1 = Store::create([
            'code' => 'MED-000001',
            'name' => 'Metro Care Pharmacy',
            'email' => 'metro@care.com',
            'mobile' => '+91 9876543210',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $this->store2 = Store::create([
            'code' => 'MED-000002',
            'name' => 'Zenith Wellness Store',
            'email' => 'zenith@wellness.com',
            'mobile' => '+91 9876543211',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // Store Owner
        $this->storeOwner = User::factory()->create([
            'name' => 'Amit Kumar',
            'email' => 'amit@metrocare.com',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->store1->id,
            'is_active' => true,
        ]);

        // Plans
        $this->basicPlan = SubscriptionPlan::create([
            'name' => 'Basic Tier',
            'slug' => 'basic-tier',
            'price' => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 7,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 3, 'max_medicines' => 500],
            'features' => ['inventory_management'],
            'sort_order' => 1,
        ]);

        $this->proPlan = SubscriptionPlan::create([
            'name' => 'Pro Tier',
            'slug' => 'pro-tier',
            'price' => 2499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 10, 'max_medicines' => 2000],
            'features' => ['inventory_management', 'sales_management', 'pos'],
            'sort_order' => 2,
        ]);

        // Subscriptions
        Subscription::create([
            'store_id' => $this->store1->id,
            'subscription_plan_id' => $this->proPlan->id,
            'start_date' => Carbon::today()->subDays(20),
            'end_date' => Carbon::today()->addDays(10), // Expiring in 10 days
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        Subscription::create([
            'store_id' => $this->store2->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25), // Expiring in 25 days
            'status' => SubscriptionStatus::TRIAL,
            'trial_ends_at' => Carbon::today()->addDays(2),
            'created_by' => $this->superAdmin->id,
        ]);

        // Payments
        Payment::create([
            'store_id' => $this->store1->id,
            'subscription_plan_id' => $this->proPlan->id,
            'amount' => 2499.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI,
            'transaction_id' => 'TXN-REPORT-001',
            'payment_date' => Carbon::now()->subDays(3),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        Payment::create([
            'store_id' => $this->store2->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'amount' => 999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::CARD,
            'transaction_id' => 'TXN-REPORT-002',
            'payment_date' => Carbon::now()->subDays(1),
            'status' => PaymentStatus::PENDING,
            'created_by' => $this->superAdmin->id,
        ]);

        Payment::create([
            'store_id' => $this->store1->id,
            'amount' => 5000.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::ONLINE,
            'transaction_id' => 'TXN-REPORT-003',
            'payment_date' => Carbon::now()->subDays(2),
            'status' => PaymentStatus::FAILED,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    public function test_super_admin_can_view_reports_overview(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.overview'));

        $response->assertOk();
        $response->assertViewIs('super-admin.reports.overview');
        $response->assertSee('System Reports');
        $response->assertSee('2,499.00'); // Paid revenue
        $response->assertSee('Metro Care Pharmacy');
    }

    public function test_store_owner_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.reports.overview'));

        $response->assertRedirect(route('super-admin.login'));

        $responseJson = $this->actingAs($this->storeOwner)
            ->getJson(route('super-admin.reports.overview'));

        $responseJson->assertStatus(403);
    }

    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get(route('super-admin.reports.overview'));
        $response->assertRedirect(route('super-admin.login'));
    }

    public function test_super_admin_can_view_store_reports(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.stores'));

        $response->assertOk();
        $response->assertViewIs('super-admin.reports.stores');
        $response->assertSee('Metro Care Pharmacy');
        $response->assertSee('Zenith Wellness Store');

        // Search filter
        $searchResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.stores', ['search' => 'Metro']));
        $searchResponse->assertSee('Metro Care Pharmacy');
        $searchResponse->assertDontSee('Zenith Wellness Store');
    }

    public function test_super_admin_can_view_subscription_reports(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.subscriptions'));

        $response->assertOk();
        $response->assertViewIs('super-admin.reports.subscriptions');
        $response->assertSee('Pro Tier');
        $response->assertSee('Basic Tier');

        // Filter by Plan
        $planFilterResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.subscriptions', ['plan_id' => $this->proPlan->id]));
        $planFilterResponse->assertSee('Metro Care Pharmacy');
        $planFilterResponse->assertDontSee('Zenith Wellness Store');
    }

    public function test_super_admin_can_view_payment_reports(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.payments'));

        $response->assertOk();
        $response->assertViewIs('super-admin.reports.payments');
        $response->assertSee('TXN-REPORT-001');
        $response->assertSee('TXN-REPORT-002');
        $response->assertSee('TXN-REPORT-003');

        // Filter by Status: PAID
        $paidResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.payments', ['status' => PaymentStatus::PAID->value]));
        $paidResponse->assertSee('TXN-REPORT-001');
        $paidResponse->assertDontSee('TXN-REPORT-002');
        $paidResponse->assertDontSee('TXN-REPORT-003');
    }

    public function test_expiring_subscriptions_report(): void
    {
        // Store 1 expires in 10 days (should show in 15 days filter, but NOT in 7 days)
        // Store 2 expires in 25 days (should show in 30 days filter, but NOT in 15 days)

        // 7 Days
        $res7 = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.expiring-subscriptions', ['days' => 7]));
        $res7->assertOk();
        $res7->assertDontSee('Metro Care Pharmacy');
        $res7->assertDontSee('Zenith Wellness Store');

        // 15 Days (should include Store 1)
        $res15 = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.expiring-subscriptions', ['days' => 15]));
        $res15->assertOk();
        $res15->assertSee('Metro Care Pharmacy');
        $res15->assertDontSee('Zenith Wellness Store');

        // 30 Days (should include Store 1 and Store 2)
        $res30 = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.expiring-subscriptions', ['days' => 30]));
        $res30->assertOk();
        $res30->assertSee('Metro Care Pharmacy');
        $res30->assertSee('Zenith Wellness Store');
    }

    public function test_csv_exports_work(): void
    {
        // 1. Export Stores
        $storesExport = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.stores.export'));
        $storesExport->assertOk();
        $this->assertStringContainsString('text/csv', $storesExport->headers->get('content-type'));

        // 2. Export Subscriptions
        $subsExport = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.subscriptions.export'));
        $subsExport->assertOk();
        $this->assertStringContainsString('text/csv', $subsExport->headers->get('content-type'));

        // 3. Export Payments
        $paymentsExport = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.reports.payments.export'));
        $paymentsExport->assertOk();
        $this->assertStringContainsString('text/csv', $paymentsExport->headers->get('content-type'));
    }
}
