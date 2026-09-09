<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminStoreManagementTest extends TestCase
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
     * TEST 1: Super Admin opens Store List.
     */
    public function test_super_admin_can_view_store_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/stores');

        $response->assertStatus(200);
        $response->assertSee('Registered Medical Stores');
        $response->assertSee('Add New Store');
    }

    /**
     * TEST 2: Create a new store with automatic code generation and logo upload.
     */
    public function test_super_admin_can_create_new_store(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('store-logo.png', 200, 200);

        $response = $this->actingAs($this->superAdmin)->post('/super-admin/stores', [
            'name' => 'Metro Care Pharmacy',
            'email' => 'metro@carepharmacy.test',
            'mobile' => '9876543210',
            'alternate_mobile' => '9123456780',
            'address' => '123 Health Ave, Suite 4',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'pincode' => '560001',
            'gstin' => '29ABCDE1234F1Z5',
            'drug_license_no' => 'KA-BLR-20B-123456',
            'license_expiry_date' => '2028-12-31',
            'store_type' => 'Retail',
            'status' => 'ACTIVE',
            'logo' => $logo,
        ]);

        $store = Store::first();

        $this->assertNotNull($store);
        $this->assertEquals('MED-000001', $store->code);
        $this->assertEquals('Metro Care Pharmacy', $store->name);
        $this->assertEquals(StoreStatus::ACTIVE, $store->status);
        $this->assertNotNull($store->logo);

        Storage::disk('public')->assertExists($store->logo);
        $response->assertRedirect(route('super-admin.stores.show', $store));
    }

    /**
     * TEST 3 & 4: Search stores by name and code.
     */
    public function test_super_admin_can_search_stores(): void
    {
        Store::create([
            'code' => 'MED-000001',
            'name' => 'Apollo Medicure',
            'mobile' => '9876500001',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        Store::create([
            'code' => 'MED-000002',
            'name' => 'Max Pharma Care',
            'mobile' => '9876500002',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'store_type' => 'Wholesale',
            'status' => StoreStatus::ACTIVE,
        ]);

        // Search by name
        $nameSearch = $this->actingAs($this->superAdmin)->get('/super-admin/stores?search=Apollo');
        $nameSearch->assertStatus(200);
        $nameSearch->assertSee('Apollo Medicure');
        $nameSearch->assertDontSee('Max Pharma Care');

        // Search by code
        $codeSearch = $this->actingAs($this->superAdmin)->get('/super-admin/stores?search=MED-000002');
        $codeSearch->assertStatus(200);
        $codeSearch->assertSee('Max Pharma Care');
        $codeSearch->assertDontSee('Apollo Medicure');
    }

    /**
     * TEST 5, 6, 7: Filter stores by status (Active, Inactive, Suspended).
     */
    public function test_super_admin_can_filter_stores_by_status(): void
    {
        Store::create([
            'code' => 'MED-000001',
            'name' => 'Active Pharmacy',
            'mobile' => '9876500001',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        Store::create([
            'code' => 'MED-000002',
            'name' => 'Inactive Pharmacy',
            'mobile' => '9876500002',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Retail',
            'status' => StoreStatus::INACTIVE,
        ]);

        Store::create([
            'code' => 'MED-000003',
            'name' => 'Suspended Pharmacy',
            'mobile' => '9876500003',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Retail',
            'status' => StoreStatus::SUSPENDED,
        ]);

        // Filter Active
        $activeResponse = $this->actingAs($this->superAdmin)->get('/super-admin/stores?status=active');
        $activeResponse->assertSee('Active Pharmacy');
        $activeResponse->assertDontSee('Inactive Pharmacy');
        $activeResponse->assertDontSee('Suspended Pharmacy');

        // Filter Inactive
        $inactiveResponse = $this->actingAs($this->superAdmin)->get('/super-admin/stores?status=inactive');
        $inactiveResponse->assertSee('Inactive Pharmacy');
        $inactiveResponse->assertDontSee('Active Pharmacy');
        $inactiveResponse->assertDontSee('Suspended Pharmacy');

        // Filter Suspended
        $suspendedResponse = $this->actingAs($this->superAdmin)->get('/super-admin/stores?status=suspended');
        $suspendedResponse->assertSee('Suspended Pharmacy');
        $suspendedResponse->assertDontSee('Active Pharmacy');
        $suspendedResponse->assertDontSee('Inactive Pharmacy');
    }

    /**
     * TEST 8: Open Store Details.
     */
    public function test_super_admin_can_view_store_details(): void
    {
        $store = Store::create([
            'code' => 'MED-000001',
            'name' => 'Sunshine Chemist',
            'email' => 'contact@sunshine.test',
            'mobile' => '9876500001',
            'address' => '45 Main Boulevard',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/super-admin/stores/{$store->id}");

        $response->assertStatus(200);
        $response->assertSee('Sunshine Chemist');
        $response->assertSee('MED-000001');
        $response->assertSee('Chennai');
        $response->assertSee('600001');
    }

    /**
     * TEST 9: Edit store information (Store Code remains permanent).
     */
    public function test_super_admin_can_edit_store_and_code_is_protected(): void
    {
        $store = Store::create([
            'code' => 'MED-000001',
            'name' => 'Original Name Chemist',
            'mobile' => '9876500001',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->superAdmin)->put("/super-admin/stores/{$store->id}", [
            'code' => 'MED-TAMPERED-999', // should be ignored
            'name' => 'Updated Name Chemist',
            'mobile' => '9999999999',
            'city' => 'Kolkata Central',
            'state' => 'West Bengal',
            'pincode' => '700002',
            'store_type' => 'Wholesale',
            'status' => 'ACTIVE',
        ]);

        $store->refresh();

        $this->assertEquals('MED-000001', $store->code); // code unchanged
        $this->assertEquals('Updated Name Chemist', $store->name);
        $this->assertEquals('9999999999', $store->mobile);
        $this->assertEquals('Kolkata Central', $store->city);
        $this->assertEquals('Wholesale', $store->store_type);

        $response->assertRedirect(route('super-admin.stores.show', $store));
    }

    /**
     * TEST 10, 11, 12: Status transitions (Deactivate, Activate, Suspend).
     */
    public function test_super_admin_can_transition_store_status(): void
    {
        $store = Store::create([
            'code' => 'MED-000001',
            'name' => 'Status Lifecycle Pharmacy',
            'mobile' => '9876500001',
            'city' => 'Hyderabad',
            'state' => 'Telangana',
            'pincode' => '500001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        // 1. Deactivate
        $this->actingAs($this->superAdmin)->patch("/super-admin/stores/{$store->id}/status", [
            'status' => 'INACTIVE',
        ]);
        $store->refresh();
        $this->assertEquals(StoreStatus::INACTIVE, $store->status);

        // 2. Suspend
        $this->actingAs($this->superAdmin)->patch("/super-admin/stores/{$store->id}/status", [
            'status' => 'SUSPENDED',
        ]);
        $store->refresh();
        $this->assertEquals(StoreStatus::SUSPENDED, $store->status);

        // 3. Activate
        $this->actingAs($this->superAdmin)->patch("/super-admin/stores/{$store->id}/status", [
            'status' => 'ACTIVE',
        ]);
        $store->refresh();
        $this->assertEquals(StoreStatus::ACTIVE, $store->status);
    }

    /**
     * TEST 13: Store Owner cannot access Store Management routes (Denied).
     */
    public function test_store_owner_cannot_access_store_management_routes(): void
    {
        $indexResponse = $this->actingAs($this->storeOwner)->get('/super-admin/stores');
        $indexResponse->assertRedirect('/super-admin/login');

        $createResponse = $this->actingAs($this->storeOwner)->get('/super-admin/stores/create');
        $createResponse->assertRedirect('/super-admin/login');

        $postResponse = $this->actingAs($this->storeOwner)->post('/super-admin/stores', [
            'name' => 'Hacked Store',
        ]);
        $postResponse->assertRedirect('/super-admin/login');
    }

    /**
     * TEST 14: Non-existing store returns 404.
     */
    public function test_non_existing_store_returns_404(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/stores/999999');
        $response->assertStatus(404);
    }

    /**
     * TEST 15: Invalid data returns validation errors.
     */
    public function test_invalid_store_creation_data_triggers_validation(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/stores', [
            'name' => '',
            'mobile' => '',
            'city' => '',
            'state' => '',
            'pincode' => '',
            'store_type' => 'InvalidType',
            'status' => 'InvalidStatus',
        ]);

        $response->assertSessionHasErrors(['name', 'mobile', 'city', 'state', 'pincode', 'store_type', 'status']);
        $this->assertEquals(0, Store::count());
    }

    /**
     * TEST 16: Sequential code generation increments accurately.
     */
    public function test_sequential_store_code_generation(): void
    {
        $code1 = Store::generateUniqueCode();
        $this->assertEquals('MED-000001', $code1);

        Store::create([
            'code' => $code1,
            'name' => 'Store One',
            'mobile' => '9876500001',
            'city' => 'City A',
            'state' => 'State A',
            'pincode' => '100001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $code2 = Store::generateUniqueCode();
        $this->assertEquals('MED-000002', $code2);
    }
}
