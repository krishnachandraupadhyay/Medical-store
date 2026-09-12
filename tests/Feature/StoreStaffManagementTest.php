<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\RbacService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected SubscriptionPlan $plan;

    protected Role $salesRole;

    protected function setUp(): void
    {
        parent::setUp();

        app(RbacService::class)->seedDefaultSystemRoles();

        $this->store = Store::create([
            'code' => 'MED-STF01',
            'name' => 'City Care Pharmacy',
            'email' => 'citycare@pharmacy.com',
            'mobile' => '+91 9876543200',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'store_type' => 'Retail',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Dr. Ajay Verma',
            'email' => 'ajay@citycare.com',
            'password' => Hash::make('OwnerPass123!'),
            'role' => UserRole::STORE_OWNER,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Standard Care Plan',
            'slug' => 'standard-care-plan',
            'price' => 1999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'status' => PlanStatus::ACTIVE,
            'features' => ['staff_management', 'medicine_management', 'sales_management'],
            'limits' => ['max_staff' => 3],
        ]);

        Subscription::create([
            'store_id'             => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'status'               => SubscriptionStatus::ACTIVE,
            'start_date'           => Carbon::today()->subDays(5)->toDateString(),
            'end_date'             => Carbon::today()->addDays(25)->toDateString(),
        ]);

        $this->salesRole = Role::where('slug', 'sales-staff')->whereNull('store_id')->firstOrFail();
    }

    /**
     * Test 1: Store owner can view staff list.
     */
    public function test_store_owner_can_view_staff_list(): void
    {
        User::factory()->create([
            'name' => 'Staff Member One',
            'email' => 'staff1@citycare.com',
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->get(route('store.staff.index'));

        $response->assertOk();
        $response->assertViewIs('store.staff.index');
        $response->assertSee('Staff Member One');
        $response->assertSee('staff1@citycare.com');
    }

    /**
     * Test 2: Store owner can create a staff member within plan quota.
     */
    public function test_store_owner_can_create_staff_member(): void
    {
        $response = $this->actingAs($this->owner)->post(route('store.staff.store'), [
            'name' => 'Vikram Seth',
            'email' => 'vikram@citycare.com',
            'mobile' => '+91 9123456780',
            'password' => 'StaffPass123!',
            'password_confirmation' => 'StaffPass123!',
            'role_id' => $this->salesRole->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('store.staff.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Vikram Seth',
            'email' => 'vikram@citycare.com',
            'store_id' => $this->store->id,
            'role' => UserRole::STORE_STAFF->value,
            'role_id' => $this->salesRole->id,
            'is_active' => 1,
            'created_by' => $this->owner->id,
        ]);

        // Audit Log verified
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::CREATED->value,
            'module' => AuditModule::STAFF->value,
        ]);
    }

    /**
     * Test 3: Staff creation fails when subscription max_staff quota is reached.
     */
    public function test_cannot_create_staff_when_plan_quota_is_exceeded(): void
    {
        // Max limit is 3, create 3 staff
        for ($i = 1; $i <= 3; $i++) {
            User::factory()->create([
                'name' => "Staff {$i}",
                'email' => "staff{$i}@citycare.com",
                'role' => UserRole::STORE_STAFF,
                'role_id' => $this->salesRole->id,
                'store_id' => $this->store->id,
                'is_active' => true,
            ]);
        }

        // Attempt to create 4th staff
        $response = $this->actingAs($this->owner)->post(route('store.staff.store'), [
            'name' => 'Exceeding Staff',
            'email' => 'exceeding@citycare.com',
            'password' => 'StaffPass123!',
            'password_confirmation' => 'StaffPass123!',
            'role_id' => $this->salesRole->id,
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('quota');
        $this->assertDatabaseMissing('users', ['email' => 'exceeding@citycare.com']);
    }

    /**
     * Test 4: Cannot assign a custom role that belongs to another store.
     */
    public function test_cannot_assign_role_belonging_to_another_store(): void
    {
        $otherStore = Store::create([
            'code'       => 'MED-OTHER99',
            'name'       => 'Other Store',
            'email'      => 'other@store.test',
            'mobile'     => '9800000099',
            'city'       => 'Chennai',
            'state'      => 'Tamil Nadu',
            'pincode'    => '600001',
            'store_type' => 'Retail',
            'status'     => StoreStatus::ACTIVE,
        ]);

        $otherStoreRole = Role::create([
            'store_id' => $otherStore->id,
            'name' => 'Exclusive Other Role',
            'slug' => 'exclusive-other-role',
            'is_system' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->post(route('store.staff.store'), [
            'name' => 'Hacker Staff',
            'email' => 'hacker@citycare.com',
            'password' => 'StaffPass123!',
            'password_confirmation' => 'StaffPass123!',
            'role_id' => $otherStoreRole->id,
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'hacker@citycare.com']);
    }

    /**
     * Test 5: Store owner can update staff member details and role.
     */
    public function test_store_owner_can_update_staff_member(): void
    {
        $staff = User::factory()->create([
            'name' => 'Kiran Joshi',
            'email' => 'kiran@citycare.com',
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $managerRole = Role::where('slug', 'store-manager')->whereNull('store_id')->firstOrFail();

        $response = $this->actingAs($this->owner)->put(route('store.staff.update', $staff), [
            'name' => 'Kiran Joshi Updated',
            'email' => 'kiran.updated@citycare.com',
            'mobile' => '+91 9998887776',
            'role_id' => $managerRole->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('store.staff.show', $staff));
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Kiran Joshi Updated',
            'email' => 'kiran.updated@citycare.com',
            'role_id' => $managerRole->id,
        ]);
    }

    /**
     * Test 6: Store owner can toggle staff active/inactive status.
     */
    public function test_store_owner_can_toggle_staff_status(): void
    {
        $staff = User::factory()->create([
            'name' => 'Pooja Nair',
            'email' => 'pooja@citycare.com',
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        // Toggle to inactive
        $response = $this->actingAs($this->owner)->patch(route('store.staff.toggle-status', $staff));
        $response->assertSessionHas('success');

        $staff->refresh();
        $this->assertFalse($staff->isActive());

        // Toggle back to active
        $this->actingAs($this->owner)->patch(route('store.staff.toggle-status', $staff));
        $staff->refresh();
        $this->assertTrue($staff->isActive());
    }

    /**
     * Test 7: Store owner can reset staff password.
     */
    public function test_store_owner_can_reset_staff_password(): void
    {
        $staff = User::factory()->create([
            'name' => 'Rahul Sen',
            'email' => 'rahul@citycare.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $this->salesRole->id,
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post(route('store.staff.reset-password', $staff), [
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $response->assertSessionHas('success');

        $staff->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword123!', $staff->password));
    }
}
