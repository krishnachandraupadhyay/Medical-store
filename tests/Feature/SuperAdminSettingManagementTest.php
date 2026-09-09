<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->storeOwner = User::factory()->create([
            'email' => 'owner@example.com',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System Settings');
        $response->assertSee('General');
        $response->assertSee('Branding');
        $response->assertSee('Contact Info');
    }

    public function test_store_owner_cannot_view_system_settings(): void
    {
        $response = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.settings.index'));

        $response->assertRedirect(route('super-admin.login'));
        $response->assertSessionHasErrors(['email']);
    }

    public function test_guest_is_redirected_from_system_settings(): void
    {
        $response = $this->get(route('super-admin.settings.index'));

        $response->assertRedirect(route('super-admin.login'));
    }

    public function test_super_admin_can_update_general_settings(): void
    {
        $payload = [
            'group' => 'general',
            'app_name' => 'MEDISTORE PRO',
            'app_tagline' => 'Enterprise Healthcare SaaS',
            'default_timezone' => 'Asia/Kolkata',
            'default_currency' => 'INR',
            'currency_symbol' => '₹',
            'date_format' => 'd-m-Y',
            'time_format' => 'H:i',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'general']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'MEDISTORE PRO',
        ]);

        $this->assertEquals('MEDISTORE PRO', setting('app_name'));
        $this->assertEquals('Enterprise Healthcare SaaS', setting('app_tagline'));
    }

    public function test_general_settings_validation_errors(): void
    {
        $payload = [
            'group' => 'general',
            'app_name' => '', // Required
            'default_timezone' => 'Invalid/Timezone',
            'default_currency' => 'TOOLONG',
            'currency_symbol' => '',
            'date_format' => '',
            'time_format' => '',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertSessionHasErrors(['app_name', 'default_timezone', 'default_currency', 'currency_symbol', 'date_format', 'time_format']);
    }

    public function test_super_admin_can_upload_branding_assets(): void
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->image('logo.png', 200, 60);
        $loginLogo = UploadedFile::fake()->image('login_logo.jpg', 300, 100);

        $payload = [
            'group' => 'branding',
            'app_logo' => $logoFile,
            'login_logo' => $loginLogo,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'branding']));
        $response->assertSessionHas('success');

        $appLogoPath = setting('app_logo');
        $this->assertNotNull($appLogoPath);
        Storage::disk('public')->assertExists($appLogoPath);

        $loginLogoPath = setting('login_logo');
        $this->assertNotNull($loginLogoPath);
        Storage::disk('public')->assertExists($loginLogoPath);
    }

    public function test_invalid_branding_upload_is_rejected(): void
    {
        Storage::fake('public');

        $invalidFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $payload = [
            'group' => 'branding',
            'app_logo' => $invalidFile,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertSessionHasErrors(['app_logo']);
    }

    public function test_super_admin_can_update_contact_settings(): void
    {
        $payload = [
            'group' => 'contact',
            'company_name' => 'Apex Healthcare Cloud Inc.',
            'company_website' => 'https://apexcloud.health',
            'support_email' => 'helpdesk@apexcloud.health',
            'support_phone' => '+91 9988776655',
            'company_address' => 'Floor 9, Tech Tower 3, Cyber City',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'contact']));
        $response->assertSessionHas('success');

        $this->assertEquals('Apex Healthcare Cloud Inc.', setting('company_name'));
        $this->assertEquals('helpdesk@apexcloud.health', setting('support_email'));
    }

    public function test_super_admin_can_update_system_and_maintenance_settings(): void
    {
        $payload = [
            'group' => 'system',
            'maintenance_mode' => '1',
            'maintenance_message' => 'System under scheduled database optimization.',
            'enable_store_registration' => '0',
            'enable_store_owner_creation' => '1',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'system']));
        $response->assertSessionHas('success');

        $this->assertTrue(setting('maintenance_mode'));
        $this->assertEquals('System under scheduled database optimization.', setting('maintenance_message'));
        $this->assertFalse(setting('enable_store_registration'));
        $this->assertTrue(setting('enable_store_owner_creation'));
    }

    public function test_maintenance_mode_middleware_blocks_tenants_and_allows_super_admin(): void
    {
        // Enable maintenance mode
        setting(['maintenance_mode' => '1', 'maintenance_message' => 'Upgrades in progress']);

        // 1. Regular store owner trying to access protected page
        $ownerResponse = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.dashboard'));

        // Middleware prevents access (503 Service Unavailable or 403)
        $ownerResponse->assertStatus(503);
        $ownerResponse->assertSee('Upgrades in progress');

        // 2. Super Admin can still access
        $adminResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.dashboard'));

        $adminResponse->assertStatus(200);

        // 3. Super Admin login page remains accessible to guests
        auth()->logout();
        $loginResponse = $this->get(route('super-admin.login'));
        $loginResponse->assertStatus(200);
    }

    public function test_super_admin_can_update_security_settings(): void
    {
        $payload = [
            'group' => 'security',
            'session_timeout' => 60,
            'password_min_length' => 10,
            'require_strong_password' => '1',
            'login_attempt_protection' => '1',
            'enable_audit_logging' => '1',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'security']));
        $response->assertSessionHas('success');

        $this->assertEquals(60, setting('session_timeout'));
        $this->assertEquals(10, setting('password_min_length'));
        $this->assertTrue(setting('require_strong_password'));
    }

    public function test_super_admin_can_update_notification_settings(): void
    {
        $payload = [
            'group' => 'notification',
            'enable_in_app_notifications' => '1',
            'enable_email_notifications' => '1',
            'enable_sms_notifications' => '0',
            'enable_whatsapp_notifications' => '0',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'notification']));
        $response->assertSessionHas('success');

        $this->assertTrue(setting('enable_in_app_notifications'));
        $this->assertTrue(setting('enable_email_notifications'));
        $this->assertFalse(setting('enable_sms_notifications'));
    }

    public function test_super_admin_can_update_subscription_settings(): void
    {
        $payload = [
            'group' => 'subscription',
            'default_trial_days' => 30,
            'allow_trial' => '1',
            'allow_new_subscriptions' => '1',
            'subscription_expiry_warning_days' => 10,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $response->assertRedirect(route('super-admin.settings.index', ['tab' => 'subscription']));
        $response->assertSessionHas('success');

        $this->assertEquals(30, setting('default_trial_days'));
        $this->assertTrue(setting('allow_trial'));
        $this->assertEquals(10, setting('subscription_expiry_warning_days'));
    }

    public function test_settings_cache_clears_on_update(): void
    {
        // Cache initial value
        $this->assertEquals('MEDISTORE', setting('app_name'));

        // Update value directly via helper
        setting(['app_name' => 'CACHED_UPDATED_NAME']);

        // Should return new value immediately
        $this->assertEquals('CACHED_UPDATED_NAME', setting('app_name'));
    }
}
