<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\RbacService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreCustomerMasterTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    private Store $otherStore;

    private User $owner;

    private User $otherOwner;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        app(RbacService::class)->seedDefaultSystemRoles();

        $this->plan = SubscriptionPlan::create([
            'name' => 'Customer Master Plan',
            'slug' => 'customer-master-plan',
            'price' => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_customers' => 999],
            'features' => [
                'customer_management',
                'supplier_management',
                'sales_management',
                'pos',
            ],
        ]);

        $this->store = Store::create([
            'code' => 'STR-CUST01',
            'name' => 'CarePlus Pharmacy',
            'email' => 'careplus@medstore.test',
            'mobile' => '9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->owner = User::factory()->create([
            'store_id' => $this->store->id,
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'password' => Hash::make('Password123!'),
        ]);

        Subscription::create([
            'store_id' => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $this->otherStore = Store::create([
            'code' => 'STR-CUST02',
            'name' => 'Apex Medicos',
            'email' => 'apex@medstore.test',
            'mobile' => '9876543211',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->otherOwner = User::factory()->create([
            'store_id' => $this->otherStore->id,
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'password' => Hash::make('Password123!'),
        ]);

        Subscription::create([
            'store_id' => $this->otherStore->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);
    }

    private function createCustomer(Store $store, array $attrs = []): Customer
    {
        return Customer::create(array_merge([
            'store_id' => $store->id,
            'customer_code' => 'CUS-'.strtoupper(substr(uniqid(), -6)),
            'name' => 'John Doe',
            'phone' => '9876540000',
            'status' => 'active',
        ], $attrs));
    }

    public function test_customer_can_be_created_with_auto_generated_code_and_doctor_and_tax_info(): void
    {
        $response = $this->actingAs($this->owner)->post(route('store.customers.store'), [
            'name' => 'Rajesh Sharma',
            'phone' => '9820011223',
            'alternate_phone' => '9820011224',
            'email' => 'rajesh@example.com',
            'gender' => 'male',
            'blood_group' => 'B+',
            'date_of_birth' => '1985-06-15',
            'emergency_contact_name' => 'Sunita Sharma',
            'emergency_contact_phone' => '9820011225',
            'doctor_name' => 'Dr. A. K. Verma',
            'tax_number' => '27AAAAA0000A1Z5',
            'address' => 'Flat 402, Sunshine Heights',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400050',
            'notes' => 'Chronic diabetic patient',
            'tags' => ['diabetic', 'vip'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'store_id' => $this->store->id,
            'name' => 'Rajesh Sharma',
            'phone' => '9820011223',
            'alternate_phone' => '9820011224',
            'blood_group' => 'B+',
            'emergency_contact_name' => 'Sunita Sharma',
            'emergency_contact_phone' => '9820011225',
            'doctor_name' => 'Dr. A. K. Verma',
            'tax_number' => '27AAAAA0000A1Z5',
            'gender' => 'male',
            'status' => 'active',
            'pincode' => '400050',
        ]);

        $customer = Customer::where('phone', '9820011223')->first();
        $this->assertNotNull($customer->customer_code);
        $this->assertStringStartsWith('CUS-', $customer->customer_code);
    }

    public function test_customer_index_displays_kpi_metrics_and_applies_status_and_due_filters(): void
    {
        $c1 = $this->createCustomer($this->store, ['name' => 'Active Due Customer', 'status' => 'active', 'phone' => '9800000001']);
        $c2 = $this->createCustomer($this->store, ['name' => 'Active Clear Customer', 'status' => 'active', 'phone' => '9800000002']);
        $c3 = $this->createCustomer($this->store, ['name' => 'Inactive Customer', 'status' => 'inactive', 'phone' => '9800000003']);

        // Create sale with outstanding for c1
        Sale::create([
            'store_id' => $this->store->id,
            'customer_id' => $c1->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-DUE-001',
            'subtotal' => 1250.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'grand_total' => 1250.00,
            'paid_amount' => 0.00,
            'due_amount' => 1250.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'sale_date' => now(),
        ]);

        // Create sale with outstanding for c3
        Sale::create([
            'store_id' => $this->store->id,
            'customer_id' => $c3->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-DUE-002',
            'subtotal' => 300.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'grand_total' => 300.00,
            'paid_amount' => 0.00,
            'due_amount' => 300.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'sale_date' => now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('store.customers.index'));
        $response->assertOk();
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 3 &&
                   $metrics['active'] === 2 &&
                   $metrics['inactive'] === 1 &&
                   $metrics['has_due_count'] === 2 &&
                   $metrics['total_outstanding'] == 1550.00;
        });

        // Filter by inactive
        $inactiveResponse = $this->actingAs($this->owner)->get(route('store.customers.index', ['status' => 'inactive']));
        $inactiveResponse->assertOk();
        $inactiveResponse->assertSee('Inactive Customer');
        $inactiveResponse->assertDontSee('Active Clear Customer');

        // Filter by due balance
        $dueResponse = $this->actingAs($this->owner)->get(route('store.customers.index', ['due' => 'has_due']));
        $dueResponse->assertOk();
        $dueResponse->assertSee('Active Due Customer');
        $dueResponse->assertSee('Inactive Customer');
        $dueResponse->assertDontSee('Active Clear Customer');
    }

    public function test_customer_search_works_by_doctor_name_and_alternate_phone(): void
    {
        $this->createCustomer($this->store, [
            'name' => 'Vikram Malhotra',
            'phone' => '9811122233',
            'alternate_phone' => '9899988877',
            'doctor_name' => 'Dr. Batra Clinic',
        ]);

        $this->createCustomer($this->store, [
            'name' => 'Sunita Gupta',
            'phone' => '9833344455',
            'alternate_phone' => '9877766655',
            'doctor_name' => 'Dr. Nair Ortho',
        ]);

        // Search by doctor name
        $searchDoc = $this->actingAs($this->owner)->get(route('store.customers.index', ['search' => 'Batra']));
        $searchDoc->assertOk();
        $searchDoc->assertSee('Vikram Malhotra');
        $searchDoc->assertDontSee('Sunita Gupta');

        // Search by alternate phone
        $searchAltPhone = $this->actingAs($this->owner)->get(route('store.customers.index', ['search' => '9877766655']));
        $searchAltPhone->assertOk();
        $searchAltPhone->assertSee('Sunita Gupta');
        $searchAltPhone->assertDontSee('Vikram Malhotra');
    }

    public function test_customer_status_can_be_toggled(): void
    {
        $customer = $this->createCustomer($this->store, ['status' => 'active']);

        $response = $this->actingAs($this->owner)->patch(route('store.customers.toggle-status', $customer));
        $response->assertRedirect();
        $this->assertEquals('inactive', $customer->fresh()->status);

        $response2 = $this->actingAs($this->owner)->patch(route('store.customers.toggle-status', $customer));
        $response2->assertRedirect();
        $this->assertEquals('active', $customer->fresh()->status);
    }

    public function test_customer_can_be_updated(): void
    {
        $customer = $this->createCustomer($this->store, [
            'name' => 'Original Name',
            'doctor_name' => 'Original Doctor',
        ]);

        $response = $this->actingAs($this->owner)->put(route('store.customers.update', $customer), [
            'name' => 'Updated Customer Name',
            'phone' => $customer->phone,
            'doctor_name' => 'Dr. Updated Speciality',
            'tax_number' => '27BBBBB1111B2Z6',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('store.customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer Name',
            'doctor_name' => 'Dr. Updated Speciality',
            'tax_number' => '27BBBBB1111B2Z6',
        ]);
    }

    public function test_safe_delete_prevents_deletion_if_customer_has_sales_history(): void
    {
        $customer = $this->createCustomer($this->store);

        // Create a sale for this customer
        Sale::create([
            'store_id' => $this->store->id,
            'customer_id' => $customer->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-TEST-001',
            'subtotal' => 500.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'grand_total' => 500.00,
            'paid_amount' => 500.00,
            'due_amount' => 0.00,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $response = $this->actingAs($this->owner)->delete(route('store.customers.destroy', $customer));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }

    public function test_safe_delete_deletes_customer_without_sales(): void
    {
        $customer = $this->createCustomer($this->store);

        $response = $this->actingAs($this->owner)->delete(route('store.customers.destroy', $customer));
        $response->assertRedirect(route('store.customers.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_tenant_isolation_is_strictly_enforced(): void
    {
        $storeBCustomer = $this->createCustomer($this->otherStore, [
            'name' => 'Store B Customer Only',
            'phone' => '9999911111',
        ]);

        // Store A cannot see Store B customer on index
        $indexResponse = $this->actingAs($this->owner)->get(route('store.customers.index'));
        $indexResponse->assertDontSee('Store B Customer Only');

        // Store A cannot view Store B customer
        $showResponse = $this->actingAs($this->owner)->get(route('store.customers.show', $storeBCustomer));
        $showResponse->assertNotFound();

        // Store A cannot edit Store B customer
        $editResponse = $this->actingAs($this->owner)->get(route('store.customers.edit', $storeBCustomer));
        $editResponse->assertNotFound();

        // Store A cannot toggle status of Store B customer
        $toggleResponse = $this->actingAs($this->owner)->patch(route('store.customers.toggle-status', $storeBCustomer));
        $toggleResponse->assertNotFound();

        // Store A cannot delete Store B customer
        $deleteResponse = $this->actingAs($this->owner)->delete(route('store.customers.destroy', $storeBCustomer));
        $deleteResponse->assertNotFound();
    }

    public function test_csv_export_streams_customer_data_properly(): void
    {
        $this->createCustomer($this->store, [
            'customer_code' => 'CUS-EXP001',
            'name' => 'Export Test Customer',
            'phone' => '9800077777',
            'doctor_name' => 'Dr. Export MD',
            'tax_number' => '27ABCDE1234F1Z5',
        ]);

        $response = $this->actingAs($this->owner)->get(route('store.customers.export'));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        // Check streamed content
        $content = $response->streamedContent();
        $this->assertStringContainsString('Customer Code', $content);
        $this->assertStringContainsString('CUS-EXP001', $content);
        $this->assertStringContainsString('Export Test Customer', $content);
        $this->assertStringContainsString('Dr. Export MD', $content);
        $this->assertStringContainsString('27ABCDE1234F1Z5', $content);
        $this->assertStringContainsString('Blood Group', $content);
        $this->assertStringContainsString('Emergency Contact Name', $content);
    }

    public function test_phone_number_validation_and_store_scoped_duplicate_prevention(): void
    {
        // 1. Phone number is required
        $reqResponse = $this->actingAs($this->owner)->post(route('store.customers.store'), [
            'name' => 'No Phone Customer',
        ]);
        $reqResponse->assertSessionHasErrors('phone');

        // 2. Reject invalid phone number (less than 10 digits or non-mobile)
        $invalidResponse = $this->actingAs($this->owner)->post(route('store.customers.store'), [
            'name' => 'Invalid Phone Customer',
            'phone' => '12345',
        ]);
        $invalidResponse->assertSessionHasErrors('phone');

        // 3. Register valid customer
        $validResponse = $this->actingAs($this->owner)->post(route('store.customers.store'), [
            'name' => 'Original Customer',
            'phone' => '9876543210',
        ]);
        $validResponse->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'store_id' => $this->store->id,
            'phone' => '9876543210',
        ]);

        // 4. Duplicate phone within same store is rejected
        $dupResponse = $this->actingAs($this->owner)->post(route('store.customers.store'), [
            'name' => 'Duplicate Phone Customer',
            'phone' => '9876543210',
        ]);
        $dupResponse->assertSessionHasErrors('phone');

        // 5. Same phone number in another store is accepted (tenant-scoped)
        $crossResponse = $this->actingAs($this->otherOwner)->post(route('store.customers.store'), [
            'name' => 'Other Store Same Phone Customer',
            'phone' => '9876543210',
        ]);
        $crossResponse->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'store_id' => $this->otherStore->id,
            'phone' => '9876543210',
        ]);
    }

    public function test_customers_can_be_filtered_by_blood_group_and_city(): void
    {
        $this->createCustomer($this->store, [
            'name' => 'Amitabh Roy',
            'phone' => '9812345671',
            'blood_group' => 'O+',
            'city' => 'Kolkata',
            'gender' => 'male',
        ]);

        $this->createCustomer($this->store, [
            'name' => 'Pooja Hegde',
            'phone' => '9812345672',
            'blood_group' => 'AB+',
            'city' => 'Bangalore',
            'gender' => 'female',
        ]);

        // Filter by blood group
        $bgResponse = $this->actingAs($this->owner)->get(route('store.customers.index', ['blood_group' => 'O+']));
        $bgResponse->assertOk();
        $bgResponse->assertSee('Amitabh Roy');
        $bgResponse->assertDontSee('Pooja Hegde');

        // Filter by city
        $cityResponse = $this->actingAs($this->owner)->get(route('store.customers.index', ['city' => 'Bangalore']));
        $cityResponse->assertOk();
        $cityResponse->assertSee('Pooja Hegde');
        $cityResponse->assertDontSee('Amitabh Roy');
    }

    public function test_subscription_customer_limit_is_strictly_enforced(): void
    {
        // Set plan customer limit to 1
        $limitedPlan = SubscriptionPlan::create([
            'name' => 'Basic Single Customer Plan',
            'slug' => 'basic-single-cust',
            'price' => 199.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_customers' => 1],
            'features' => ['customer_management'],
        ]);

        $this->store->subscriptions()->update(['status' => SubscriptionStatus::EXPIRED]);

        Subscription::create([
            'store_id' => $this->store->id,
            'subscription_plan_id' => $limitedPlan->id,
            'start_date' => Carbon::today()->subDays(1),
            'end_date' => Carbon::today()->addDays(29),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        // First customer creates fine
        $this->createCustomer($this->store, [
            'name' => 'First Customer Allowed',
            'phone' => '9888877771',
        ]);

        // Attempt second customer via store() endpoint
        $response = $this->actingAs($this->owner)->post(route('store.customers.store'), [
            'name' => 'Second Customer Exceeding Limit',
            'phone' => '9888877772',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('customers', [
            'name' => 'Second Customer Exceeding Limit',
        ]);
    }
}
