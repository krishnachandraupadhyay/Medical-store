<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\SaleStatus;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use App\Services\BusinessAlertService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreBusinessAlertsTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create([
            'code' => 'ALERT-001',
            'name' => 'Alert Testing Pharmacy',
            'email' => 'alert@pharmacy.test',
            'mobile' => '9822211100',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Alert Owner',
            'email' => 'alertowner@pharmacy.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->store->id,
        ]);
    }

    public function test_low_stock_alert_is_triggered_when_stock_below_reorder_level(): void
    {
        $medicine = Medicine::create([
            'store_id' => $this->store->id,
            'name' => 'Cetirizine 10mg',
            'generic_name' => 'Cetirizine',
            'strength' => '10mg',
            'reorder_level' => 20,
            'status' => 'active',
        ]);

        Batch::create([
            'store_id' => $this->store->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-CET-01',
            'expiry_date' => Carbon::today()->addMonths(6),
            'quantity' => 10,
            'purchase_price' => 2.00,
            'mrp' => 5.00,
            'selling_price' => 4.50,
            'status' => 'active',
        ]);

        /** @var BusinessAlertService $service */
        $service = app(BusinessAlertService::class);
        $count = $service->checkLowStockAlerts($this->store);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::LOW_STOCK->value,
            'alert_key' => "low_stock:med:{$medicine->id}",
            'priority' => NotificationPriority::HIGH->value,
        ]);

        // Running again should not create duplicate unread alert
        $secondCount = $service->checkLowStockAlerts($this->store);
        $this->assertEquals(0, $secondCount);
        $this->assertEquals(1, Notification::where('store_id', $this->store->id)->where('alert_key', "low_stock:med:{$medicine->id}")->count());
    }

    public function test_out_of_stock_critical_alert_is_triggered_when_quantity_zero(): void
    {
        $medicine = Medicine::create([
            'store_id' => $this->store->id,
            'name' => 'Insulin Glargine',
            'generic_name' => 'Insulin',
            'strength' => '100IU',
            'reorder_level' => 5,
            'status' => 'active',
        ]);

        /** @var BusinessAlertService $service */
        $service = app(BusinessAlertService::class);
        $count = $service->checkOutOfStockAlerts($this->store);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::OUT_OF_STOCK->value,
            'alert_key' => "out_of_stock:med:{$medicine->id}",
            'priority' => NotificationPriority::CRITICAL->value,
        ]);
    }

    public function test_expiring_soon_and_expired_batch_alerts(): void
    {
        $medicine = Medicine::create([
            'store_id' => $this->store->id,
            'name' => 'Azithromycin 500mg',
            'generic_name' => 'Azithromycin',
            'strength' => '500mg',
            'reorder_level' => 10,
            'status' => 'active',
        ]);

        // Batch expiring in 10 days
        $expiringBatch = Batch::create([
            'store_id' => $this->store->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-EXP-SOON',
            'expiry_date' => Carbon::today()->addDays(10),
            'quantity' => 15,
            'purchase_price' => 10.00,
            'mrp' => 20.00,
            'selling_price' => 18.00,
            'status' => 'active',
        ]);

        // Batch already expired 5 days ago
        $expiredBatch = Batch::create([
            'store_id' => $this->store->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-EXPIRED',
            'expiry_date' => Carbon::today()->subDays(5),
            'quantity' => 8,
            'purchase_price' => 10.00,
            'mrp' => 20.00,
            'selling_price' => 18.00,
            'status' => 'active',
        ]);

        /** @var BusinessAlertService $service */
        $service = app(BusinessAlertService::class);

        // Check expiring
        $expiringCount = $service->checkExpiryAlerts($this->store, 30);
        $this->assertEquals(1, $expiringCount);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::EXPIRING_BATCH->value,
            'alert_key' => "expiring:batch:{$expiringBatch->id}:30d",
            'priority' => NotificationPriority::HIGH->value,
        ]);

        // Check expired
        $expiredCount = $service->checkExpiredBatchAlerts($this->store);
        $this->assertEquals(1, $expiredCount);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::EXPIRED_BATCH->value,
            'alert_key' => "expired:batch:{$expiredBatch->id}",
            'priority' => NotificationPriority::CRITICAL->value,
        ]);
    }

    public function test_customer_outstanding_alert_with_cooldown(): void
    {
        $customer = Customer::create([
            'store_id' => $this->store->id,
            'customer_code' => 'CUST-ALERT-1',
            'name' => 'Rajesh Sharma',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        Sale::create([
            'store_id' => $this->store->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'invoice_number' => 'INV-DUE-01',
            'sale_date' => now()->subDays(10),
            'status' => SaleStatus::COMPLETED,
            'subtotal' => 1000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 1000.00,
            'paid_amount' => 400.00,
            'payment_status' => 'partial',
            'payment_method' => 'cash',
        ]);

        /** @var BusinessAlertService $service */
        $service = app(BusinessAlertService::class);
        $count = $service->checkCustomerOutstandingAlerts($this->store);

        $weekKey = now()->format('Y-W');
        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::CUSTOMER_OUTSTANDING->value,
            'alert_key' => "cust_due:{$customer->id}:{$weekKey}",
        ]);

        // Running within same week must be skipped by alert_key
        $secondCount = $service->checkCustomerOutstandingAlerts($this->store);
        $this->assertEquals(0, $secondCount);
    }

    public function test_process_reminders_artisan_command(): void
    {
        $this->artisan('reminders:process')
            ->expectsOutputToContain('Reminders and business alerts processed successfully.')
            ->assertExitCode(0);
    }
}
