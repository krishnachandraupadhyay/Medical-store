<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected Store $store;

    protected SubscriptionPlan $plan;

    protected Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        // Super Admin
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Store & Owner
        $this->store = Store::create([
            'code' => 'MED-000001',
            'name' => 'Metro Care Pharmacy',
            'email' => 'metro@care.com',
            'mobile' => '+91 9876543210',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->storeOwner = User::factory()->create([
            'name' => 'Amit Kumar',
            'email' => 'amit@metrocare.com',
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        // Subscription Plan
        $this->plan = SubscriptionPlan::create([
            'name' => 'Pro Tier',
            'slug' => 'pro-tier',
            'price' => 1999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 14,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_staff' => 10, 'max_medicines' => 2000],
            'features' => ['inventory_management', 'sales_management', 'pos'],
            'sort_order' => 1,
        ]);

        // Subscription
        $this->subscription = Subscription::create([
            'store_id' => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
            'status' => SubscriptionStatus::ACTIVE,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    public function test_super_admin_can_view_payments_list(): void
    {
        Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI,
            'transaction_id' => 'TXN-20260909-0001',
            'payment_date' => now(),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.payments.index'));

        $response->assertOk();
        $response->assertViewIs('super-admin.payments.index');
        $response->assertSee('Payment Management');
        $response->assertSee('TXN-20260909-0001');
        $response->assertSee('Metro Care Pharmacy');
        $response->assertSee('1,999.00');
    }

    public function test_store_owner_cannot_access_payment_management(): void
    {
        $response = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.payments.index'));

        $response->assertRedirect(route('super-admin.login'));

        $responseCreate = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.payments.create'));

        $responseCreate->assertRedirect(route('super-admin.login'));

        $responseJson = $this->actingAs($this->storeOwner)
            ->getJson(route('super-admin.payments.index'));

        $responseJson->assertStatus(403);
    }

    public function test_guest_cannot_access_payment_management(): void
    {
        $response = $this->get(route('super-admin.payments.index'));
        $response->assertRedirect(route('super-admin.login'));
    }

    public function test_super_admin_can_record_manual_payment(): void
    {
        $paymentData = [
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => '1999.00',
            'currency' => 'INR',
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'transaction_id' => 'TXN-TEST-123456',
            'payment_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => PaymentStatus::PAID->value,
            'notes' => 'Received via NEFT reference #981273981.',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.payments.store'), $paymentData);

        $this->assertDatabaseHas('payments', [
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'transaction_id' => 'TXN-TEST-123456',
            'amount' => 1999.00,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'status' => PaymentStatus::PAID->value,
            'created_by' => $this->superAdmin->id,
        ]);

        $createdPayment = Payment::where('transaction_id', 'TXN-TEST-123456')->first();
        $response->assertRedirect(route('super-admin.payments.show', $createdPayment));
        $response->assertSessionHas('success');
    }

    public function test_payment_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.payments.store'), []);

        $response->assertSessionHasErrors([
            'store_id',
            'amount',
            'currency',
            'payment_method',
            'transaction_id',
            'payment_date',
            'status',
        ]);
    }

    public function test_duplicate_transaction_id_is_rejected(): void
    {
        Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'amount' => 500.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::CASH,
            'transaction_id' => 'TXN-DUP-0001',
            'payment_date' => now(),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.payments.store'), [
                'store_id' => $this->store->id,
                'subscription_id' => $this->subscription->id,
                'amount' => 750.00,
                'currency' => 'INR',
                'payment_method' => PaymentMethod::CASH->value,
                'transaction_id' => 'TXN-DUP-0001', // duplicate
                'payment_date' => now()->format('Y-m-d H:i:s'),
                'status' => PaymentStatus::PAID->value,
            ]);

        $response->assertSessionHasErrors(['transaction_id']);
    }

    public function test_super_admin_can_view_payment_details(): void
    {
        $payment = Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::CARD,
            'transaction_id' => 'TXN-VIEW-0001',
            'payment_date' => now(),
            'status' => PaymentStatus::PAID,
            'notes' => 'Card swiped at POS terminal.',
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.payments.show', $payment));

        $response->assertOk();
        $response->assertViewIs('super-admin.payments.show');
        $response->assertSee('TXN-VIEW-0001');
        $response->assertSee('Metro Care Pharmacy');
        $response->assertSee('Pro Tier');
        $response->assertSee('Card (Credit/Debit)');
        $response->assertSee('Card swiped at POS terminal.');
    }

    public function test_super_admin_can_update_payment(): void
    {
        $payment = Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1000.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::CASH,
            'transaction_id' => 'TXN-EDIT-0001',
            'payment_date' => now(),
            'status' => PaymentStatus::PENDING,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.payments.update', $payment), [
                'transaction_id' => 'TXN-EDIT-0001',
                'amount' => '1200.00',
                'payment_method' => PaymentMethod::UPI->value,
                'payment_date' => now()->format('Y-m-d H:i:s'),
                'status' => PaymentStatus::PAID->value,
                'notes' => 'Updated notes after customer verification.',
            ]);

        $response->assertRedirect(route('super-admin.payments.show', $payment));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => 1200.00,
            'payment_method' => PaymentMethod::UPI->value,
            'status' => PaymentStatus::PAID->value,
            'notes' => 'Updated notes after customer verification.',
            'updated_by' => $this->superAdmin->id,
        ]);
    }

    public function test_super_admin_can_update_payment_status_via_patch(): void
    {
        $payment = Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::ONLINE,
            'transaction_id' => 'TXN-STATUS-0001',
            'payment_date' => now(),
            'status' => PaymentStatus::PENDING,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.payments.update-status', $payment), [
                'status' => PaymentStatus::REFUNDED->value,
                'notes' => 'Customer requested refund.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::REFUNDED->value,
            'updated_by' => $this->superAdmin->id,
        ]);
    }

    public function test_payment_index_search_and_filters(): void
    {
        $store2 = Store::create([
            'code' => 'MED-000002',
            'name' => 'Zenith Health Store',
            'email' => 'zenith@care.com',
            'mobile' => '+91 9123456780',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'pincode' => '560001',
            'store_type' => 'Hospital',
            'status' => StoreStatus::ACTIVE,
        ]);

        Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI,
            'transaction_id' => 'TXN-METRO-001',
            'payment_date' => Carbon::parse('2026-06-01 10:00:00'),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        Payment::create([
            'store_id' => $store2->id,
            'amount' => 5000.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::CASH,
            'transaction_id' => 'TXN-ZENITH-002',
            'payment_date' => Carbon::parse('2026-06-15 12:00:00'),
            'status' => PaymentStatus::PENDING,
            'created_by' => $this->superAdmin->id,
        ]);

        // Search by TXN ID
        $searchResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.payments.index', ['search' => 'TXN-ZENITH']));
        $searchResponse->assertSee('TXN-ZENITH-002');
        $searchResponse->assertDontSee('TXN-METRO-001');

        // Filter by Status
        $statusResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.payments.index', ['status' => PaymentStatus::PENDING->value]));
        $statusResponse->assertSee('TXN-ZENITH-002');
        $statusResponse->assertDontSee('TXN-METRO-001');

        // Filter by Payment Method
        $methodResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.payments.index', ['payment_method' => PaymentMethod::UPI->value]));
        $methodResponse->assertSee('TXN-METRO-001');
        $methodResponse->assertDontSee('TXN-ZENITH-002');

        // Filter by Date Range
        $dateResponse = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.payments.index', ['date_from' => '2026-06-10', 'date_to' => '2026-06-20']));
        $dateResponse->assertSee('TXN-ZENITH-002');
        $dateResponse->assertDontSee('TXN-METRO-001');
    }

    public function test_dashboard_displays_real_payment_revenue(): void
    {
        Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 2500.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI,
            'transaction_id' => 'TXN-DASH-001',
            'payment_date' => now(),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1500.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::CASH,
            'transaction_id' => 'TXN-DASH-002',
            'payment_date' => now(),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
        ]);

        // Failed payment should NOT be included in revenue
        Payment::create([
            'store_id' => $this->store->id,
            'amount' => 9999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::ONLINE,
            'transaction_id' => 'TXN-DASH-003',
            'payment_date' => now(),
            'status' => PaymentStatus::FAILED,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.dashboard'));

        $response->assertOk();
        // Total Paid Revenue = 2500 + 1500 = 4,000
        $response->assertSee('₹4,000');
    }

    public function test_payment_relationships_and_transaction_id_generator(): void
    {
        $txnId1 = Payment::generateTransactionId();
        $this->assertStringStartsWith('TXN-'.date('Ymd').'-', $txnId1);

        $payment = Payment::create([
            'store_id' => $this->store->id,
            'subscription_id' => $this->subscription->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 1999.00,
            'currency' => 'INR',
            'payment_method' => PaymentMethod::UPI,
            'transaction_id' => $txnId1,
            'payment_date' => now(),
            'status' => PaymentStatus::PAID,
            'created_by' => $this->superAdmin->id,
            'updated_by' => $this->superAdmin->id,
        ]);

        // Test BelongsTo relationships
        $this->assertEquals($this->store->id, $payment->store->id);
        $this->assertEquals($this->subscription->id, $payment->subscription->id);
        $this->assertEquals($this->plan->id, $payment->subscriptionPlan->id);
        $this->assertEquals($this->superAdmin->id, $payment->creator->id);
        $this->assertEquals($this->superAdmin->id, $payment->updater->id);

        // Test HasMany relationships
        $this->assertTrue($this->store->payments->contains($payment));
        $this->assertTrue($this->subscription->payments->contains($payment));
        $this->assertTrue($this->plan->payments->contains($payment));

        // Next sequential ID should increment
        $txnId2 = Payment::generateTransactionId();
        $this->assertNotEquals($txnId1, $txnId2);
    }
}
