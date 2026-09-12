<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreStaffAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected Role $salesRole;

    protected function setUp(): void
    {
        parent::setUp();

        app(RbacService::class)->seedDefaultSystemRoles();

        $this->store = Store::create([
            'code' => 'MED-AUTH01',
            'name' => 'Apollo Medical Store',
            'email' => 'apollo@medstore.com',
            'mobile' => '+91 9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->salesRole = Role::where('slug', 'sales-staff')->whereNull('store_id')->firstOrFail();
    }

    /**
     * Test 1: Active staff can log in with valid credentials and last_login_at is updated.
     */
    public function test_active_staff_can_login_with_valid_credentials(): void
    {
        $staff = User::factory()->create([
            'name' => 'Suresh Kumar',
            'email' => 'suresh@medstore.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('store.login.submit'), [
            'email' => 'suresh@medstore.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('store.dashboard'));
        $this->assertAuthenticatedAs($staff);

        $staff->refresh();
        $this->assertNotNull($staff->last_login_at);
    }

    /**
     * Test 2: Inactive staff cannot log in and session is not established.
     */
    public function test_inactive_staff_cannot_login(): void
    {
        User::factory()->create([
            'name' => 'Deactivated Staff',
            'email' => 'inactive@medstore.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => false,
        ]);

        $response = $this->post(route('store.login.submit'), [
            'email' => 'inactive@medstore.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 3: Invalid credentials remain rejected for staff.
     */
    public function test_staff_login_with_invalid_credentials_rejected(): void
    {
        User::factory()->create([
            'name' => 'Suresh Kumar',
            'email' => 'suresh@medstore.com',
            'password' => Hash::make('CorrectPassword123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('store.login.submit'), [
            'email' => 'suresh@medstore.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 4: Staff whose assigned role is inactive cannot log in.
     */
    public function test_staff_with_inactive_role_cannot_login(): void
    {
        $inactiveRole = Role::create([
            'store_id' => $this->store->id,
            'name' => 'Temporary Trainee',
            'slug' => 'temp-trainee',
            'is_system' => false,
            'status' => 'inactive',
        ]);

        User::factory()->create([
            'name' => 'Trainee John',
            'email' => 'john@medstore.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $inactiveRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('store.login.submit'), [
            'email' => 'john@medstore.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 5: Staff whose store is inactive cannot log in.
     */
    public function test_staff_with_inactive_store_cannot_login(): void
    {
        $inactiveStore = Store::create([
            'code' => 'MED-INACTIVE',
            'name' => 'Closed Pharmacy',
            'email' => 'closed@medstore.com',
            'mobile' => '+91 9876543211',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Retail',
            'status' => StoreStatus::INACTIVE,
        ]);

        User::factory()->create([
            'name' => 'Staff Inactive Store',
            'email' => 'closedstaff@medstore.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $inactiveStore->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('store.login.submit'), [
            'email' => 'closedstaff@medstore.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 6: Deactivated staff with active session is immediately kicked out by middleware.
     */
    public function test_deactivated_staff_session_is_terminated_by_middleware(): void
    {
        $staff = User::factory()->create([
            'name' => 'Active Staff',
            'email' => 'active@medstore.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        // Deactivate staff
        $staff->update(['is_active' => false]);

        $response = $this->get(route('store.dashboard'));
        $response->assertRedirect(route('store.login'));
        $this->assertGuest();
    }

    /**
     * Test 7: Staff cannot access super admin routes.
     */
    public function test_staff_cannot_access_super_admin_routes(): void
    {
        $staff = User::factory()->create([
            'name' => 'Active Staff',
            'email' => 'staff@medstore.com',
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('super-admin.dashboard'));
        $response->assertRedirect(route('super-admin.login'));
    }
}
