<?php

namespace Tests\Feature\Store;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\ContactNote;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\User;
use App\Services\RbacService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupplierRelationshipTest extends TestCase
{
    use RefreshDatabase;

    private Store            $store;
    private Store            $otherStore;
    private User             $owner;
    private User             $otherOwner;
    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        app(RbacService::class)->seedDefaultSystemRoles();

        $this->plan = SubscriptionPlan::create([
            'name'          => 'Supplier CRM Plan',
            'slug'          => 'supplier-crm-plan',
            'price'         => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days'    => 0,
            'status'        => PlanStatus::ACTIVE,
            'limits'        => ['max_suppliers' => 999],
            'features'      => [
                'supplier_management',
                'purchase_management',
            ],
        ]);

        $this->store = Store::create([
            'code'       => 'MED-SUP01',
            'name'       => 'Supplier Test Store A',
            'email'      => 'sup-a@medstore.test',
            'mobile'     => '9876540001',
            'city'       => 'Mumbai',
            'state'      => 'Maharashtra',
            'pincode'    => '400001',
            'store_type' => 'Retail',
            'status'     => StoreStatus::ACTIVE,
        ]);

        $this->owner = User::factory()->create([
            'store_id'  => $this->store->id,
            'role'      => UserRole::STORE_OWNER,
            'is_active' => true,
            'password'  => Hash::make('Password123!'),
        ]);

        Subscription::create([
            'store_id'             => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date'           => Carbon::today()->subDays(5),
            'end_date'             => Carbon::today()->addDays(25),
            'status'               => SubscriptionStatus::ACTIVE,
        ]);

        $this->otherStore = Store::create([
            'code'       => 'MED-SUP02',
            'name'       => 'Supplier Test Store B',
            'email'      => 'sup-b@medstore.test',
            'mobile'     => '9876540002',
            'city'       => 'Pune',
            'state'      => 'Maharashtra',
            'pincode'    => '411001',
            'store_type' => 'Retail',
            'status'     => StoreStatus::ACTIVE,
        ]);

        $this->otherOwner = User::factory()->create([
            'store_id'  => $this->otherStore->id,
            'role'      => UserRole::STORE_OWNER,
            'is_active' => true,
            'password'  => Hash::make('Password123!'),
        ]);

        Subscription::create([
            'store_id'             => $this->otherStore->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date'           => Carbon::today()->subDays(5),
            'end_date'             => Carbon::today()->addDays(25),
            'status'               => SubscriptionStatus::ACTIVE,
        ]);
    }

    private function login(?User $user = null): static
    {
        return $this->actingAs($user ?? $this->owner);
    }

    private function makeSupplier(Store $store, array $attrs = []): Supplier
    {
        return Supplier::create(array_merge([
            'store_id'   => $store->id,
            'name'       => 'Supplier ' . uniqid(),
            'phone'      => '9900000000',
            'status'     => 'active',
            'created_by' => $this->owner->id,
        ], $attrs));
    }

    // ─── Supplier 360° Show ───────────────────────────────────────────────────

    public function test_supplier_show_loads(): void
    {
        $supplier = $this->makeSupplier($this->store);

        $this->login()->get(route('store.suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($supplier->name)
            ->assertSee('Notes & Follow-ups');
    }

    public function test_cannot_view_other_store_supplier(): void
    {
        $supplier = $this->makeSupplier($this->otherStore, ['created_by' => $this->otherOwner->id]);

        $this->login()->get(route('store.suppliers.show', $supplier))
            ->assertNotFound();
    }

    // ─── Notes ────────────────────────────────────────────────────────────────

    public function test_can_add_note_to_supplier(): void
    {
        $supplier = $this->makeSupplier($this->store);

        $this->login()->post(route('store.suppliers.notes.store', $supplier), [
            'type'    => 'meeting',
            'subject' => 'Pricing discussion',
            'body'    => 'Discussed bulk discount of 5% for orders over 50k.',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_notes', [
            'notable_type' => Supplier::class,
            'notable_id'   => $supplier->id,
            'store_id'     => $this->store->id,
            'type'         => 'meeting',
        ]);
    }

    public function test_cannot_add_note_to_other_store_supplier(): void
    {
        $supplier = $this->makeSupplier($this->otherStore, ['created_by' => $this->otherOwner->id]);

        $this->login()->post(route('store.suppliers.notes.store', $supplier), [
            'type' => 'note',
            'body' => 'Cross-store attack.',
        ])->assertNotFound();

        $this->assertDatabaseMissing('contact_notes', ['notable_id' => $supplier->id]);
    }

    // ─── Follow-up ────────────────────────────────────────────────────────────

    public function test_can_mark_supplier_followup_done(): void
    {
        $supplier = $this->makeSupplier($this->store);

        $note = ContactNote::create([
            'store_id'       => $this->store->id,
            'notable_type'   => Supplier::class,
            'notable_id'     => $supplier->id,
            'type'           => 'followup',
            'body'           => 'Call about invoice.',
            'follow_up_date' => now()->addDay()->toDateString(),
            'follow_up_done' => false,
            'created_by'     => $this->owner->id,
        ]);

        $this->login()->patch(route('store.suppliers.notes.done', [$supplier, $note]))
            ->assertRedirect();

        $this->assertDatabaseHas('contact_notes', [
            'id'             => $note->id,
            'follow_up_done' => true,
        ]);
    }

    // ─── Delete Note ──────────────────────────────────────────────────────────

    public function test_can_delete_supplier_note(): void
    {
        $supplier = $this->makeSupplier($this->store);

        $note = ContactNote::create([
            'store_id'     => $this->store->id,
            'notable_type' => Supplier::class,
            'notable_id'   => $supplier->id,
            'type'         => 'note',
            'body'         => 'To delete.',
            'created_by'   => $this->owner->id,
        ]);

        $this->login()->delete(route('store.suppliers.notes.destroy', [$supplier, $note]))
            ->assertRedirect();

        $this->assertDatabaseMissing('contact_notes', ['id' => $note->id]);
    }

    // ─── Financial Helpers ────────────────────────────────────────────────────

    public function test_supplier_financial_helpers_return_zero_with_no_purchases(): void
    {
        $supplier = $this->makeSupplier($this->store);

        $this->assertEquals(0.0, $supplier->totalPurchased());
        $this->assertEquals(0.0, $supplier->totalPaid());
        $this->assertEquals(0.0, $supplier->outstandingAmount());
        $this->assertEquals(0, $supplier->purchasesCount());
        $this->assertNull($supplier->lastPurchaseDate());
    }

    // ─── Supplier CRM Fields ─────────────────────────────────────────────────

    public function test_supplier_crm_fields_are_stored(): void
    {
        $supplier = $this->makeSupplier($this->store, [
            'tags'          => ['pharma', 'generic'],
            'credit_limit'  => 100000,
            'payment_terms' => 'Net 30',
        ]);

        $this->assertEquals(['pharma', 'generic'], $supplier->fresh()->tags);
        $this->assertEquals(100000.0, $supplier->fresh()->credit_limit);
        $this->assertEquals('Net 30', $supplier->fresh()->payment_terms);
    }
}
