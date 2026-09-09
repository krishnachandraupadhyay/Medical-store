<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1: Unauthenticated user accessing /super-admin/dashboard is redirected to Super Admin login.
     */
    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/super-admin/dashboard');

        $response->assertRedirect('/super-admin/login');
        $this->assertGuest();
    }

    /**
     * TEST 2: Valid Super Admin can log in and access /super-admin/dashboard.
     */
    public function test_valid_super_admin_can_login_and_access_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@medistore.test',
            'password' => Hash::make('Secret12345!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $loginResponse = $this->post('/super-admin/login', [
            'email' => 'superadmin@medistore.test',
            'password' => 'Secret12345!',
        ]);

        $loginResponse->assertRedirect('/super-admin/dashboard');
        $this->assertAuthenticatedAs($superAdmin);

        $dashboardResponse = $this->actingAs($superAdmin)->get('/super-admin/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('System Dashboard');
        $dashboardResponse->assertSee('SUPER_ADMIN');
    }

    /**
     * TEST 3: Store Owner cannot log in through Super Admin portal or access Super Admin dashboard.
     */
    public function test_store_owner_cannot_login_or_access_super_admin_dashboard(): void
    {
        $storeOwner = User::factory()->create([
            'email' => 'owner@medistore.test',
            'password' => Hash::make('Secret12345!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => 1,
        ]);

        // Attempting to log in via Super Admin login page
        $loginResponse = $this->post('/super-admin/login', [
            'email' => 'owner@medistore.test',
            'password' => 'Secret12345!',
        ]);

        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Attempting to access dashboard with Store Owner session
        $dashboardResponse = $this->actingAs($storeOwner)->get('/super-admin/dashboard');
        $dashboardResponse->assertRedirect('/super-admin/login');
        $dashboardResponse->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TEST 4: Login fails when an incorrect password is provided.
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'superadmin@medistore.test',
            'password' => Hash::make('Secret12345!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post('/super-admin/login', [
            'email' => 'superadmin@medistore.test',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TEST 5: Inactive Super Admin cannot log in.
     */
    public function test_inactive_super_admin_is_denied_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@medistore.test',
            'password' => Hash::make('Secret12345!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => false,
        ]);

        $response = $this->post('/super-admin/login', [
            'email' => 'inactive@medistore.test',
            'password' => 'Secret12345!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TEST 6 & 7: Super Admin logout destroys session and subsequent dashboard access redirects to login.
     */
    public function test_super_admin_logout_destroys_session_and_protects_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@medistore.test',
            'password' => Hash::make('Secret12345!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin);
        $this->assertAuthenticatedAs($superAdmin);

        // Perform logout
        $logoutResponse = $this->post('/super-admin/logout');
        $logoutResponse->assertRedirect('/super-admin/login');
        $this->assertGuest();

        // Accessing dashboard after logout
        $dashboardResponse = $this->get('/super-admin/dashboard');
        $dashboardResponse->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 8: Public Super Admin registration URL does not exist.
     */
    public function test_no_public_super_admin_registration_route_exists(): void
    {
        $this->get('/register-super-admin')->assertStatus(404);
        $this->get('/super-admin/register')->assertStatus(404);
        $this->post('/super-admin/register')->assertStatus(404);
    }

    /**
     * TEST 9: Empty email and password submit validation errors.
     */
    public function test_validation_errors_for_empty_fields(): void
    {
        $response = $this->post('/super-admin/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }
}
