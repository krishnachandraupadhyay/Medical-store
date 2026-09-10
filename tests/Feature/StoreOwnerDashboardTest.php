<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreOwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected Store $storeB;

    protected User $ownerA;

    protected User $ownerB;

    protected User $superAdmin;

    protected SubscriptionPlan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Stores
        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'Sharma Medical Store',
            'email' => 'sharma@medical.com',
            'mobile' => '+91 9876543210',
            'city' => 'Varanasi',
            'state' => 'Uttar Pradesh',
            'pincode' => '221001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
            'drug_license_no' => 'DL-UP-2026-001',
            'gstin' => '09ABCDE1234F1Z5',
        ]);

        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Gupta Pharmacy',
            'email' => 'gupta@pharmacy.com',
            'mobile' => '+91 9876543211',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
            'drug_license_no' => 'DL-UP-2026-002',
            'gstin' => '09ABCDE1234F2Z6',
        ]);

        // 2. Setup Store Owners
        $this->ownerA = User::factory()->create([
            'name' => 'Rahul Sharma',
            'email' => 'rahul@sharmamedical.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Amit Gupta',
            'email' => 'amit@guptapharmacy.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        // 3. Setup Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@medistore.com',
            'password' => Hash::make('SuperAdmin123!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
            'store_id' => null,
        ]);

        // 4. Setup Subscription Plan
        $this->proPlan = SubscriptionPlan::create([
            'name' => 'Professional Tier',
            'slug' => 'professional-tier',
            'price' => 1499.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_staff' => 5,
                'max_medicines' => 5000,
            ],
            'features' => ['inventory', 'pos_billing', 'gst_reports'],
        ]);
    }

    /**
     * Test 1: Store Owner dashboard opens successfully and displays store identification.
     */
    public function test_authenticated_store_owner_can_access_dashboard_with_real_store_data(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get(route('store.dashboard'));

        $response->assertOk();
        $response->assertViewIs('store.dashboard');
        $response->assertSee('Sharma Medical Store');
        $response->assertSee('MED-000001');
        $response->assertSee('Varanasi');
        $response->assertSee('DL-UP-2026-001');
        $response->assertSee('Rahul Sharma');
    }

    /**
     * Test 2: Unauthenticated user is redirected to store login.
     */
    public function test_unauthenticated_user_redirected_to_store_login(): void
    {
        $response = $this->get(route('store.dashboard'));

        $response->assertRedirect(route('store.login'));
    }

    /**
     * Test 3: Super Admin cannot access Store Owner dashboard.
     */
    public function test_super_admin_cannot_access_store_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('store.dashboard'));

        $response->assertRedirect(route('store.login'));
    }

    /**
     * Test 4: Store Owner cannot access Super Admin routes.
     */
    public function test_store_owner_cannot_access_super_admin_routes(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get(route('super-admin.dashboard'));

        $response->assertRedirect(route('super-admin.login'));
    }

    /**
     * Test 5: Strict multi-tenant isolation — Store Owner A does NOT see Store Owner B data.
     */
    public function test_store_owner_never_sees_another_store_data(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get(route('store.dashboard'));

        $response->assertOk();
        $response->assertSee('Sharma Medical Store');
        $response->assertSee('MED-000001');
        $response->assertDontSee('Gupta Pharmacy');
        $response->assertDontSee('MED-000002');
        $response->assertDontSee('Amit Gupta');
    }

    /**
     * Test 6: Active subscription details render correctly when assigned.
     */
    public function test_active_subscription_details_render_on_dashboard(): void
    {
        // Assign Pro Plan to Store A
        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->proPlan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get(route('store.dashboard'));

        $response->assertOk();
        $response->assertSee('Professional Tier');
        $response->assertSee('₹1,499');
        $response->assertSee('5,000');
    }

    /**
     * Test 7: Fallback state renders gracefully when no subscription is assigned.
     */
    public function test_no_subscription_fallback_renders_gracefully(): void
    {
        $response = $this->actingAs($this->ownerB)
            ->get(route('store.dashboard'));

        $response->assertOk();
        $response->assertSee('No Active Plan');
        $response->assertSee('No subscription assigned yet');
    }

    /**
     * Test 8: Targeted store notifications are displayed while private notifications of other stores are isolated.
     */
    public function test_notifications_are_scoped_to_store_and_broadcasts(): void
    {
        // Broadcast notification for all stores
        Notification::create([
            'title' => 'System Maintenance Announcement',
            'message' => 'Platform maintenance scheduled at midnight.',
            'type' => NotificationType::SYSTEM,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        // Specific notification for Store A only
        Notification::create([
            'title' => 'Store A Special License Notice',
            'message' => 'Please verify your drug license expiry.',
            'type' => NotificationType::ANNOUNCEMENT,
            'priority' => NotificationPriority::IMPORTANT,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        // Specific notification for Store B only
        Notification::create([
            'title' => 'Store B Secret Notice',
            'message' => 'Confidential info for Store B.',
            'type' => NotificationType::ANNOUNCEMENT,
            'priority' => NotificationPriority::IMPORTANT,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeB->id,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        // Check Store A view
        $resA = $this->actingAs($this->ownerA)->get(route('store.dashboard'));
        $resA->assertSee('System Maintenance Announcement');
        $resA->assertSee('Store A Special License Notice');
        $resA->assertDontSee('Store B Secret Notice');

        // Check Store B view
        $resB = $this->actingAs($this->ownerB)->get(route('store.dashboard'));
        $resB->assertSee('System Maintenance Announcement');
        $resB->assertSee('Store B Secret Notice');
        $resB->assertDontSee('Store A Special License Notice');
    }

    /**
     * Test 9: Real payment transaction records render on dashboard.
     */
    public function test_real_payment_records_render_on_dashboard(): void
    {
        Payment::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->proPlan->id,
            'amount' => 1499.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI,
            'transaction_id' => 'TXN-STORE-A-999',
            'payment_date' => Carbon::today(),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('store.dashboard'));
        $response->assertOk();
        $response->assertSee('TXN-STORE-A-999');
        $response->assertSee('1,499.00');
    }

    /**
     * Test 10: Inactive Store Owner cannot access dashboard.
     */
    public function test_inactive_store_owner_cannot_access_dashboard(): void
    {
        $this->ownerA->update(['is_active' => false]);

        $response = $this->actingAs($this->ownerA)->get(route('store.dashboard'));
        $response->assertRedirect(route('store.login'));
    }

    /**
     * Test 11: Suspended Store blocks Store Owner from dashboard.
     */
    public function test_suspended_store_blocks_owner_from_dashboard(): void
    {
        $this->storeA->update(['status' => StoreStatus::SUSPENDED]);

        $response = $this->actingAs($this->ownerA)->get(route('store.dashboard'));
        $response->assertRedirect(route('store.login'));
    }
}
