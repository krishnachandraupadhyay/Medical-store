<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSubscriptionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create([
            'code' => 'SUB-001',
            'name' => 'Sub Testing Pharmacy',
            'email' => 'sub@pharmacy.test',
            'mobile' => '9844433322',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'status' => StoreStatus::ACTIVE,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Sub Owner',
            'email' => 'subowner@pharmacy.test',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
            'store_id' => $this->store->id,
        ]);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Gold Plan',
            'slug' => 'gold-plan',
            'price' => 1999.00,
            'billing_cycle' => BillingCycle::MONTHLY,
            'trial_days' => 0,
            'status' => PlanStatus::ACTIVE,
            'features' => ['medicine_management'],
            'limits' => [],
        ]);
    }

    public function test_subscription_expiring_in_7_days_triggers_high_priority_alert(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->subDays(23),
            'end_date' => Carbon::today()->addDays(7),
            'status' => SubscriptionStatus::ACTIVE,
            'features' => ['medicine_management'],
        ]);

        /** @var ReminderService $service */
        $service = app(ReminderService::class);
        $count = $service->checkSubscriptionReminders($this->store);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::SUBSCRIPTION_EXPIRING->value,
            'priority' => NotificationPriority::HIGH->value,
            'alert_key' => "sub_expiring:{$sub->id}:7d",
        ]);

        // Duplicate check
        $secondCount = $service->checkSubscriptionReminders($this->store);
        $this->assertEquals(0, $secondCount);
    }

    public function test_subscription_expired_triggers_critical_alert(): void
    {
        $sub = Subscription::create([
            'store_id' => $this->store->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => Carbon::today()->subDays(35),
            'end_date' => Carbon::today()->subDays(2),
            'status' => SubscriptionStatus::EXPIRED,
            'features' => ['medicine_management'],
        ]);

        /** @var ReminderService $service */
        $service = app(ReminderService::class);
        $count = $service->checkSubscriptionReminders($this->store);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('notifications', [
            'store_id' => $this->store->id,
            'type' => NotificationType::SUBSCRIPTION_EXPIRED->value,
            'priority' => NotificationPriority::CRITICAL->value,
            'alert_key' => "sub_expired:{$sub->id}",
        ]);
    }
}
