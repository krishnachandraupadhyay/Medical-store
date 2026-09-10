<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreOwnerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Store $activeStore;

    protected Store $inactiveStore;

    protected Store $suspendedStore;

    protected User $activeOwner;

    protected User $inactiveOwner;

    protected User $ownerWithoutStore;

    protected User $ownerWithInactiveStore;

    protected User $ownerWithSuspendedStore;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Active Store
        $this->activeStore = Store::create([
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

        // 2. Inactive Store
        $this->inactiveStore = Store::create([
            'code' => 'MED-000002',
            'name' => 'Gupta Pharmacy',
            'email' => 'gupta@pharmacy.com',
            'mobile' => '+91 9876543211',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'store_type' => 'Retail',
            'status' => StoreStatus::INACTIVE,
        ]);

        // 3. Suspended Store
        $this->suspendedStore = Store::create([
            'code' => 'MED-000003',
            'name' => 'City Medical',
            'email' => 'city@medical.com',
            'mobile' => '+91 9876543212',
            'city' => 'Kanpur',
            'state' => 'Uttar Pradesh',
            'pincode' => '208001',
            'store_type' => 'Retail',
            'status' => StoreStatus::SUSPENDED,
        ]);

        // 4. Active Store Owner
        $this->activeOwner = User::factory()->create([
            'name' => 'Rahul Sharma',
            'email' => 'rahul@sharmamedical.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->activeStore->id,
        ]);

        // 5. Inactive Store Owner
        $this->inactiveOwner = User::factory()->create([
            'name' => 'Inactive User',
            'email' => 'inactive@sharmamedical.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => false,
            'store_id' => $this->activeStore->id,
        ]);

        // 6. Store Owner Without Store
        $this->ownerWithoutStore = User::factory()->create([
            'name' => 'Orphan Owner',
            'email' => 'orphan@owner.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => null,
        ]);

        // 7. Store Owner with Inactive Store
        $this->ownerWithInactiveStore = User::factory()->create([
            'name' => 'Amit Gupta',
            'email' => 'amit@guptapharmacy.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->inactiveStore->id,
        ]);

        // 8. Store Owner with Suspended Store
        $this->ownerWithSuspendedStore = User::factory()->create([
            'name' => 'Raj Singh',
            'email' => 'raj@citymedical.com',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->suspendedStore->id,
        ]);

        // 9. Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@medistore.com',
            'password' => Hash::make('SuperAdmin123!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
            'store_id' => null,
        ]);
    }

    /**
     * Test 1: Store Owner can view login form.
     */
    public function test_store_owner_can_view_login_form(): void
    {
        $response = $this->get(route('store.login'));

        $response->assertOk();
        $response->assertViewIs('store.auth.login');
        $response->assertSee('Store Owner Login');
    }

    /**
     * Test 2: Correct Store Owner credentials -> login successful and redirects to dashboard.
     */
    public function test_active_store_owner_can_login_with_correct_credentials(): void
    {
        $response = $this->post(route('store.login.submit'), [
            'email' => 'rahul@sharmamedical.com',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('store.dashboard'));
        $this->assertAuthenticatedAs($this->activeOwner);
    }

    /**
     * Test 3: Wrong password -> login rejected with error.
     */
    public function test_login_rejected_with_wrong_password(): void
    {
        $response = $this->post(route('store.login.submit'), [
            'email' => 'rahul@sharmamedical.com',
            'password' => 'WrongPassword999',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 4: Inactive Store Owner -> login rejected.
     */
    public function test_inactive_store_owner_cannot_login(): void
    {
        $response = $this->post(route('store.login.submit'), [
            'email' => 'inactive@sharmamedical.com',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 5: Store Owner whose store is Inactive -> login rejected.
     */
    public function test_store_owner_with_inactive_store_cannot_login(): void
    {
        $response = $this->post(route('store.login.submit'), [
            'email' => 'amit@guptapharmacy.com',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 6: Store Owner whose store is Suspended -> login rejected.
     */
    public function test_store_owner_with_suspended_store_cannot_login(): void
    {
        $response = $this->post(route('store.login.submit'), [
            'email' => 'raj@citymedical.com',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 7: Store Owner without valid store relationship -> login rejected.
     */
    public function test_store_owner_without_store_cannot_login(): void
    {
        $response = $this->post(route('store.login.submit'), [
            'email' => 'orphan@owner.com',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 8: Unauthenticated user opening /store/dashboard -> redirected to /store/login.
     */
    public function test_unauthenticated_user_redirected_to_store_login(): void
    {
        $response = $this->get(route('store.dashboard'));

        $response->assertRedirect(route('store.login'));
    }

    /**
     * Test 9: Authenticated Store Owner can view store placeholder dashboard.
     */
    public function test_authenticated_store_owner_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->activeOwner)
            ->get(route('store.dashboard'));

        $response->assertOk();
        $response->assertViewIs('store.dashboard');
        $response->assertSee($this->activeOwner->name);
        $response->assertSee('Sharma Medical Store');
        $response->assertSee('MED-000001');
    }

    /**
     * Test 10: Store Owner opening Super Admin route -> access denied / logged out.
     */
    public function test_store_owner_cannot_access_super_admin_routes(): void
    {
        $response = $this->actingAs($this->activeOwner)
            ->get(route('super-admin.dashboard'));

        $response->assertRedirect(route('super-admin.login'));
    }

    /**
     * Test 11: Super Admin opening Store Owner dashboard -> access denied / redirected.
     */
    public function test_super_admin_cannot_access_store_dashboard_as_owner(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('store.dashboard'));

        $response->assertRedirect(route('store.login'));
    }

    /**
     * Test 12: Middleware blocks active Store Owner if store is suspended after login.
     */
    public function test_middleware_blocks_owner_if_store_status_changes_to_suspended(): void
    {
        // Authenticate owner
        $this->actingAs($this->activeOwner);

        // Suspend store in DB
        $this->activeStore->update(['status' => StoreStatus::SUSPENDED]);

        $response = $this->get(route('store.dashboard'));

        $response->assertRedirect(route('store.login'));
        $this->assertGuest();
    }

    /**
     * Test 13: Store Owner Logout -> session invalidated and dashboard inaccessible.
     */
    public function test_store_owner_logout(): void
    {
        $this->actingAs($this->activeOwner);

        $response = $this->post(route('store.logout'));

        $response->assertRedirect(route('store.login'));
        $this->assertGuest();

        // Dashboard inaccessible now
        $dashboardResponse = $this->get(route('store.dashboard'));
        $dashboardResponse->assertRedirect(route('store.login'));
    }

    /**
     * Test 14: Store context is bound strictly to the authenticated user.
     */
    public function test_store_context_is_bound_to_authenticated_owner(): void
    {
        $this->actingAs($this->activeOwner);

        $response = $this->get(route('store.dashboard'));

        $response->assertOk();
        $response->assertSee($this->activeStore->name);
        $response->assertDontSee($this->inactiveStore->name);
        $response->assertDontSee($this->suspendedStore->name);
    }
}
