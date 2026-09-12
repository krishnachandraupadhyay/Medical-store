<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Facades\CurrentStore;
use App\Facades\SubscriptionAccess;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SubscriptionAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $basicPlan;

    protected SubscriptionPlan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Super Admin
        $this->superAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // 2. Setup Plans
        $this->basicPlan = SubscriptionPlan::create([
            'name' => 'Basic Tier',
            'slug' => 'basic-tier',
            'price' => 499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_staff' => 2,
                'max_medicines' => 10,
                'max_invoices' => 100,
                'max_customers' => 50,
            ],
            'features' => [
                'pos',
            ],
        ]);

        $this->proPlan = SubscriptionPlan::create([
            'name' => 'Pro Tier',
            'slug' => 'pro-tier',
            'price' => 1499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_staff' => 10,
                'max_medicines' => -1, // Unlimited
                'max_invoices' => -1,
                'max_customers' => -1,
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
                'purchase_management',
                'pos',
            ],
        ]);

        // 3. Setup Store A & Owner A
        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'Store Alpha Pharmacy',
            'email' => 'storeA@medistore.test',
            'mobile' => '9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'ownerA@medistore.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        // 4. Setup Store B & Owner B
        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Store Beta Chemist',
            'email' => 'storeB@medistore.test',
            'mobile' => '9876543211',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'ownerB@medistore.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        // Register dummy test routes guarded by feature middleware
        Route::middleware(['web', 'auth', 'feature:medicine_management'])->get('/test-feature/medicine', function () {
            return response()->json(['success' => true, 'message' => 'Medicine Access Granted']);
        });

        Route::middleware(['web', 'auth', 'feature:inventory_management'])->get('/test-feature/inventory', function () {
            return response()->json(['success' => true, 'message' => 'Inventory Access Granted']);
        });
    }

    /**
     * TEST 1: CurrentStoreContext securely resolves only the authenticated Store Owner's assigned Store.
     */
    public function test_current_store_context_resolves_only_authorized_store(): void
    {
        $this->actingAs($this->ownerA);
        CurrentStore::reset();

        $this->assertNotNull(CurrentStore::get());
        $this->assertEquals($this->storeA->id, CurrentStore::id());
        $this->assertEquals('Store Alpha Pharmacy', CurrentStore::get()->name);

        $this->actingAs($this->ownerB);
        CurrentStore::reset();

        $this->assertNotNull(CurrentStore::get());
        $this->assertEquals($this->storeB->id, CurrentStore::id());
        $this->assertNotEquals($this->storeA->id, CurrentStore::id());
    }

    /**
     * TEST 2: Active subscription within valid calendar dates grants access.
     */
    public function test_active_subscription_grants_feature_access(): void
    {
        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->proPlan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $this->actingAs($this->ownerA);
        SubscriptionAccess::clearCache();

        $this->assertTrue(SubscriptionAccess::hasActiveSubscription($this->storeA));
        $this->assertTrue(SubscriptionAccess::hasFeature($this->storeA, 'medicine_management'));
        $this->assertTrue(SubscriptionAccess::hasFeature($this->storeA, 'medicine')); // Alias check

        $response = $this->getJson('/test-feature/medicine');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * TEST 3: Expired subscription (by calendar date) blocks access even if status column is active.
     */
    public function test_expired_subscription_by_date_is_denied(): void
    {
        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->proPlan->id,
            'start_date' => Carbon::today()->subDays(30),
            'end_date' => Carbon::today()->subDays(1), // Expired yesterday
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $this->actingAs($this->ownerA);
        SubscriptionAccess::clearCache();

        $this->assertFalse(SubscriptionAccess::hasActiveSubscription($this->storeA));

        $response = $this->getJson('/test-feature/medicine');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'Access Denied',
            'code' => 'SUBSCRIPTION_EXPIRED',
        ]);
    }

    /**
     * TEST 4: Inactive subscriptions (Cancelled, Suspended, Pending) are denied.
     */
    public function test_inactive_subscription_statuses_are_denied(): void
    {
        // Cancelled
        $sub = Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->proPlan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::CANCELLED,
        ]);

        $this->actingAs($this->ownerA);
        SubscriptionAccess::clearCache();

        $this->assertFalse(SubscriptionAccess::hasActiveSubscription($this->storeA));
        $this->getJson('/test-feature/medicine')->assertStatus(403);

        // Suspended
        $sub->update(['status' => SubscriptionStatus::SUSPENDED]);
        SubscriptionAccess::clearCache();
        $this->getJson('/test-feature/medicine')->assertStatus(403);

        // Pending
        $sub->update(['status' => SubscriptionStatus::PENDING]);
        SubscriptionAccess::clearCache();
        $this->getJson('/test-feature/medicine')->assertStatus(403);
    }

    /**
     * TEST 5: Active subscription without requested feature is denied with FEATURE_NOT_INCLUDED.
     */
    public function test_plan_missing_requested_feature_is_denied(): void
    {
        // Basic plan only has 'pos', not 'medicine_management'
        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'start_date' => Carbon::today()->subDays(2),
            'end_date' => Carbon::today()->addDays(28),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $this->actingAs($this->ownerA);
        SubscriptionAccess::clearCache();

        $this->assertTrue(SubscriptionAccess::hasActiveSubscription($this->storeA));
        $this->assertFalse(SubscriptionAccess::hasFeature($this->storeA, 'medicine_management'));

        $response = $this->getJson('/test-feature/medicine');
        $response->assertStatus(403);
        $response->assertJson([
            'code' => 'FEATURE_NOT_INCLUDED',
            'feature' => 'medicine_management',
        ]);
    }

    /**
     * TEST 6: Quota limit calculations and enforcement.
     */
    public function test_quota_limits_and_usage_calculations(): void
    {
        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->basicPlan->id, // max_medicines = 10
            'start_date' => Carbon::today()->subDays(2),
            'end_date' => Carbon::today()->addDays(28),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        SubscriptionAccess::clearCache();

        // 1. Register test usage resolver
        SubscriptionAccess::registerUsageResolver('max_medicines', function (Store $store) {
            return 8; // Simulated usage of 8
        });

        $this->assertEquals(10, SubscriptionAccess::getLimit($this->storeA, 'max_medicines'));
        $this->assertEquals(8, SubscriptionAccess::getUsage($this->storeA, 'max_medicines'));
        $this->assertEquals(2, SubscriptionAccess::remaining($this->storeA, 'max_medicines'));
        $this->assertTrue(SubscriptionAccess::checkLimit($this->storeA, 'max_medicines', 1)); // 8 + 1 <= 10
        $this->assertTrue(SubscriptionAccess::checkLimit($this->storeA, 'max_medicines', 2)); // 8 + 2 <= 10
        $this->assertFalse(SubscriptionAccess::checkLimit($this->storeA, 'max_medicines', 3)); // 8 + 3 > 10

        // 2. Simulated usage at capacity (10)
        SubscriptionAccess::registerUsageResolver('max_medicines', fn (Store $store) => 10);
        $this->assertEquals(0, SubscriptionAccess::remaining($this->storeA, 'max_medicines'));
        $this->assertFalse(SubscriptionAccess::checkLimit($this->storeA, 'max_medicines', 1));

        // 3. Unlimited plan (-1)
        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->proPlan->id, // max_medicines = -1
            'start_date' => Carbon::today()->subDays(2),
            'end_date' => Carbon::today()->addDays(28),
            'status' => SubscriptionStatus::ACTIVE,
        ]);
        SubscriptionAccess::clearCache();

        $this->assertTrue(SubscriptionAccess::isUnlimited($this->storeB, 'max_medicines'));
        $this->assertEquals(-1, SubscriptionAccess::getLimit($this->storeB, 'max_medicines'));
        $this->assertEquals(-1, SubscriptionAccess::remaining($this->storeB, 'max_medicines'));
        $this->assertTrue(SubscriptionAccess::checkLimit($this->storeB, 'max_medicines', 9999));
    }

    /**
     * TEST 7: Super Admin bypasses store subscription restrictions.
     */
    public function test_super_admin_bypasses_subscription_restrictions(): void
    {
        // Store A has no active subscription
        $this->actingAs($this->superAdmin);

        $response = $this->getJson('/test-feature/medicine');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * TEST 8: Tenant Isolation - Store A cannot access Store B's subscription or data.
     */
    public function test_store_a_cannot_access_store_b_subscription(): void
    {
        // Store B has active Pro subscription
        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->proPlan->id,
            'start_date' => Carbon::today()->subDays(2),
            'end_date' => Carbon::today()->addDays(28),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        // Store A has NO subscription
        $this->actingAs($this->ownerA);
        CurrentStore::reset();
        SubscriptionAccess::clearCache();

        $this->assertFalse(SubscriptionAccess::hasActiveSubscription());
        $this->assertFalse(SubscriptionAccess::canUseFeature(null, 'medicine_management'));

        // Querying for null/current store gives Store A (inactive), never Store B
        $this->assertEquals($this->storeA->id, CurrentStore::id());
        $this->assertNull(SubscriptionAccess::getActiveSubscription());
    }
}
