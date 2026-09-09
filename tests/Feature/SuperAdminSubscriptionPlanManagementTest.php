<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\UserRole;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSubscriptionPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->storeOwner = User::factory()->create([
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
        ]);
    }

    /**
     * TEST 1: Super Admin can view Subscription Plans Directory.
     */
    public function test_super_admin_can_view_subscription_plans_list(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Growth Plan',
            'slug' => 'growth-plan',
            'description' => 'Ideal for expanding stores',
            'price' => 1499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 5, 'max_medicines' => 1000, 'max_invoices' => 2000, 'max_customers' => 1000],
            'features' => ['inventory_management', 'purchase_management', 'sales_management', 'reports'],
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans');

        $response->assertStatus(200);
        $response->assertSee('Subscription Plans');
        $response->assertSee('Create New Plan');
        $response->assertSee('Growth Plan');
        $response->assertSee('1,499.00');
    }

    /**
     * TEST 2: Super Admin can view Create Plan page.
     */
    public function test_super_admin_can_view_create_plan_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans/create');

        $response->assertStatus(200);
        $response->assertSee('Provision New Subscription Tier');
        $response->assertSee('General Information');
        $response->assertSee('Capacity Limits');
        $response->assertSee('Enabled Functional Modules');
    }

    /**
     * TEST 3: Super Admin can create a new Subscription Plan.
     */
    public function test_super_admin_can_create_new_subscription_plan(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/plans', [
            'name' => 'Enterprise Pro',
            'slug' => 'enterprise-pro',
            'description' => 'Unlimited capacity for multi-branch pharmacies.',
            'price' => '4999.00',
            'billing_cycle' => 'yearly',
            'trial_days' => '30',
            'status' => 'active',
            'is_popular' => '1',
            'sort_order' => '1',
            'limits' => [
                'max_staff' => -1,
                'max_medicines' => -1,
                'max_invoices' => -1,
                'max_customers' => -1,
            ],
            'features' => [
                'inventory_management',
                'purchase_management',
                'sales_management',
                'pos',
                'customer_management',
                'staff_management',
                'reports',
                'advanced_reports',
            ],
        ]);

        $plan = SubscriptionPlan::where('name', 'Enterprise Pro')->first();

        $this->assertNotNull($plan);
        $this->assertEquals('enterprise-pro', $plan->slug);
        $this->assertEquals(4999.00, (float) $plan->price);
        $this->assertEquals(BillingCycle::YEARLY, $plan->billing_cycle);
        $this->assertEquals(30, $plan->trial_days);
        $this->assertEquals(PlanStatus::ACTIVE, $plan->status);
        $this->assertTrue($plan->is_popular);
        $this->assertEquals(-1, $plan->getLimit('max_staff'));
        $this->assertTrue($plan->hasFeature('pos'));
        $this->assertTrue($plan->hasFeature('advanced_reports'));
        $this->assertEquals($this->superAdmin->id, $plan->created_by);

        $response->assertRedirect(route('super-admin.subscriptions.plans.show', $plan));
    }

    /**
     * TEST 4: Validation prevents duplicate plan name.
     */
    public function test_duplicate_plan_name_is_rejected(): void
    {
        SubscriptionPlan::create([
            'name' => 'Starter Plan',
            'slug' => 'starter-plan',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 7,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 2, 'max_medicines' => 200, 'max_invoices' => 500, 'max_customers' => 200],
            'features' => ['inventory_management'],
        ]);

        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/plans', [
            'name' => 'Starter Plan',
            'price' => '499.00',
            'billing_cycle' => 'monthly',
            'trial_days' => '7',
            'status' => 'active',
            'limits' => ['max_staff' => 2, 'max_medicines' => 200, 'max_invoices' => 500, 'max_customers' => 200],
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertEquals(1, SubscriptionPlan::count());
    }

    /**
     * TEST 5: Validation catches negative price, invalid billing cycle, or negative limits.
     */
    public function test_invalid_plan_attributes_trigger_validation(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/plans', [
            'name' => '',
            'price' => '-50',
            'billing_cycle' => 'invalid_cycle',
            'trial_days' => '-5',
            'status' => 'invalid_status',
            'limits' => [
                'max_staff' => -5,
                'max_medicines' => -10,
                'max_invoices' => -2,
                'max_customers' => -3,
            ],
            'features' => ['non_existing_feature_key'],
        ]);

        $response->assertSessionHasErrors(['name', 'price', 'billing_cycle', 'trial_days', 'status', 'limits.max_staff', 'features.0']);
    }

    /**
     * TEST 6: Super Admin can view Subscription Plan details.
     */
    public function test_super_admin_can_view_plan_details(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Retail Chemist Plus',
            'slug' => 'retail-chemist-plus',
            'description' => 'Designed for standalone retail stores',
            'price' => 799.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 4, 'max_medicines' => 800, 'max_invoices' => 1500, 'max_customers' => 1000],
            'features' => ['inventory_management', 'sales_management', 'reports'],
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/super-admin/subscriptions/plans/{$plan->id}");

        $response->assertStatus(200);
        $response->assertSee('Retail Chemist Plus');
        $response->assertSee('₹799.00');
        $response->assertSee('Resource Capacity Limits');
        $response->assertSee('Functional Modules Matrix');
    }

    /**
     * TEST 7: Super Admin can view Edit Plan page.
     */
    public function test_super_admin_can_view_edit_plan_page(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Standard Tier',
            'slug' => 'standard-tier',
            'price' => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 3, 'max_medicines' => 500, 'max_invoices' => 1000, 'max_customers' => 500],
            'features' => ['inventory_management'],
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/super-admin/subscriptions/plans/{$plan->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Subscription Tier');
        $response->assertSee('Standard Tier');
    }

    /**
     * TEST 8: Super Admin can update an existing Subscription Plan.
     */
    public function test_super_admin_can_update_subscription_plan(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Old Plan Name',
            'slug' => 'old-plan-name',
            'price' => 500.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 7,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 2, 'max_medicines' => 300, 'max_invoices' => 500, 'max_customers' => 300],
            'features' => ['inventory_management'],
        ]);

        $response = $this->actingAs($this->superAdmin)->put("/super-admin/subscriptions/plans/{$plan->id}", [
            'name' => 'Updated Plan Name',
            'slug' => 'updated-plan-name',
            'description' => 'Updated tier description',
            'price' => '899.00',
            'billing_cycle' => 'yearly',
            'trial_days' => '21',
            'status' => 'active',
            'is_popular' => '1',
            'sort_order' => '2',
            'limits' => [
                'max_staff' => 8,
                'max_medicines' => 2000,
                'max_invoices' => 5000,
                'max_customers' => 3000,
            ],
            'features' => ['inventory_management', 'purchase_management', 'pos'],
        ]);

        $plan->refresh();

        $this->assertEquals('Updated Plan Name', $plan->name);
        $this->assertEquals('updated-plan-name', $plan->slug);
        $this->assertEquals(899.00, (float) $plan->price);
        $this->assertEquals(BillingCycle::YEARLY, $plan->billing_cycle);
        $this->assertEquals(21, $plan->trial_days);
        $this->assertEquals(8, $plan->getLimit('max_staff'));
        $this->assertTrue($plan->hasFeature('pos'));
        $this->assertEquals($this->superAdmin->id, $plan->updated_by);

        $response->assertRedirect(route('super-admin.subscriptions.plans.show', $plan));
    }

    /**
     * TEST 9: Status toggle (Activate / Deactivate).
     */
    public function test_super_admin_can_toggle_plan_status(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Toggle Status Plan',
            'slug' => 'toggle-status-plan',
            'price' => 1200.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 5, 'max_medicines' => 1000, 'max_invoices' => 2000, 'max_customers' => 1000],
            'features' => ['inventory_management'],
        ]);

        // 1. Deactivate
        $deactRes = $this->actingAs($this->superAdmin)
            ->from(route('super-admin.subscriptions.plans.show', $plan))
            ->patch("/super-admin/subscriptions/plans/{$plan->id}/status", [
                'status' => 'inactive',
            ]);
        $plan->refresh();
        $this->assertEquals(PlanStatus::INACTIVE, $plan->status);
        $deactRes->assertRedirect(route('super-admin.subscriptions.plans.show', $plan));

        // 2. Activate
        $actRes = $this->actingAs($this->superAdmin)
            ->from(route('super-admin.subscriptions.plans.show', $plan))
            ->patch("/super-admin/subscriptions/plans/{$plan->id}/status", [
                'status' => 'active',
            ]);
        $plan->refresh();
        $this->assertEquals(PlanStatus::ACTIVE, $plan->status);
        $actRes->assertRedirect(route('super-admin.subscriptions.plans.show', $plan));
    }

    /**
     * TEST 10: Search plans by name, slug, or description.
     */
    public function test_super_admin_can_search_plans(): void
    {
        SubscriptionPlan::create([
            'name' => 'Ayurvedic Pharmacy Special',
            'slug' => 'ayurvedic-special',
            'price' => 599.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 7,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 2, 'max_medicines' => 300, 'max_invoices' => 500, 'max_customers' => 200],
        ]);

        SubscriptionPlan::create([
            'name' => 'Allopathic Chain Master',
            'slug' => 'allopathic-master',
            'price' => 2999.00,
            'billing_cycle' => BillingCycle::YEARLY,
            'trial_days' => 30,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 20, 'max_medicines' => 10000, 'max_invoices' => 20000, 'max_customers' => 10000],
        ]);

        $searchName = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans?search=Ayurvedic');
        $searchName->assertSee('Ayurvedic Pharmacy Special');
        $searchName->assertDontSee('Allopathic Chain Master');

        $searchSlug = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans?search=allopathic-master');
        $searchSlug->assertSee('Allopathic Chain Master');
        $searchSlug->assertDontSee('Ayurvedic Pharmacy Special');
    }

    /**
     * TEST 11: Filter plans by status and billing cycle.
     */
    public function test_super_admin_can_filter_plans_by_status_and_cycle(): void
    {
        SubscriptionPlan::create([
            'name' => 'Monthly Active Plan',
            'slug' => 'monthly-active',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 7,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 2, 'max_medicines' => 200, 'max_invoices' => 500, 'max_customers' => 200],
        ]);

        SubscriptionPlan::create([
            'name' => 'Yearly Inactive Plan',
            'slug' => 'yearly-inactive',
            'price' => 4999.00,
            'billing_cycle' => BillingCycle::YEARLY,
            'trial_days' => 0,
            'status' => PlanStatus::INACTIVE,
            'limits' => ['max_staff' => 2, 'max_medicines' => 200, 'max_invoices' => 500, 'max_customers' => 200],
        ]);

        // Filter active
        $activeRes = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans?status=active');
        $activeRes->assertSee('Monthly Active Plan');
        $activeRes->assertDontSee('Yearly Inactive Plan');

        // Filter inactive
        $inactiveRes = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans?status=inactive');
        $inactiveRes->assertSee('Yearly Inactive Plan');
        $inactiveRes->assertDontSee('Monthly Active Plan');

        // Filter yearly
        $yearlyRes = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans?cycle=yearly');
        $yearlyRes->assertSee('Yearly Inactive Plan');
        $yearlyRes->assertDontSee('Monthly Active Plan');
    }

    /**
     * TEST 12: Store Owner cannot access Subscription Plan management routes.
     */
    public function test_store_owner_cannot_access_subscription_plan_management(): void
    {
        $indexRes = $this->actingAs($this->storeOwner)->get('/super-admin/subscriptions/plans');
        $indexRes->assertRedirect('/super-admin/login');

        $createRes = $this->actingAs($this->storeOwner)->get('/super-admin/subscriptions/plans/create');
        $createRes->assertRedirect('/super-admin/login');

        $postRes = $this->actingAs($this->storeOwner)->post('/super-admin/subscriptions/plans', [
            'name' => 'Unauthorized Plan',
        ]);
        $postRes->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 13: Guest cannot access Subscription Plan management routes.
     */
    public function test_guest_cannot_access_subscription_plan_management(): void
    {
        $response = $this->get('/super-admin/subscriptions/plans');
        $response->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 14: Non-existent subscription plan returns 404.
     */
    public function test_non_existent_subscription_plan_returns_404(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/plans/999999');
        $response->assertStatus(404);
    }
}
