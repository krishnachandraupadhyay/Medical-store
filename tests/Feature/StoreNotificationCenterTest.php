<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreNotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected Store $storeA;

    protected User $ownerA;

    protected Store $storeB;

    protected User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeA = Store::create([
            'code' => 'NOTIF-001',
            'name' => 'Store Alpha Pharmacy',
            'email' => 'alpha@pharmacy.test',
            'mobile' => '9811100011',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Alpha',
            'email' => 'alpha@owner.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeA->id,
        ]);

        $this->storeB = Store::create([
            'code' => 'NOTIF-002',
            'name' => 'Store Beta Pharmacy',
            'email' => 'beta@pharmacy.test',
            'mobile' => '9811100022',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Beta',
            'email' => 'beta@owner.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->storeB->id,
        ]);
    }

    public function test_store_owner_can_view_notification_center(): void
    {
        Notification::create([
            'title' => 'Low Stock Alert',
            'message' => 'Paracetamol 500mg has 5 units remaining.',
            'type' => NotificationType::LOW_STOCK,
            'priority' => NotificationPriority::HIGH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get(route('store.notifications.index'));

        $response->assertOk()
            ->assertViewIs('store.notifications.index')
            ->assertSee('Notification Center')
            ->assertSee('Low Stock Alert')
            ->assertSee('Paracetamol 500mg');
    }

    public function test_tenant_isolation_store_owner_cannot_see_other_store_notifications(): void
    {
        Notification::create([
            'title' => 'Store B Secret Alert',
            'message' => 'Confidential data for Store B',
            'type' => NotificationType::LOW_STOCK,
            'priority' => NotificationPriority::HIGH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeB->id,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get(route('store.notifications.index'));

        $response->assertOk()
            ->assertDontSee('Store B Secret Alert');
    }

    public function test_notification_filters_by_type_priority_and_status(): void
    {
        // Unread Low Stock
        Notification::create([
            'title' => 'Low Stock Paracetamol',
            'message' => 'Stock is low',
            'type' => NotificationType::LOW_STOCK,
            'priority' => NotificationPriority::HIGH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        // Read Expired Batch
        Notification::create([
            'title' => 'Expired Amoxicillin Batch',
            'message' => 'Batch expired',
            'type' => NotificationType::EXPIRED_BATCH,
            'priority' => NotificationPriority::CRITICAL,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => now(),
            'sent_at' => now(),
        ]);

        // Filter by unread
        $responseUnread = $this->actingAs($this->ownerA)
            ->get(route('store.notifications.index', ['status' => 'unread']));
        $responseUnread->assertOk()
            ->assertSee('Low Stock Paracetamol')
            ->assertDontSee('Expired Amoxicillin Batch');

        // Filter by read
        $responseRead = $this->actingAs($this->ownerA)
            ->get(route('store.notifications.index', ['status' => 'read']));
        $responseRead->assertOk()
            ->assertSee('Expired Amoxicillin Batch')
            ->assertDontSee('Low Stock Paracetamol');

        // Filter by type
        $responseType = $this->actingAs($this->ownerA)
            ->get(route('store.notifications.index', ['type' => NotificationType::LOW_STOCK->value]));
        $responseType->assertOk()
            ->assertSee('Low Stock Paracetamol')
            ->assertDontSee('Expired Amoxicillin Batch');
    }

    public function test_unread_count_json_endpoint(): void
    {
        Notification::create([
            'title' => 'Alert 1',
            'message' => 'Msg 1',
            'type' => NotificationType::LOW_STOCK,
            'priority' => NotificationPriority::HIGH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        Notification::create([
            'title' => 'Alert 2',
            'message' => 'Msg 2',
            'type' => NotificationType::OUT_OF_STOCK,
            'priority' => NotificationPriority::CRITICAL,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->getJson(route('store.notifications.unread-count'));

        $response->assertOk()
            ->assertJson(['unread_count' => 2]);
    }

    public function test_dropdown_json_endpoint_returns_formatted_notifications(): void
    {
        Notification::create([
            'title' => 'Batch Expiring',
            'message' => 'Batch B-101 is expiring in 15 days.',
            'type' => NotificationType::EXPIRING_BATCH,
            'priority' => NotificationPriority::HIGH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->getJson(route('store.notifications.dropdown'));

        $response->assertOk()
            ->assertJsonStructure([
                'unread_count',
                'notifications' => [
                    '*' => ['id', 'title', 'message', 'type', 'priority', 'is_unread', 'time_ago', 'action_url'],
                ],
            ])
            ->assertJsonFragment(['title' => 'Batch Expiring', 'unread_count' => 1]);
    }

    public function test_mark_single_notification_as_read(): void
    {
        $notif = Notification::create([
            'title' => 'Sale Completed',
            'message' => 'Sale #INV-001 completed',
            'type' => NotificationType::SALE_COMPLETED,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $this->assertNull($notif->fresh()->read_at);

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.notifications.mark-read', $notif->id));

        $response->assertRedirect();
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_cannot_mark_other_store_notification_as_read(): void
    {
        $notifB = Notification::create([
            'title' => 'Store B Alert',
            'message' => 'Alert for Store B',
            'type' => NotificationType::LOW_STOCK,
            'priority' => NotificationPriority::HIGH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeB->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.notifications.mark-read', $notifB->id));

        $response->assertForbidden();
        $this->assertNull($notifB->fresh()->read_at);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        Notification::create([
            'title' => 'Alert 1',
            'message' => 'Msg 1',
            'type' => NotificationType::LOW_STOCK,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        Notification::create([
            'title' => 'Alert 2',
            'message' => 'Msg 2',
            'type' => NotificationType::EXPIRING_BATCH,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->storeA->id,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->ownerA)
            ->post(route('store.notifications.mark-all-read'));

        $response->assertRedirect();
        $this->assertEquals(0, Notification::forStore($this->storeA->id)->unread()->count());
    }

    public function test_notification_preferences_view_and_update(): void
    {
        $responseView = $this->actingAs($this->ownerA)
            ->get(route('store.notifications.preferences'));

        $responseView->assertOk()
            ->assertViewIs('store.notifications.preferences')
            ->assertSee('Notification Preferences')
            ->assertSee('Inventory & Expiry Alerts')
            ->assertSee('Low Stock Alert');

        // Turn off low_stock and out_of_stock
        $responseUpdate = $this->actingAs($this->ownerA)
            ->put(route('store.notifications.update-preferences'), [
                'preferences' => [
                    'expiring_batch' => '1',
                    'sale_completed' => '1',
                    // low_stock is omitted, should become false
                ],
            ]);

        $responseUpdate->assertRedirect(route('store.notifications.preferences'))
            ->assertSessionHas('success');

        $this->assertFalse(NotificationPreference::isEnabled($this->storeA->id, $this->ownerA->id, NotificationType::LOW_STOCK->value));
        $this->assertTrue(NotificationPreference::isEnabled($this->storeA->id, $this->ownerA->id, NotificationType::EXPIRING_BATCH->value));
    }
}
