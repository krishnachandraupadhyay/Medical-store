<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminNotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $storeOwner;

    protected Store $store;

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
    }

    public function test_super_admin_can_view_notifications_list(): void
    {
        Notification::create([
            'title' => 'Scheduled Platform Maintenance',
            'message' => 'Platform will undergo maintenance at midnight.',
            'type' => NotificationType::MAINTENANCE,
            'priority' => NotificationPriority::IMPORTANT,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.notifications.index'));

        $response->assertOk();
        $response->assertViewIs('super-admin.notifications.index');
        $response->assertSee('Notification Management');
        $response->assertSee('Scheduled Platform Maintenance');
    }

    public function test_store_owner_cannot_access_notifications_management(): void
    {
        $response = $this->actingAs($this->storeOwner)
            ->get(route('super-admin.notifications.index'));

        $response->assertRedirect(route('super-admin.login'));

        $responseJson = $this->actingAs($this->storeOwner)
            ->getJson(route('super-admin.notifications.index'));

        $responseJson->assertStatus(403);
    }

    public function test_guest_cannot_access_notifications_management(): void
    {
        $response = $this->get(route('super-admin.notifications.index'));
        $response->assertRedirect(route('super-admin.login'));
    }

    public function test_super_admin_can_create_broadcast_notification_immediately(): void
    {
        $payload = [
            'title' => 'Important GST Compliance Update',
            'message' => 'Please verify your GST numbers before the quarterly return deadline.',
            'type' => NotificationType::ANNOUNCEMENT->value,
            'priority' => NotificationPriority::URGENT->value,
            'target_type' => NotificationTargetType::ALL_STORES->value,
            'delivery_mode' => 'now',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.store'), $payload);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Important GST Compliance Update',
            'type' => NotificationType::ANNOUNCEMENT->value,
            'priority' => NotificationPriority::URGENT->value,
            'target_type' => NotificationTargetType::ALL_STORES->value,
            'status' => NotificationStatus::SENT->value,
            'created_by' => $this->superAdmin->id,
        ]);

        $notification = Notification::where('title', 'Important GST Compliance Update')->first();
        $this->assertNotNull($notification->sent_at);
        $response->assertRedirect(route('super-admin.notifications.show', $notification));
        $response->assertSessionHas('success');
    }

    public function test_super_admin_can_schedule_notification_for_later(): void
    {
        $futureTime = Carbon::now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);

        $payload = [
            'title' => 'Store Specific Expiry Warning',
            'message' => 'Your subscription tier will expire in 3 days.',
            'type' => NotificationType::SUBSCRIPTION->value,
            'priority' => NotificationPriority::IMPORTANT->value,
            'target_type' => NotificationTargetType::SPECIFIC_STORE->value,
            'store_id' => $this->store->id,
            'delivery_mode' => 'schedule',
            'scheduled_at' => $futureTime->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.store'), $payload);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Store Specific Expiry Warning',
            'target_type' => NotificationTargetType::SPECIFIC_STORE->value,
            'store_id' => $this->store->id,
            'status' => NotificationStatus::SCHEDULED->value,
            'created_by' => $this->superAdmin->id,
        ]);

        $notification = Notification::where('title', 'Store Specific Expiry Warning')->first();
        $this->assertNotNull($notification->scheduled_at);
        $this->assertNull($notification->sent_at);
        $response->assertRedirect(route('super-admin.notifications.show', $notification));
    }

    public function test_notification_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'message',
            'type',
            'priority',
            'target_type',
            'delivery_mode',
        ]);
    }

    public function test_specific_store_target_requires_valid_store(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.store'), [
                'title' => 'Target Test',
                'message' => 'Message body',
                'type' => NotificationType::GENERAL->value,
                'priority' => NotificationPriority::NORMAL->value,
                'target_type' => NotificationTargetType::SPECIFIC_STORE->value,
                'store_id' => null,
                'delivery_mode' => 'now',
            ]);

        $response->assertSessionHasErrors(['store_id']);
    }

    public function test_scheduled_notification_requires_future_datetime(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.store'), [
                'title' => 'Schedule Test',
                'message' => 'Message body',
                'type' => NotificationType::GENERAL->value,
                'priority' => NotificationPriority::NORMAL->value,
                'target_type' => NotificationTargetType::ALL_STORES->value,
                'delivery_mode' => 'schedule',
                'scheduled_at' => Carbon::now()->subDay()->format('Y-m-d H:i:s'), // past date
            ]);

        $response->assertSessionHasErrors(['scheduled_at']);
    }

    public function test_super_admin_can_view_notification_details(): void
    {
        $notification = Notification::create([
            'title' => 'Payment Gateway Maintenance',
            'message' => 'UPI channels will undergo maintenance.',
            'type' => NotificationType::PAYMENT,
            'priority' => NotificationPriority::URGENT,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.notifications.show', $notification));

        $response->assertOk();
        $response->assertViewIs('super-admin.notifications.show');
        $response->assertSee('Payment Gateway Maintenance');
        $response->assertSee('UPI channels will undergo maintenance.');
        $response->assertSee('Urgent Priority');
    }

    public function test_super_admin_can_edit_draft_notification(): void
    {
        $notification = Notification::create([
            'title' => 'Draft Title',
            'message' => 'Draft message.',
            'type' => NotificationType::GENERAL,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::DRAFT,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.notifications.update', $notification), [
                'title' => 'Updated Title',
                'message' => 'Updated message.',
                'type' => NotificationType::ANNOUNCEMENT->value,
                'priority' => NotificationPriority::IMPORTANT->value,
                'target_type' => NotificationTargetType::ALL_STORES->value,
                'delivery_mode' => 'draft',
            ]);

        $response->assertRedirect(route('super-admin.notifications.show', $notification));
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'title' => 'Updated Title',
            'priority' => NotificationPriority::IMPORTANT->value,
        ]);
    }

    public function test_sent_notification_cannot_be_edited(): void
    {
        $notification = Notification::create([
            'title' => 'Sent Title',
            'message' => 'Sent message.',
            'type' => NotificationType::GENERAL,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        $responseEdit = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.notifications.edit', $notification));
        $responseEdit->assertRedirect(route('super-admin.notifications.show', $notification));

        $responseUpdate = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.notifications.update', $notification), [
                'title' => 'Hacked Title',
                'message' => 'Hacked message.',
                'type' => NotificationType::GENERAL->value,
                'priority' => NotificationPriority::NORMAL->value,
                'target_type' => NotificationTargetType::ALL_STORES->value,
                'delivery_mode' => 'now',
            ]);

        $responseUpdate->assertRedirect(route('super-admin.notifications.show', $notification));
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'title' => 'Sent Title',
        ]);
    }

    public function test_scheduled_notification_can_be_cancelled(): void
    {
        $notification = Notification::create([
            'title' => 'Scheduled Cancellation Test',
            'message' => 'Will be cancelled.',
            'type' => NotificationType::GENERAL,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SCHEDULED,
            'scheduled_at' => Carbon::now()->addDays(5),
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.notifications.cancel', $notification));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => NotificationStatus::CANCELLED->value,
            'updated_by' => $this->superAdmin->id,
        ]);
    }

    public function test_sent_notification_cannot_be_deleted(): void
    {
        $notification = Notification::create([
            'title' => 'Sent Deletion Guard Test',
            'message' => 'Cannot be deleted.',
            'type' => NotificationType::GENERAL,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.notifications.destroy', $notification));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'deleted_at' => null,
        ]);
    }

    public function test_draft_notification_can_be_deleted(): void
    {
        $notification = Notification::create([
            'title' => 'Draft Deletion Test',
            'message' => 'Can be deleted.',
            'type' => NotificationType::GENERAL,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::DRAFT,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.notifications.destroy', $notification));

        $response->assertRedirect(route('super-admin.notifications.index'));
        $this->assertSoftDeleted('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_notifications_search_and_filters(): void
    {
        Notification::create([
            'title' => 'Alpha Security Patch',
            'message' => 'Security patch details.',
            'type' => NotificationType::SYSTEM,
            'priority' => NotificationPriority::URGENT,
            'target_type' => NotificationTargetType::ALL_STORES,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
        ]);

        Notification::create([
            'title' => 'Beta Holiday Notice',
            'message' => 'Holiday notice.',
            'type' => NotificationType::ANNOUNCEMENT,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->store->id,
            'status' => NotificationStatus::DRAFT,
            'created_by' => $this->superAdmin->id,
        ]);

        // Search
        $searchRes = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.notifications.index', ['search' => 'Alpha']));
        $searchRes->assertSee('Alpha Security Patch');
        $searchRes->assertDontSee('Beta Holiday Notice');

        // Filter by Priority
        $prioRes = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.notifications.index', ['priority' => NotificationPriority::URGENT->value]));
        $prioRes->assertSee('Alpha Security Patch');
        $prioRes->assertDontSee('Beta Holiday Notice');

        // Filter by Status
        $statusRes = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.notifications.index', ['status' => NotificationStatus::DRAFT->value]));
        $statusRes->assertSee('Beta Holiday Notice');
        $statusRes->assertDontSee('Alpha Security Patch');
    }

    public function test_store_and_notification_relationship(): void
    {
        $notification = Notification::create([
            'title' => 'Target Store Relationship Test',
            'message' => 'Message to store.',
            'type' => NotificationType::GENERAL,
            'priority' => NotificationPriority::NORMAL,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $this->store->id,
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'created_by' => $this->superAdmin->id,
            'updated_by' => $this->superAdmin->id,
        ]);

        $this->assertEquals($this->store->id, $notification->store->id);
        $this->assertEquals($this->superAdmin->id, $notification->creator->id);
        $this->assertTrue($this->store->notifications->contains($notification));
    }
}
