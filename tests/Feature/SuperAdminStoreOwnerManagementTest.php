<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminStoreOwnerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected Store $storeA;

    protected Store $storeB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'Metro Care Pharmacy',
            'mobile' => '9876500001',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'pincode' => '560001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Apollo Life Chemist',
            'mobile' => '9876500002',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->storeOwner = User::factory()->create([
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeA->id,
            'is_active' => true,
        ]);
    }

    /**
     * TEST 1: Super Admin views Store Owners Directory.
     */
    public function test_super_admin_can_view_store_owners_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners');

        $response->assertStatus(200);
        $response->assertSee('Store Owners Directory');
        $response->assertSee('Add Store Owner');
        $response->assertSee($this->storeOwner->name);
        $response->assertSee($this->storeA->name);
    }

    /**
     * TEST 2: Super Admin views Create Store Owner Page.
     */
    public function test_super_admin_can_view_create_store_owner_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners/create');

        $response->assertStatus(200);
        $response->assertSee('Register Store Owner');
        $response->assertSee('Metro Care Pharmacy');
        $response->assertSee('Apollo Life Chemist');
    }

    /**
     * TEST 3: Super Admin creates a new Store Owner and assigns to a Store.
     */
    public function test_super_admin_can_create_store_owner_and_assign_to_store(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/store-owners', [
            'store_id' => $this->storeB->id,
            'name' => 'Ramesh Gupta',
            'email' => 'ramesh.gupta@apollolife.test',
            'mobile' => '9988776655',
            'password' => 'SecureOwner#2026',
            'password_confirmation' => 'SecureOwner#2026',
            'is_active' => '1',
        ]);

        $owner = User::where('email', 'ramesh.gupta@apollolife.test')->first();

        $this->assertNotNull($owner);
        $this->assertEquals('Ramesh Gupta', $owner->name);
        $this->assertEquals('9988776655', $owner->mobile);
        $this->assertEquals($this->storeB->id, $owner->store_id);
        $this->assertEquals(UserRole::STORE_OWNER, $owner->role);
        $this->assertTrue($owner->is_active);
        $this->assertTrue(Hash::check('SecureOwner#2026', $owner->password));

        $response->assertRedirect(route('super-admin.store-owners.show', $owner));
    }

    /**
     * TEST 4: Validation on required and invalid fields.
     */
    public function test_store_owner_creation_requires_valid_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/store-owners', [
            'store_id' => '',
            'name' => '',
            'email' => 'invalid-email',
            'mobile' => '',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
            'is_active' => '',
        ]);

        $response->assertSessionHasErrors(['store_id', 'name', 'email', 'mobile', 'password', 'is_active']);
    }

    /**
     * TEST 5: Business Rule: Prevent assigning two active owners to the same store.
     */
    public function test_cannot_assign_two_active_owners_to_same_store(): void
    {
        // storeA already has $this->storeOwner as active owner
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/store-owners', [
            'store_id' => $this->storeA->id,
            'name' => 'Second Owner',
            'email' => 'second.owner@metrocare.test',
            'mobile' => '9111222333',
            'password' => 'SecureOwner#2026',
            'password_confirmation' => 'SecureOwner#2026',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors(['store_id']);
        $this->assertNull(User::where('email', 'second.owner@metrocare.test')->first());
    }

    /**
     * TEST 6: Can assign a new active owner if the previous owner is deactivated.
     */
    public function test_can_assign_new_active_owner_if_previous_owner_is_inactive(): void
    {
        $this->storeOwner->update(['is_active' => false]);

        $response = $this->actingAs($this->superAdmin)->post('/super-admin/store-owners', [
            'store_id' => $this->storeA->id,
            'name' => 'Replacement Owner',
            'email' => 'replacement@metrocare.test',
            'mobile' => '9111222333',
            'password' => 'SecureOwner#2026',
            'password_confirmation' => 'SecureOwner#2026',
            'is_active' => '1',
        ]);

        $newOwner = User::where('email', 'replacement@metrocare.test')->first();
        $this->assertNotNull($newOwner);
        $this->assertTrue($newOwner->is_active);
        $response->assertRedirect(route('super-admin.store-owners.show', $newOwner));
    }

    /**
     * TEST 7: Search store owners by name, email, mobile, and store name/code.
     */
    public function test_super_admin_can_search_store_owners(): void
    {
        $ownerB = User::factory()->create([
            'name' => 'Sunil Verma',
            'email' => 'sunil.verma@example.com',
            'mobile' => '9911223344',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeB->id,
            'is_active' => true,
        ]);

        // Search by name
        $searchName = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners?search=Sunil');
        $searchName->assertSee('Sunil Verma');
        $searchName->assertDontSee($this->storeOwner->name);

        // Search by email
        $searchEmail = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners?search=sunil.verma');
        $searchEmail->assertSee('Sunil Verma');

        // Search by mobile
        $searchMobile = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners?search=9911223344');
        $searchMobile->assertSee('Sunil Verma');

        // Search by store code
        $searchCode = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners?search=MED-000002');
        $searchCode->assertSee('Sunil Verma');
        $searchCode->assertDontSee($this->storeOwner->name);
    }

    /**
     * TEST 8: Filter store owners by status (active / inactive).
     */
    public function test_super_admin_can_filter_store_owners_by_status(): void
    {
        $inactiveOwner = User::factory()->create([
            'name' => 'Inactive John',
            'email' => 'john.inactive@example.com',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeB->id,
            'is_active' => false,
        ]);

        // Filter active
        $activeRes = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners?status=active');
        $activeRes->assertSee($this->storeOwner->name);
        $activeRes->assertDontSee('Inactive John');

        // Filter inactive
        $inactiveRes = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners?status=inactive');
        $inactiveRes->assertSee('Inactive John');
        $inactiveRes->assertDontSee($this->storeOwner->name);
    }

    /**
     * TEST 9: Filter store owners by store.
     */
    public function test_super_admin_can_filter_store_owners_by_store(): void
    {
        $ownerB = User::factory()->create([
            'name' => 'Owner For Store B',
            'email' => 'ownerb@example.com',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeB->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/super-admin/store-owners?store_id={$this->storeB->id}");
        $response->assertSee('Owner For Store B');
        $response->assertDontSee($this->storeOwner->name);
    }

    /**
     * TEST 10: View store owner details.
     */
    public function test_super_admin_can_view_store_owner_details(): void
    {
        $response = $this->actingAs($this->superAdmin)->get("/super-admin/store-owners/{$this->storeOwner->id}");

        $response->assertStatus(200);
        $response->assertSee($this->storeOwner->name);
        $response->assertSee($this->storeOwner->email);
        $response->assertSee($this->storeA->name);
        $response->assertSee($this->storeA->code);
    }

    /**
     * TEST 11: View edit store owner page.
     */
    public function test_super_admin_can_view_edit_store_owner_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get("/super-admin/store-owners/{$this->storeOwner->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Store Owner Profile');
        $response->assertSee($this->storeOwner->name);
        $response->assertSee($this->storeOwner->email);
    }

    /**
     * TEST 12: Super Admin updates store owner details (without password change).
     */
    public function test_super_admin_can_update_store_owner_details(): void
    {
        $response = $this->actingAs($this->superAdmin)->put("/super-admin/store-owners/{$this->storeOwner->id}", [
            'store_id' => $this->storeA->id,
            'name' => 'Updated Owner Name',
            'email' => 'updated.owner@metrocare.test',
            'mobile' => '9888777666',
            'is_active' => '1',
        ]);

        $this->storeOwner->refresh();

        $this->assertEquals('Updated Owner Name', $this->storeOwner->name);
        $this->assertEquals('updated.owner@metrocare.test', $this->storeOwner->email);
        $this->assertEquals('9888777666', $this->storeOwner->mobile);
        $response->assertRedirect(route('super-admin.store-owners.show', $this->storeOwner));
    }

    /**
     * TEST 13: Super Admin resets store owner password.
     */
    public function test_super_admin_can_update_store_owner_password(): void
    {
        $oldPasswordHash = $this->storeOwner->password;

        $response = $this->actingAs($this->superAdmin)->put("/super-admin/store-owners/{$this->storeOwner->id}", [
            'store_id' => $this->storeA->id,
            'name' => $this->storeOwner->name,
            'email' => $this->storeOwner->email,
            'mobile' => $this->storeOwner->mobile,
            'password' => 'NewOwnerSecret#2026',
            'password_confirmation' => 'NewOwnerSecret#2026',
            'is_active' => '1',
        ]);

        $this->storeOwner->refresh();

        $this->assertNotEquals($oldPasswordHash, $this->storeOwner->password);
        $this->assertTrue(Hash::check('NewOwnerSecret#2026', $this->storeOwner->password));
        $response->assertRedirect(route('super-admin.store-owners.show', $this->storeOwner));
    }

    /**
     * TEST 14: Status toggle between Active and Inactive.
     */
    public function test_super_admin_can_toggle_store_owner_status(): void
    {
        // 1. Deactivate
        $deactRes = $this->actingAs($this->superAdmin)
            ->from(route('super-admin.store-owners.show', $this->storeOwner))
            ->patch("/super-admin/store-owners/{$this->storeOwner->id}/status", [
                'is_active' => '0',
            ]);
        $this->storeOwner->refresh();
        $this->assertFalse($this->storeOwner->is_active);
        $deactRes->assertRedirect(route('super-admin.store-owners.show', $this->storeOwner));

        // 2. Activate
        $actRes = $this->actingAs($this->superAdmin)
            ->from(route('super-admin.store-owners.show', $this->storeOwner))
            ->patch("/super-admin/store-owners/{$this->storeOwner->id}/status", [
                'is_active' => '1',
            ]);
        $this->storeOwner->refresh();
        $this->assertTrue($this->storeOwner->is_active);
        $actRes->assertRedirect(route('super-admin.store-owners.show', $this->storeOwner));
    }

    /**
     * TEST 15: Conflict prevention on activation: Cannot activate if store already has another active owner.
     */
    public function test_cannot_activate_owner_if_store_already_has_another_active_owner(): void
    {
        // create a secondary inactive owner for storeA
        $inactiveOwner = User::factory()->create([
            'name' => 'Secondary Inactive',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->storeA->id,
            'is_active' => false,
        ]);

        // Attempt to activate while $this->storeOwner is active
        $response = $this->actingAs($this->superAdmin)->patch("/super-admin/store-owners/{$inactiveOwner->id}/status", [
            'is_active' => '1',
        ]);

        $inactiveOwner->refresh();
        $this->assertFalse($inactiveOwner->is_active);
        $response->assertSessionHas('error');
    }

    /**
     * TEST 16: Non-super-admin (Store Owner) cannot access Store Owner management routes.
     */
    public function test_store_owner_cannot_access_store_owner_management_routes(): void
    {
        $indexResponse = $this->actingAs($this->storeOwner)->get('/super-admin/store-owners');
        $indexResponse->assertRedirect('/super-admin/login');

        $createResponse = $this->actingAs($this->storeOwner)->get('/super-admin/store-owners/create');
        $createResponse->assertRedirect('/super-admin/login');

        $postResponse = $this->actingAs($this->storeOwner)->post('/super-admin/store-owners', [
            'name' => 'Malicious User',
        ]);
        $postResponse->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 17: Guest cannot access Store Owner management routes.
     */
    public function test_guest_cannot_access_store_owner_management_routes(): void
    {
        $response = $this->get('/super-admin/store-owners');
        $response->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 18: Non-existent Store Owner returns 404.
     */
    public function test_non_existent_store_owner_returns_404(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/store-owners/999999');
        $response->assertStatus(404);
    }
}
