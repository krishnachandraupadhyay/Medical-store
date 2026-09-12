<?php

namespace Tests\Feature\Store;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\ContactNote;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\RbacService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRelationshipTest extends TestCase
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
            'name' => 'CRM Test Plan',
            'slug' => 'crm-test-plan',
            'price' => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => ['max_customers' => 999],
            'features' => [
                'customer_management',
                'supplier_management',
                'sales_management',
            ],
        ]);

        $this->store = Store::create([
            'code' => 'MED-CRM01',
            'name' => 'MedCRM Store A',
            'email' => 'crm-a@medstore.test',
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
            'code' => 'MED-CRM02',
            'name' => 'MedCRM Store B',
            'email' => 'crm-b@medstore.test',
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

    private function login(?User $user = null): static
    {
        return $this->actingAs($user ?? $this->owner);
    }

    private function makeCustomer(Store $store, array $attrs = []): Customer
    {
        return Customer::create(array_merge([
            'store_id' => $store->id,
            'customer_code' => 'CUS-'.uniqid(),
            'name' => 'Test Customer '.uniqid(),
            'phone' => '9900000000',
            'status' => 'active',
            'loyalty_tier' => 'regular',
            'created_by' => $this->owner->id,
            'updated_by' => $this->owner->id,
        ], $attrs));
    }

    // ─── Customer Index with Tier Filter ─────────────────────────────────────

    public function test_customer_index_loads(): void
    {
        $this->makeCustomer($this->store, ['loyalty_tier' => 'gold']);

        $this->login()->get(route('store.customers.index'))
            ->assertOk()
            ->assertSee('Customer Directory');
    }

    public function test_loyalty_tier_filter_works(): void
    {
        $this->makeCustomer($this->store, ['name' => 'Gold Mem',    'loyalty_tier' => 'gold']);
        $this->makeCustomer($this->store, ['name' => 'Reg Member', 'loyalty_tier' => 'regular']);

        $response = $this->login()->get(route('store.customers.index', ['tier' => 'gold']));
        $response->assertOk()->assertSee('Gold Mem')->assertDontSee('Reg Member');
    }

    // ─── Customer 360° Show ───────────────────────────────────────────────────

    public function test_customer_show_loads_with_all_data(): void
    {
        $customer = $this->makeCustomer($this->store);

        $this->login()->get(route('store.customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee('Notes & Follow-ups');
    }

    public function test_cannot_view_other_store_customer(): void
    {
        $customer = $this->makeCustomer($this->otherStore, ['created_by' => $this->otherOwner->id, 'updated_by' => $this->otherOwner->id]);

        $this->login()->get(route('store.customers.show', $customer))
            ->assertNotFound();
    }

    // ─── Notes ────────────────────────────────────────────────────────────────

    public function test_can_add_note_to_customer(): void
    {
        $customer = $this->makeCustomer($this->store);

        $this->login()->post(route('store.customers.notes.store', $customer), [
            'type' => 'call',
            'subject' => 'Follow up on order',
            'body' => 'Called the customer about their pending order.',
            'follow_up_date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_notes', [
            'notable_type' => Customer::class,
            'notable_id' => $customer->id,
            'store_id' => $this->store->id,
            'type' => 'call',
            'body' => 'Called the customer about their pending order.',
        ]);
    }

    public function test_note_validation_requires_body(): void
    {
        $customer = $this->makeCustomer($this->store);

        $this->login()->post(route('store.customers.notes.store', $customer), [
            'type' => 'note',
            'body' => '',
        ])->assertSessionHasErrors('body');
    }

    public function test_cannot_add_note_to_other_store_customer(): void
    {
        $customer = $this->makeCustomer($this->otherStore, ['created_by' => $this->otherOwner->id, 'updated_by' => $this->otherOwner->id]);

        $this->login()->post(route('store.customers.notes.store', $customer), [
            'type' => 'note',
            'body' => 'Sneaky note',
        ])->assertNotFound();

        $this->assertDatabaseMissing('contact_notes', ['notable_id' => $customer->id]);
    }

    // ─── Follow-up ────────────────────────────────────────────────────────────

    public function test_can_mark_followup_done(): void
    {
        $customer = $this->makeCustomer($this->store);

        $note = ContactNote::create([
            'store_id' => $this->store->id,
            'notable_type' => Customer::class,
            'notable_id' => $customer->id,
            'type' => 'followup',
            'body' => 'Call back next week.',
            'follow_up_date' => now()->addDay()->toDateString(),
            'follow_up_done' => false,
            'created_by' => $this->owner->id,
        ]);

        $this->login()->patch(route('store.customers.notes.done', [$customer, $note]))
            ->assertRedirect();

        $this->assertDatabaseHas('contact_notes', [
            'id' => $note->id,
            'follow_up_done' => true,
        ]);
    }

    public function test_cannot_mark_other_store_customer_followup_done(): void
    {
        $customer = $this->makeCustomer($this->otherStore, ['created_by' => $this->otherOwner->id, 'updated_by' => $this->otherOwner->id]);

        $note = ContactNote::create([
            'store_id' => $this->otherStore->id,
            'notable_type' => Customer::class,
            'notable_id' => $customer->id,
            'type' => 'followup',
            'body' => 'Other store note.',
            'follow_up_date' => now()->addDay()->toDateString(),
            'follow_up_done' => false,
            'created_by' => $this->otherOwner->id,
        ]);

        $this->login()->patch(route('store.customers.notes.done', [$customer, $note]))
            ->assertNotFound();

        $this->assertDatabaseHas('contact_notes', [
            'id' => $note->id,
            'follow_up_done' => false,
        ]);
    }

    // ─── Delete Note ──────────────────────────────────────────────────────────

    public function test_can_delete_own_note(): void
    {
        $customer = $this->makeCustomer($this->store);

        $note = ContactNote::create([
            'store_id' => $this->store->id,
            'notable_type' => Customer::class,
            'notable_id' => $customer->id,
            'type' => 'note',
            'body' => 'To be deleted.',
            'created_by' => $this->owner->id,
        ]);

        $this->login()->delete(route('store.customers.notes.destroy', [$customer, $note]))
            ->assertRedirect();

        $this->assertDatabaseMissing('contact_notes', ['id' => $note->id]);
    }

    public function test_cannot_delete_other_store_note(): void
    {
        $customer = $this->makeCustomer($this->otherStore, ['created_by' => $this->otherOwner->id, 'updated_by' => $this->otherOwner->id]);

        $note = ContactNote::create([
            'store_id' => $this->otherStore->id,
            'notable_type' => Customer::class,
            'notable_id' => $customer->id,
            'type' => 'note',
            'body' => 'Protected note.',
            'created_by' => $this->otherOwner->id,
        ]);

        $this->login()->delete(route('store.customers.notes.destroy', [$customer, $note]))
            ->assertNotFound();

        $this->assertDatabaseHas('contact_notes', ['id' => $note->id]);
    }

    // ─── ContactNote Model Helpers ────────────────────────────────────────────

    public function test_overdue_followup_detected(): void
    {
        $customer = $this->makeCustomer($this->store);

        $note = new ContactNote([
            'store_id' => $this->store->id,
            'notable_type' => Customer::class,
            'notable_id' => $customer->id,
            'type' => 'followup',
            'body' => 'Overdue.',
            'follow_up_date' => now()->subDay(),
            'follow_up_done' => false,
            'created_by' => $this->owner->id,
        ]);

        $this->assertTrue($note->isFollowUpOverdue());
        $this->assertEquals('overdue', $note->followUpStatusBadge());
    }

    public function test_loyalty_tier_auto_labels(): void
    {
        $customer = new Customer(['loyalty_tier' => 'gold']);
        $this->assertEquals('Gold', $customer->loyaltyTierLabel());
        $this->assertEquals('tier-gold', $customer->loyaltyTierBadgeClass());
    }
}
