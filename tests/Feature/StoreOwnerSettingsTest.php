<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreOwnerSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected Store $storeB;

    protected User $ownerA;

    protected User $ownerB;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // 1. Create Store A
        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'Sharma Medical Store',
            'email' => 'sharma@medical.com',
            'mobile' => '+91 9876543210',
            'alternate_mobile' => '+91 9876543219',
            'address' => 'Shop No. 12, Main Market',
            'city' => 'Varanasi',
            'state' => 'Uttar Pradesh',
            'pincode' => '221001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
            'drug_license_no' => 'DL-UP-2026-001',
            'license_expiry_date' => Carbon::today()->addYear(),
            'gstin' => '09ABCDE1234F1Z5',
            'settings' => [
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'date_format' => 'd-m-Y',
                'invoice_prefix' => 'INV',
            ],
        ]);

        // 2. Create Store B
        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Gupta Pharmacy',
            'email' => 'gupta@pharmacy.com',
            'mobile' => '+91 9876543211',
            'address' => 'Shop No. 45, Chowk',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
            'drug_license_no' => 'DL-UP-2026-002',
            'license_expiry_date' => Carbon::today()->addMonths(6),
            'gstin' => '09ABCDE1234F2Z6',
        ]);

        // 3. Create Store Owners
        $this->ownerA = User::factory()->create([
            'name' => 'Rahul Sharma',
            'email' => 'rahul@sharmamedical.com',
            'mobile' => '9876543210',
            'password' => Hash::make('CurrentSecret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Amit Gupta',
            'email' => 'amit@guptapharmacy.com',
            'mobile' => '9876543211',
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        // 4. Create Super Admin
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
     * Test 1: Store Owner opens Store Settings -> own store information appears.
     */
    public function test_store_owner_opens_store_settings_own_store_information_appears(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get(route('store.settings.index'));

        $response->assertOk();
        $response->assertViewIs('store.settings.index');
        $response->assertSee('Sharma Medical Store');
        $response->assertSee('MED-000001');
        $response->assertSee('Varanasi');
        $response->assertSee('DL-UP-2026-001');
        $response->assertSee('09ABCDE1234F1Z5');
        $response->assertSee('Rahul Sharma');
        $response->assertSee('rahul@sharmamedical.com');
    }

    /**
     * Test 2: Edit Store Name and Type -> successful update.
     */
    public function test_edit_store_name_and_type_successful(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'name' => 'Sharma Healthcare & Surgicals',
                'store_type' => 'Retail + Wholesale',
                'mobile' => '+91 9876543210',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'pincode' => '221001',
            ]);

        $response->assertRedirect(route('store.settings.index'));
        $response->assertSessionHas('status', 'Store profile updated successfully.');

        $this->storeA->refresh();
        $this->assertEquals('Sharma Healthcare & Surgicals', $this->storeA->name);
        $this->assertEquals('Retail + Wholesale', $this->storeA->store_type);
    }

    /**
     * Test 3: Edit contact information -> successful update.
     */
    public function test_edit_contact_information_successful(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'name' => $this->storeA->name,
                'store_type' => $this->storeA->store_type,
                'email' => 'support@sharmamedical.com',
                'mobile' => '+91 9998887776',
                'alternate_mobile' => '+91 8887776665',
                'city' => $this->storeA->city,
                'state' => $this->storeA->state,
                'pincode' => $this->storeA->pincode,
            ]);

        $response->assertRedirect(route('store.settings.index'));

        $this->storeA->refresh();
        $this->assertEquals('support@sharmamedical.com', $this->storeA->email);
        $this->assertEquals('+91 9998887776', $this->storeA->mobile);
        $this->assertEquals('+91 8887776665', $this->storeA->alternate_mobile);
    }

    /**
     * Test 4: Edit address -> successful update.
     */
    public function test_edit_address_successful(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'name' => $this->storeA->name,
                'store_type' => $this->storeA->store_type,
                'mobile' => $this->storeA->mobile,
                'address' => 'Plot 99, Sigra Commercial Complex',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'pincode' => '221002',
            ]);

        $response->assertRedirect(route('store.settings.index'));

        $this->storeA->refresh();
        $this->assertEquals('Plot 99, Sigra Commercial Complex', $this->storeA->address);
        $this->assertEquals('221002', $this->storeA->pincode);
    }

    /**
     * Test 5: Edit GSTIN and license information -> successful update.
     */
    public function test_edit_gstin_and_license_information_successful(): void
    {
        $newExpiry = Carbon::today()->addYears(2)->format('Y-m-d');

        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'name' => $this->storeA->name,
                'store_type' => $this->storeA->store_type,
                'mobile' => $this->storeA->mobile,
                'city' => $this->storeA->city,
                'state' => $this->storeA->state,
                'pincode' => $this->storeA->pincode,
                'gstin' => '09ABCDE9999F1Z9',
                'drug_license_no' => 'DL-UP-2028-999',
                'license_expiry_date' => $newExpiry,
            ]);

        $response->assertRedirect(route('store.settings.index'));

        $this->storeA->refresh();
        $this->assertEquals('09ABCDE9999F1Z9', $this->storeA->gstin);
        $this->assertEquals('DL-UP-2028-999', $this->storeA->drug_license_no);
        $this->assertEquals($newExpiry, $this->storeA->license_expiry_date->format('Y-m-d'));
    }

    /**
     * Test 6: Upload valid Store Logo -> successful file storage.
     */
    public function test_upload_valid_store_logo_successful(): void
    {
        $file = UploadedFile::fake()->image('store_logo.png', 300, 300);

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.settings.update-logo'), [
                'logo' => $file,
            ]);

        $response->assertRedirect(route('store.settings.index'));
        $response->assertSessionHas('status', 'Store logo updated successfully.');

        $this->storeA->refresh();
        $this->assertNotNull($this->storeA->logo);
        Storage::disk('public')->assertExists($this->storeA->logo);
    }

    /**
     * Test 7: Invalid logo / file rejected.
     */
    public function test_invalid_logo_file_rejected(): void
    {
        // Non-image pdf file
        $file = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.settings.update-logo'), [
                'logo' => $file,
            ]);

        $response->assertSessionHasErrors(['logo']);
        $this->assertNull($this->storeA->fresh()->logo);
    }

    /**
     * Test 8: Expired license warning is displayed.
     */
    public function test_expired_license_warning_is_displayed(): void
    {
        $this->storeA->update([
            'license_expiry_date' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get(route('store.settings.index'));

        $response->assertOk();
        $response->assertSee('Drug License Has Expired!');
    }

    /**
     * Test 9: Store Owner attempts to submit another store_id -> rejected / ignored.
     */
    public function test_store_owner_cannot_modify_another_store_via_payload(): void
    {
        $originalStoreBName = $this->storeB->name;

        // Owner A submits update trying to inject Store B ID
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'store_id' => $this->storeB->id,
                'name' => 'Hacked Name Attempt',
                'store_type' => 'Retail',
                'mobile' => '+91 9876543210',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'pincode' => '221001',
            ]);

        $response->assertRedirect(route('store.settings.index'));

        // Store B must NOT be changed
        $this->assertEquals($originalStoreBName, $this->storeB->fresh()->name);
        // Store A was modified because it operates strictly on auth user's store
        $this->assertEquals('Hacked Name Attempt', $this->storeA->fresh()->name);
    }

    /**
     * Test 10: Store Owner attempts to modify Store Code -> not allowed.
     */
    public function test_store_owner_cannot_modify_store_code(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'code' => 'MED-999999',
                'name' => $this->storeA->name,
                'store_type' => 'Retail',
                'mobile' => '+91 9876543210',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'pincode' => '221001',
            ]);

        $response->assertRedirect(route('store.settings.index'));
        $this->assertEquals('MED-000001', $this->storeA->fresh()->code);
    }

    /**
     * Test 11: Store Owner attempts to modify Store Status -> not allowed.
     */
    public function test_store_owner_cannot_modify_store_status(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'status' => 'SUSPENDED',
                'name' => $this->storeA->name,
                'store_type' => 'Retail',
                'mobile' => '+91 9876543210',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'pincode' => '221001',
            ]);

        $response->assertRedirect(route('store.settings.index'));
        $this->assertEquals(StoreStatus::ACTIVE, $this->storeA->fresh()->status);
    }

    /**
     * Test 12: Store Owner attempts to modify their Role -> not allowed.
     */
    public function test_store_owner_cannot_modify_role_via_account_form(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-account'), [
                'name' => 'Rahul Sharma Admin',
                'email' => 'rahul@sharmamedical.com',
                'role' => UserRole::SUPER_ADMIN->value,
                'is_active' => true,
            ]);

        $response->assertRedirect(route('store.settings.index'));
        $this->assertEquals(UserRole::STORE_OWNER, $this->ownerA->fresh()->role);
    }

    /**
     * Test 13: Change password with wrong current password -> rejected.
     */
    public function test_change_password_with_wrong_current_password_rejected(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-password'), [
                'current_password' => 'WrongPassword123!',
                'new_password' => 'BrandNewPassword123!',
                'new_password_confirmation' => 'BrandNewPassword123!',
            ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('CurrentSecret123!', $this->ownerA->fresh()->password));
    }

    /**
     * Test 14: Change password with correct current password -> successful.
     */
    public function test_change_password_with_correct_current_password_successful(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-password'), [
                'current_password' => 'CurrentSecret123!',
                'new_password' => 'BrandNewPassword123!',
                'new_password_confirmation' => 'BrandNewPassword123!',
            ]);

        $response->assertRedirect(route('store.settings.index'));
        $response->assertSessionHas('status', 'Password changed successfully.');

        $this->assertTrue(Hash::check('BrandNewPassword123!', $this->ownerA->fresh()->password));
    }

    /**
     * Test 15: After password change, authentication still works correctly with new credentials.
     */
    public function test_after_password_change_authentication_works_with_new_password(): void
    {
        // 1. Update password
        $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-password'), [
                'current_password' => 'CurrentSecret123!',
                'new_password' => 'BrandNewPassword123!',
                'new_password_confirmation' => 'BrandNewPassword123!',
            ]);

        // 2. Logout
        $this->post(route('store.logout'));

        // 3. Login with old password fails
        $failResponse = $this->post(route('store.login.submit'), [
            'email' => 'rahul@sharmamedical.com',
            'password' => 'CurrentSecret123!',
        ]);
        $failResponse->assertSessionHasErrors();
        $this->assertGuest();

        // 4. Login with new password succeeds
        $passResponse = $this->post(route('store.login.submit'), [
            'email' => 'rahul@sharmamedical.com',
            'password' => 'BrandNewPassword123!',
        ]);
        $passResponse->assertRedirect(route('store.dashboard'));
        $this->assertAuthenticatedAs($this->ownerA);
    }

    /**
     * Test 16: Another Store Owner cannot access or modify this store's information.
     */
    public function test_cross_store_isolation_in_settings(): void
    {
        $resB = $this->actingAs($this->ownerB)->get(route('store.settings.index'));
        $resB->assertOk();
        $resB->assertSee('Gupta Pharmacy');
        $resB->assertSee('MED-000002');
        $resB->assertDontSee('Sharma Medical Store');
        $resB->assertDontSee('MED-000001');
    }

    /**
     * Test 17: Unauthenticated user cannot access Store Settings.
     */
    public function test_unauthenticated_user_cannot_access_store_settings(): void
    {
        $response = $this->get(route('store.settings.index'));
        $response->assertRedirect(route('store.login'));
    }

    /**
     * Test 18: Store logo removal works successfully.
     */
    public function test_store_logo_removal_successful(): void
    {
        // 1. Upload logo
        $file = UploadedFile::fake()->image('logo.png');
        $this->actingAs($this->ownerA)->post(route('store.settings.update-logo'), ['logo' => $file]);

        $logoPath = $this->storeA->fresh()->logo;
        $this->assertNotNull($logoPath);
        Storage::disk('public')->assertExists($logoPath);

        // 2. Remove logo
        $response = $this->actingAs($this->ownerA)->delete(route('store.settings.remove-logo'));
        $response->assertRedirect(route('store.settings.index'));
        $response->assertSessionHas('status', 'Store logo removed successfully.');

        $this->assertNull($this->storeA->fresh()->logo);
        Storage::disk('public')->assertMissing($logoPath);
    }

    /**
     * Test 19: Store preferences (timezone, currency, date format, invoice prefix) update properly.
     */
    public function test_store_preferences_update_properly(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->put(route('store.settings.update-profile'), [
                'name' => $this->storeA->name,
                'store_type' => $this->storeA->store_type,
                'mobile' => $this->storeA->mobile,
                'city' => $this->storeA->city,
                'state' => $this->storeA->state,
                'pincode' => $this->storeA->pincode,
                'timezone' => 'UTC',
                'currency' => 'USD',
                'date_format' => 'Y-m-d',
                'invoice_prefix' => 'MEDINV',
            ]);

        $response->assertRedirect(route('store.settings.index'));

        $this->storeA->refresh();
        $this->assertEquals('UTC', $this->storeA->timezone());
        $this->assertEquals('USD', $this->storeA->currency());
        $this->assertEquals('Y-m-d', $this->storeA->dateFormat());
        $this->assertEquals('MEDINV', $this->storeA->invoicePrefix());
    }
}
