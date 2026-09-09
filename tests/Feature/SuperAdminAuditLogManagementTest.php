<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SuperAdminAuditLogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected Store $store;

    protected SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('12345678'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->store = Store::create([
            'code' => 'MED-000001',
            'name' => 'Metro Care Pharmacy',
            'email' => 'metro@care.com',
            'mobile' => '+91 9876543210',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'address' => '12 Central Market',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->storeOwner = User::factory()->create([
            'name' => 'John Pharmacist',
            'email' => 'owner@example.com',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Growth Plan',
            'slug' => 'growth-plan',
            'price' => 2999,
            'billing_cycle' => 'monthly',
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    public function test_super_admin_can_view_audit_logs_page(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('System Audit Logs');
        $response->assertSee('Total Recorded Events');
    }

    public function test_store_owner_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.audit-logs.index'));

        $response->assertRedirect(route('super-admin.login'));
        $response->assertSessionHasErrors(['email']);
    }

    public function test_guest_cannot_access_audit_logs(): void
    {
        $response = $this->get(route('super-admin.audit-logs.index'));

        $response->assertRedirect(route('super-admin.login'));
    }

    public function test_store_creation_creates_audit_log(): void
    {
        $payload = [
            'name' => 'Sunrise Medical Store',
            'email' => 'sunrise@med.com',
            'mobile' => '+91 9123456780',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE->value,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.stores.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::STORES->value,
            'action' => AuditAction::CREATED->value,
        ]);

        $log = AuditLog::where('module', AuditModule::STORES)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Sunrise Medical Store', $log->description);
    }

    public function test_store_update_and_status_change_creates_audit_log(): void
    {
        // 1. Update store details
        $updatePayload = [
            'name' => 'Metro Care Pharmacy Updated',
            'email' => 'metro@care.com',
            'mobile' => '+91 9876543210',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE->value,
        ];

        $this->actingAs($this->superAdmin)
            ->put(route('super-admin.stores.update', $this->store), $updatePayload);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::STORES->value,
            'action' => AuditAction::UPDATED->value,
        ]);

        // 2. Suspend store
        $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.stores.update-status', $this->store), [
                'status' => StoreStatus::SUSPENDED->value,
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::STORES->value,
            'action' => AuditAction::SUSPENDED->value,
        ]);
    }

    public function test_store_owner_creation_creates_audit_log_and_redacts_password(): void
    {
        // Create store without active owner
        $newStore = Store::create([
            'code' => 'MED-000099',
            'name' => 'New Alpha Pharmacy',
            'email' => 'alpha@med.com',
            'mobile' => '+91 9876500000',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $payload = [
            'name' => 'Alice Chemist',
            'email' => 'alice@chemist.com',
            'mobile' => '+91 9998887776',
            'password' => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
            'store_id' => $newStore->id,
            'is_active' => '1',
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.store-owners.store'), $payload);

        $log = AuditLog::where('module', AuditModule::STORE_OWNERS)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals(AuditAction::CREATED, $log->action);

        // Verify password is NOT saved in new_values
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        $this->assertArrayNotHasKey('password_confirmation', $log->new_values ?? []);
    }

    public function test_subscription_plan_creation_and_update_creates_audit_log(): void
    {
        $payload = [
            'name' => 'Enterprise Health Plan',
            'price' => 9999,
            'billing_cycle' => 'yearly',
            'trial_days' => 30,
            'status' => 'active',
            'limits' => [
                'max_staff' => 20,
                'max_medicines' => 50000,
                'max_invoices' => 50000,
                'max_customers' => 50000,
            ],
            'features' => ['inventory_management', 'pos'],
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.subscriptions.plans.store'), $payload);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::SUBSCRIPTION_PLANS->value,
            'action' => AuditAction::CREATED->value,
        ]);
    }

    public function test_subscription_assignment_creates_audit_log(): void
    {
        $payload = [
            'store_id' => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addMonth()->toDateString(),
            'status' => 'active',
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.subscriptions.stores.store'), $payload);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::STORE_SUBSCRIPTIONS->value,
            'action' => AuditAction::ASSIGNED->value,
        ]);
    }

    public function test_payment_recording_and_status_update_creates_audit_log(): void
    {
        $payload = [
            'store_id' => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 2999,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI->value,
            'transaction_id' => 'TXN-99887766',
            'payment_date' => Carbon::today()->toDateString(),
            'status' => PaymentStatus::PAID->value,
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.payments.store'), $payload);

        $payment = Payment::where('transaction_id', 'TXN-99887766')->first();
        $this->assertNotNull($payment);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::PAYMENTS->value,
            'action' => AuditAction::CREATED->value,
        ]);

        // Status change
        $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.payments.update-status', $payment), [
                'status' => PaymentStatus::REFUNDED->value,
                'notes' => 'Customer requested refund.',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::PAYMENTS->value,
            'action' => AuditAction::STATUS_CHANGED->value,
        ]);
    }

    public function test_notification_creation_and_cancellation_creates_audit_log(): void
    {
        $payload = [
            'title' => 'Scheduled Maintenance Notice',
            'message' => 'Servers will undergo maintenance tonight.',
            'type' => 'maintenance',
            'priority' => 'important',
            'target_type' => 'all_stores',
            'delivery_mode' => 'schedule',
            'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.store'), $payload);

        $notification = Notification::latest('id')->first();
        $this->assertNotNull($notification);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::NOTIFICATIONS->value,
            'action' => AuditAction::CREATED->value,
        ]);

        // Cancel notification
        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.cancel', $notification));

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::NOTIFICATIONS->value,
            'action' => AuditAction::CANCELLED->value,
        ]);
    }

    public function test_settings_update_creates_audit_log(): void
    {
        $payload = [
            'group' => 'general',
            'app_name' => 'NEW MEDISTORE SUITE',
            'default_timezone' => 'Asia/Kolkata',
            'default_currency' => 'INR',
            'currency_symbol' => '₹',
            'date_format' => 'Y-m-d',
            'time_format' => 'h:i A',
        ];

        $this->actingAs($this->superAdmin)
            ->put(route('super-admin.settings.update'), $payload);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::SETTINGS->value,
            'action' => AuditAction::SETTINGS_UPDATED->value,
        ]);
    }

    public function test_super_admin_login_and_logout_creates_audit_log(): void
    {
        // Login
        $this->post(route('super-admin.login.submit'), [
            'email' => 'superadmin@gmail.com',
            'password' => '12345678',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::AUTH->value,
            'action' => AuditAction::LOGIN->value,
        ]);

        // Logout
        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.logout'));

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditModule::AUTH->value,
            'action' => AuditAction::LOGOUT->value,
        ]);
    }

    public function test_audit_log_search_and_filters(): void
    {
        AuditLogger::log(AuditAction::CREATED, AuditModule::STORES, 'Special Keyword Alpha Search', $this->store);
        AuditLogger::log(AuditAction::UPDATED, AuditModule::PAYMENTS, 'Special Keyword Beta Search');

        // Search Keyword
        $searchResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.audit-logs.index', ['search' => 'Alpha']));

        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Special Keyword Alpha Search');
        $searchResponse->assertDontSee('Special Keyword Beta Search');

        // Module filter
        $moduleResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.audit-logs.index', ['module' => 'payments']));

        $moduleResponse->assertStatus(200);
        $moduleResponse->assertSee('Special Keyword Beta Search');
        $moduleResponse->assertDontSee('Special Keyword Alpha Search');
    }

    public function test_audit_log_details_view_renders_diffs_and_telemetry(): void
    {
        $log = AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORES,
            'Updated store name',
            $this->store,
            ['name' => 'Old Store Name'],
            ['name' => 'New Store Name']
        );

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.audit-logs.show', $log));

        $response->assertStatus(200);
        $response->assertSee('Event #'.$log->id);
        $response->assertSee('Old Store Name');
        $response->assertSee('New Store Name');
        $response->assertSee('Actor Information');
        $response->assertSee('Client Telemetry');
    }

    public function test_audit_logging_disabled_setting_skips_recording(): void
    {
        setting(['enable_audit_logging' => '0']);

        $initialCount = AuditLog::count();

        AuditLogger::log(AuditAction::CREATED, AuditModule::STORES, 'Should not be recorded');

        $this->assertEquals($initialCount, AuditLog::count());
    }
}
