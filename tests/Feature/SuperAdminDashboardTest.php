<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1: Super Admin login -> Dashboard opens with 200 OK.
     */
    public function test_super_admin_can_access_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'admin@medistore.test',
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('System Dashboard');
        $response->assertSee('Total Stores');
        $response->assertSee('Store Owners');
        $response->assertSee('Quick System Actions');
    }

    /**
     * TEST 2: Store Owner attempts to access dashboard -> Access denied.
     */
    public function test_store_owner_is_denied_dashboard_access(): void
    {
        $storeOwner = User::factory()->create([
            'email' => 'owner@medistore.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
        ]);

        $response = $this->actingAs($storeOwner)->get('/super-admin/dashboard');

        $response->assertRedirect('/super-admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TEST 3: Unauthenticated user accessing dashboard -> Redirect to login.
     */
    public function test_unauthenticated_user_redirects_to_login(): void
    {
        $response = $this->get('/super-admin/dashboard');

        $response->assertRedirect('/super-admin/login');
        $this->assertGuest();
    }

    /**
     * TEST 4: Dashboard statistics match database records.
     */
    public function test_dashboard_statistics_match_database(): void
    {
        $superAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Create 3 active store owners and 2 inactive store owners
        User::factory()->count(3)->create([
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
        ]);
        User::factory()->count(2)->create([
            'role' => UserRole::STORE_OWNER,
            'is_active' => false,
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/dashboard');

        $response->assertStatus(200);
        // Total store owners should be 5
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_store_owners'] === 5
                && $stats['active_store_owners'] === 3
                && $stats['inactive_store_owners'] === 2
                && $stats['total_super_admins'] === 1;
        });
    }

    /**
     * TEST 5: No store records shows proper empty state.
     */
    public function test_empty_state_shown_when_no_stores_exist(): void
    {
        $superAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('No stores available yet');
        $response->assertSee('No system activity logged yet');
    }

    /**
     * TEST 6: Inactive Super Admin is denied access to dashboard.
     */
    public function test_inactive_super_admin_denied_dashboard_access(): void
    {
        $inactiveAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveAdmin)->get('/super-admin/dashboard');

        $response->assertRedirect('/super-admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TEST 7: Logout from dashboard destroys session and redirects to login.
     */
    public function test_logout_from_dashboard_destroys_session(): void
    {
        $superAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post('/super-admin/logout');

        $response->assertRedirect('/super-admin/login');
        $this->assertGuest();
    }
}
