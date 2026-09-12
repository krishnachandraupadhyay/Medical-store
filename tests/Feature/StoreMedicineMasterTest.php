<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Facades\CurrentStore;
use App\Facades\SubscriptionAccess;
use App\Models\Category;
use App\Models\DosageForm;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreMedicineMasterTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected SubscriptionPlan $medicinePlan;

    protected Category $category;

    protected Manufacturer $manufacturer;

    protected DosageForm $dosageForm;

    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Subscription Plan with medicine_management feature and quota of 5
        $this->medicinePlan = SubscriptionPlan::create([
            'name' => 'Pharma Growth Tier',
            'slug' => 'pharma-growth-tier',
            'price' => 999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'limits' => [
                'max_staff' => 5,
                'max_medicines' => 5, // Limit of 5 medicines for testing quota
                'max_invoices' => 500,
                'max_customers' => 200,
            ],
            'features' => [
                'medicine_management',
                'inventory_management',
            ],
        ]);

        // 2. Setup Store A & Owner A with active subscription
        $this->storeA = Store::create([
            'code' => 'MED-000001',
            'name' => 'Apex Pharmacy Store A',
            'email' => 'apex@medistore.test',
            'mobile' => '9876543201',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'ownerA@apex.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeA->id,
            'subscription_plan_id' => $this->medicinePlan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        // 3. Setup Store B & Owner B
        $this->storeB = Store::create([
            'code' => 'MED-000002',
            'name' => 'Beacon Chemist Store B',
            'email' => 'beacon@medistore.test',
            'mobile' => '9876543202',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'ownerB@beacon.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);

        Subscription::create([
            'store_id' => $this->storeB->id,
            'subscription_plan_id' => $this->medicinePlan->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(25),
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        // 4. Setup Global Masters
        $this->category = Category::create([
            'store_id' => null,
            'name' => 'Analgesics & Antipyretics',
            'slug' => 'analgesics-antipyretics',
            'status' => 'active',
        ]);

        $this->manufacturer = Manufacturer::create([
            'store_id' => null,
            'name' => 'Cipla Ltd',
            'code' => 'CIPLA',
            'status' => 'active',
        ]);

        $this->dosageForm = DosageForm::create([
            'store_id' => null,
            'name' => 'Tablet',
            'short_name' => 'Tab',
            'status' => 'active',
        ]);

        $this->unit = Unit::create([
            'store_id' => null,
            'name' => 'Strip',
            'short_name' => 'Strip',
            'status' => 'active',
        ]);

        SubscriptionAccess::clearCache();
        CurrentStore::reset();
    }

    /**
     * TEST 1: Store Owner can view Medicines index directory.
     */
    public function test_store_owner_can_view_medicines_index(): void
    {
        Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Paracetamol 500mg',
            'generic_name' => 'Paracetamol',
            'brand_name' => 'Crocin',
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'dosage_form_id' => $this->dosageForm->id,
            'unit_id' => $this->unit->id,
            'strength' => '500 mg',
            'pack_size' => '10 Tablets',
            'prescription_required' => false,
            'hsn_code' => '3004',
            'gst_rate' => 12.00,
            'reorder_level' => 10,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get('/store/medicines');

        $response->assertStatus(200);
        $response->assertSee('Medicine Master');
        $response->assertSee('Paracetamol 500mg');
        $response->assertSee('Crocin');
        $response->assertSee('500 mg');
        $response->assertSee('Cipla Ltd');
    }

    /**
     * TEST 2: Server-side search finds medicine by name, generic, or brand.
     */
    public function test_store_owner_can_search_medicines(): void
    {
        Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Dolo 650',
            'generic_name' => 'Paracetamol',
            'brand_name' => 'Dolo',
            'status' => 'active',
            'gst_rate' => 12.00,
            'reorder_level' => 10,
        ]);

        Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Amoxicillin 250mg',
            'generic_name' => 'Amoxicillin Trihydrate',
            'brand_name' => 'Mox',
            'status' => 'active',
            'gst_rate' => 12.00,
            'reorder_level' => 10,
        ]);

        $this->actingAs($this->ownerA);

        // Search by name
        $res1 = $this->get('/store/medicines?search=Dolo');
        $res1->assertSee('Dolo 650');
        $res1->assertDontSee('Amoxicillin 250mg');

        // Search by generic
        $res2 = $this->get('/store/medicines?search=Amoxicillin');
        $res2->assertSee('Amoxicillin 250mg');
        $res2->assertDontSee('Dolo 650');
    }

    /**
     * TEST 3: Store Owner can create a new Medicine.
     */
    public function test_store_owner_can_create_new_medicine(): void
    {
        $response = $this->actingAs($this->ownerA)->post('/store/medicines', [
            'name' => 'Azithromycin 500mg',
            'generic_name' => 'Azithromycin',
            'brand_name' => 'Azithral',
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'dosage_form_id' => $this->dosageForm->id,
            'unit_id' => $this->unit->id,
            'strength' => '500 mg',
            'pack_size' => '3 Tablets',
            'prescription_required' => '1',
            'hsn_code' => '3004',
            'gst_rate' => '12.00',
            'reorder_level' => '15',
            'description' => 'Take once daily before meals.',
            'status' => 'active',
        ]);

        $medicine = Medicine::where('name', 'Azithromycin 500mg')->first();

        $this->assertNotNull($medicine);
        $this->assertEquals($this->storeA->id, $medicine->store_id);
        $this->assertEquals('Azithral', $medicine->brand_name);
        $this->assertTrue($medicine->prescription_required);
        $this->assertEquals(12.00, (float) $medicine->gst_rate);
        $this->assertEquals(15, $medicine->reorder_level);
        $this->assertEquals($this->ownerA->id, $medicine->created_by);

        $response->assertRedirect(route('store.medicines.show', $medicine));
    }

    /**
     * TEST 4: Store Owner can view and edit Medicine details.
     */
    public function test_store_owner_can_view_and_update_medicine(): void
    {
        $medicine = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Pantoprazole 40mg',
            'generic_name' => 'Pantoprazole',
            'brand_name' => 'Pan 40',
            'status' => 'active',
            'gst_rate' => 12.00,
            'reorder_level' => 10,
        ]);

        $this->actingAs($this->ownerA);

        // Show page
        $showRes = $this->get("/store/medicines/{$medicine->id}");
        $showRes->assertStatus(200);
        $showRes->assertSee('Pantoprazole 40mg');
        $showRes->assertSee('Pan 40');

        // Update
        $updateRes = $this->put("/store/medicines/{$medicine->id}", [
            'name' => 'Pantoprazole 40mg Gastro',
            'generic_name' => 'Pantoprazole Sodium',
            'brand_name' => 'Pan 40 Gastro',
            'gst_rate' => '18.00',
            'reorder_level' => '25',
            'status' => 'active',
        ]);

        $medicine->refresh();
        $this->assertEquals('Pantoprazole 40mg Gastro', $medicine->name);
        $this->assertEquals(18.00, (float) $medicine->gst_rate);
        $this->assertEquals(25, $medicine->reorder_level);
        $this->assertEquals($this->ownerA->id, $medicine->updated_by);

        $updateRes->assertRedirect(route('store.medicines.show', $medicine));
    }

    /**
     * TEST 5: Store Owner can toggle status and soft delete medicine.
     */
    public function test_store_owner_can_toggle_status_and_soft_delete(): void
    {
        $medicine = Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Cetirizine 10mg',
            'status' => 'active',
            'gst_rate' => 12.00,
            'reorder_level' => 5,
        ]);

        $this->actingAs($this->ownerA);

        // Toggle active -> inactive
        $this->patch("/store/medicines/{$medicine->id}/status");
        $medicine->refresh();
        $this->assertEquals('inactive', $medicine->status);

        // Toggle inactive -> active
        $this->patch("/store/medicines/{$medicine->id}/status");
        $medicine->refresh();
        $this->assertEquals('active', $medicine->status);

        // Soft delete
        $delRes = $this->delete("/store/medicines/{$medicine->id}");
        $delRes->assertRedirect(route('store.medicines.index'));
        $this->assertSoftDeleted('medicines', ['id' => $medicine->id]);
    }

    /**
     * TEST 6: Validation catches missing required attributes.
     */
    public function test_medicine_validation_catches_invalid_inputs(): void
    {
        $response = $this->actingAs($this->ownerA)->post('/store/medicines', [
            'name' => '',
            'gst_rate' => '-5',
            'reorder_level' => '-1',
            'status' => 'invalid_status',
            'category_id' => 999999, // Non-existent category
            'manufacturer_id' => 999999, // Non-existent manufacturer
        ]);

        $response->assertSessionHasErrors(['name', 'gst_rate', 'reorder_level', 'status', 'category_id', 'manufacturer_id']);
    }

    /**
     * TEST 7: Tenant Isolation - Store A cannot view, edit, or delete Store B's medicine.
     */
    public function test_tenant_isolation_prevents_cross_store_access(): void
    {
        $medicineB = Medicine::create([
            'store_id' => $this->storeB->id,
            'name' => 'Beta Exclusive Drug',
            'status' => 'active',
            'gst_rate' => 12.00,
            'reorder_level' => 10,
        ]);

        $this->actingAs($this->ownerA); // Logged in as Store A Owner

        // View Store B medicine -> 404
        $this->get("/store/medicines/{$medicineB->id}")->assertStatus(404);

        // Edit Store B medicine -> 404
        $this->get("/store/medicines/{$medicineB->id}/edit")->assertStatus(404);

        // Update Store B medicine -> 404
        $this->put("/store/medicines/{$medicineB->id}", [
            'name' => 'Tampered Drug',
            'gst_rate' => '12.00',
            'reorder_level' => '10',
            'status' => 'active',
        ])->assertStatus(404);

        // Delete Store B medicine -> 404
        $this->delete("/store/medicines/{$medicineB->id}")->assertStatus(404);

        $this->assertDatabaseHas('medicines', [
            'id' => $medicineB->id,
            'name' => 'Beta Exclusive Drug',
        ]);
    }

    /**
     * TEST 8: Duplicate medicine within same store is prevented, but allowed in another store.
     */
    public function test_duplicate_medicine_handling(): void
    {
        Medicine::create([
            'store_id' => $this->storeA->id,
            'name' => 'Metformin 500mg',
            'strength' => '500 mg',
            'dosage_form_id' => $this->dosageForm->id,
            'status' => 'active',
            'gst_rate' => 12.00,
            'reorder_level' => 10,
        ]);

        // Attempt duplicate in Store A -> Rejected
        $responseA = $this->actingAs($this->ownerA)->post('/store/medicines', [
            'name' => 'Metformin 500mg',
            'strength' => '500 mg',
            'dosage_form_id' => $this->dosageForm->id,
            'gst_rate' => '12.00',
            'reorder_level' => '10',
            'status' => 'active',
        ]);

        $responseA->assertSessionHasErrors(['name']);

        // Same medicine in Store B -> Allowed
        $responseB = $this->actingAs($this->ownerB)->post('/store/medicines', [
            'name' => 'Metformin 500mg',
            'strength' => '500 mg',
            'dosage_form_id' => $this->dosageForm->id,
            'gst_rate' => '12.00',
            'reorder_level' => '10',
            'status' => 'active',
        ]);

        $this->assertEquals(2, Medicine::where('name', 'Metformin 500mg')->count());
    }

    /**
     * TEST 9: Quota Limit - Store Owner is blocked from creating medicine when limit is reached.
     */
    public function test_medicine_creation_is_blocked_at_quota_limit(): void
    {
        // Store A's plan has limit max_medicines = 5. Create 5 active medicines.
        for ($i = 1; $i <= 5; $i++) {
            Medicine::create([
                'store_id' => $this->storeA->id,
                'name' => "Batch Medicine {$i}",
                'status' => 'active',
                'gst_rate' => 12.00,
                'reorder_level' => 10,
            ]);
        }

        $this->actingAs($this->ownerA);
        SubscriptionAccess::clearCache();

        $this->assertEquals(5, SubscriptionAccess::getUsage($this->storeA, 'max_medicines'));
        $this->assertEquals(0, SubscriptionAccess::remaining($this->storeA, 'max_medicines'));
        $this->assertFalse(SubscriptionAccess::checkLimit($this->storeA, 'max_medicines', 1));

        // Create page redirects with error
        $createGetRes = $this->get('/store/medicines/create');
        $createGetRes->assertRedirect(route('store.medicines.index'));
        $createGetRes->assertSessionHas('error');

        // Post request is rejected
        $postRes = $this->post('/store/medicines', [
            'name' => 'Exceeding Quota Drug',
            'gst_rate' => '12.00',
            'reorder_level' => '10',
            'status' => 'active',
        ]);

        $postRes->assertSessionHas('error');
        $this->assertDatabaseMissing('medicines', ['name' => 'Exceeding Quota Drug']);
    }
}
