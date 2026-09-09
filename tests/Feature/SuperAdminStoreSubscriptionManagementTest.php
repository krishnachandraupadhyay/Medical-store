<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminStoreSubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected Store $activeStore;

    protected Store $inactiveStore;

    protected SubscriptionPlan $activePlan;

    protected SubscriptionPlan $inactivePlan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Super Admin User
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // 2. Active Store & Owner
        $this->activeStore = Store::create([
            'code' => 'MED-000001',
            'name' => 'Apex Care Pharmacy',
            'email' => 'apex@care.com',
            'mobile' => '+91 9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->storeOwner = User::factory()->create([
            'name' => 'Rahul Sharma',
            'email' => 'rahul@apexcare.com',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->activeStore->id,
            'is_active' => true,
        ]);

        // 3. Inactive Store
        $this->inactiveStore = Store::create([
            'code' => 'MED-000002',
            'name' => 'Closed Pharmacy',
            'email' => 'closed@pharmacy.com',
            'mobile' => '+91 9876543211',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Wholesale',
            'status' => StoreStatus::INACTIVE,
        ]);

        // 4. Active Plan
        $this->activePlan = SubscriptionPlan::create([
            'name' => 'Growth Plan',
            'slug' => 'growth-plan',
            'price' => 1499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 5, 'max_medicines' => 1000, 'max_invoices' => 2000, 'max_customers' => 1000],
            'features' => ['inventory_management', 'purchase_management', 'sales_management', 'pos'],
        ]);

        // 5. Inactive Plan
        $this->inactivePlan = SubscriptionPlan::create([
            'name' => 'Legacy Discontinued Plan',
            'slug' => 'legacy-plan',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::INACTIVE,
            'limits' => ['max_staff' => 1, 'max_medicines' => 100, 'max_invoices' => 100, 'max_customers' => 100],
            'features' => ['sales_management'],
        ]);
    }

    /**
     * TEST 1: Super Admin can view Store Subscriptions index page.
     */
    public function test_super_admin_can_view_store_subscriptions_index(): void
    {
        Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'trial_ends_at' => Carbon::today()->addDays(14),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/stores');

        $response->assertStatus(200);
        $response->assertSee('Store Subscriptions');
        $response->assertSee('Apex Care Pharmacy');
        $response->assertSee('Growth Plan');
        $response->assertSee('Rahul Sharma');
    }

    /**
     * TEST 2: Super Admin can view Create Subscription assignment page.
     */
    public function test_super_admin_can_view_create_subscription_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/stores/create');

        $response->assertStatus(200);
        $response->assertSee('Assign Subscription Plan to Store');
        $response->assertSee('Apex Care Pharmacy');
        $response->assertSee('Growth Plan');
    }

    /**
     * TEST 3: Super Admin can assign an active plan to an active store.
     */
    public function test_super_admin_can_assign_active_plan_to_active_store(): void
    {
        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->addDays(30)->toDateString();
        $trialDate = Carbon::today()->addDays(14)->toDateString();

        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'trial_ends_at' => $trialDate,
            'status' => 'active',
            'notes' => 'Provisioned with special introductory support.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscriptions', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'status' => 'active',
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /**
     * TEST 4: Inactive store cannot receive a new subscription.
     */
    public function test_inactive_store_cannot_receive_subscription(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->inactiveStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('store_id');
        $this->assertDatabaseMissing('subscriptions', [
            'store_id' => $this->inactiveStore->id,
        ]);
    }

    /**
     * TEST 5: Inactive plan cannot be assigned to a store.
     */
    public function test_inactive_plan_cannot_be_assigned(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->inactivePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('subscription_plan_id');
        $this->assertDatabaseMissing('subscriptions', [
            'subscription_plan_id' => $this->inactivePlan->id,
        ]);
    }

    /**
     * TEST 6: Validation fails if end_date is before start_date.
     */
    public function test_validation_fails_if_end_date_is_before_start_date(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->subDays(5)->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    /**
     * TEST 7: Validation fails if trial_ends_at is after end_date.
     */
    public function test_validation_fails_if_trial_ends_at_is_after_end_date(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(10)->toDateString(),
            'trial_ends_at' => Carbon::today()->addDays(20)->toDateString(),
            'status' => 'trial',
        ]);

        $response->assertSessionHasErrors('trial_ends_at');
    }

    /**
     * TEST 8: Existing subscription history remains intact when a new one is assigned.
     */
    public function test_existing_subscription_history_remains_intact(): void
    {
        // First Subscription (Older)
        $firstSub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->subDays(60),
            'end_date' => Carbon::today()->subDays(30),
            'status' => SubscriptionStatus::EXPIRED,
            'created_by' => $this->superAdmin->id,
        ]);

        // Second Subscription (New)
        $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);

        // Both subscriptions exist in database
        $this->assertEquals(2, Subscription::where('store_id', $this->activeStore->id)->count());
        $this->assertDatabaseHas('subscriptions', ['id' => $firstSub->id, 'status' => 'expired']);
    }

    /**
     * TEST 9: Conflicting active subscription is safely superseded.
     */
    public function test_conflicting_active_subscription_is_safely_superseded(): void
    {
        // Existing Active
        $oldActive = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->addDays(20),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        // Create new replacement subscription
        $this->actingAs($this->superAdmin)->post('/super-admin/subscriptions/stores', [
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(365)->toDateString(),
            'status' => 'active',
        ]);

        // Old subscription status transitioned to cancelled
        $oldActive->refresh();
        $this->assertEquals(SubscriptionStatus::CANCELLED, $oldActive->status);

        // New active subscription exists
        $newActive = Subscription::where('store_id', $this->activeStore->id)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->first();
        $this->assertNotNull($newActive);
        $this->assertEquals(Carbon::today()->addDays(365)->toDateString(), $newActive->end_date->toDateString());
    }

    /**
     * TEST 10: Super Admin can view subscription details with inherited plan features.
     */
    public function test_super_admin_can_view_subscription_details(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'trial_ends_at' => Carbon::today()->addDays(14),
            'status' => SubscriptionStatus::ACTIVE,
            'notes' => 'Custom trial granted.',
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/super-admin/subscriptions/stores/{$sub->id}");

        $response->assertStatus(200);
        $response->assertSee('Apex Care Pharmacy');
        $response->assertSee('Growth Plan');
        $response->assertSee('Custom trial granted.');
        $response->assertSee('Inventory Management');
        $response->assertSee('Point of Sale (POS)');
    }

    /**
     * TEST 11: Super Admin can view edit subscription page.
     */
    public function test_super_admin_can_view_edit_subscription_page(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/super-admin/subscriptions/stores/{$sub->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Store Subscription');
        $response->assertSee('Apex Care Pharmacy');
    }

    /**
     * TEST 12: Super Admin can update subscription details.
     */
    public function test_super_admin_can_update_subscription(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        $newEndDate = Carbon::today()->addDays(60)->toDateString();

        $response = $this->actingAs($this->superAdmin)->put("/super-admin/subscriptions/stores/{$sub->id}", [
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => $newEndDate,
            'status' => 'suspended',
            'notes' => 'Suspended due to audit verification.',
        ]);

        $response->assertRedirect();
        $sub->refresh();

        $this->assertEquals(SubscriptionStatus::SUSPENDED, $sub->status);
        $this->assertEquals($newEndDate, $sub->end_date->toDateString());
        $this->assertEquals('Suspended due to audit verification.', $sub->notes);
    }

    /**
     * TEST 13: Super Admin can update subscription status (cancel / suspend / activate).
     */
    public function test_super_admin_can_update_subscription_status(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        // Cancel status patch
        $response = $this->actingAs($this->superAdmin)->patch("/super-admin/subscriptions/stores/{$sub->id}/status", [
            'status' => 'cancelled',
        ]);

        $response->assertRedirect();
        $sub->refresh();
        $this->assertEquals(SubscriptionStatus::CANCELLED, $sub->status);
    }

    /**
     * TEST 14: Expired subscription is recognized when end_date has passed.
     */
    public function test_expired_subscription_is_recognized_when_end_date_has_passed(): void
    {
        $expiredSub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today()->subDays(40),
            'end_date' => Carbon::today()->subDays(10),
            'status' => SubscriptionStatus::ACTIVE, // Stored as active, but date is past
            'created_by' => $this->superAdmin->id,
        ]);

        $this->assertTrue($expiredSub->isExpired());
        $this->assertEquals(SubscriptionStatus::EXPIRED, $expiredSub->effectiveStatus());
        $this->assertEquals('Expired', $expiredSub->effectiveStatusLabel());
    }

    /**
     * TEST 15: Store Owner (Non-Super-Admin) cannot access store subscription routes.
     */
    public function test_store_owner_cannot_access_store_subscription_routes(): void
    {
        $response = $this->actingAs($this->storeOwner)->get('/super-admin/subscriptions/stores');
        $response->assertRedirect('/super-admin/login');

        $responseCreate = $this->actingAs($this->storeOwner)->get('/super-admin/subscriptions/stores/create');
        $responseCreate->assertRedirect('/super-admin/login');

        $responseJson = $this->actingAs($this->storeOwner)->getJson('/super-admin/subscriptions/stores');
        $responseJson->assertStatus(403);
    }

    /**
     * TEST 16: Unauthenticated guest cannot access store subscription routes.
     */
    public function test_unauthenticated_guest_cannot_access_store_subscription_routes(): void
    {
        $response = $this->get('/super-admin/subscriptions/stores');
        $response->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 17: Search by store name and owner name works.
     */
    public function test_search_by_store_and_owner_works(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        // Search by store name
        $resStore = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/stores?search=Apex+Care');
        $resStore->assertStatus(200);
        $resStore->assertSee('Apex Care Pharmacy');

        // Search by owner name
        $resOwner = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/stores?search=Rahul');
        $resOwner->assertStatus(200);
        $resOwner->assertSee('Rahul Sharma');
    }

    /**
     * TEST 18: Filters by plan and status work.
     */
    public function test_filters_by_plan_and_status_work(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->activeStore->id,
            'subscription_plan_id' => $this->activePlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        $resPlan = $this->actingAs($this->superAdmin)->get("/super-admin/subscriptions/stores?plan_id={$this->activePlan->id}");
        $resPlan->assertStatus(200);
        $resPlan->assertSee('Apex Care Pharmacy');

        $resStatus = $this->actingAs($this->superAdmin)->get('/super-admin/subscriptions/stores?status=active');
        $resStatus->assertStatus(200);
        $resStatus->assertSee('Apex Care Pharmacy');
    }
}
