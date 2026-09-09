<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'trial_ends_at',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'trial_ends_at' => 'date',
            'status' => SubscriptionStatus::class,
        ];
    }

    /**
     * The store associated with this subscription.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * The subscription plan tier assigned.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * The super admin user who created this subscription record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The super admin user who last updated this subscription record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * All payments related to this subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'subscription_id')->orderBy('payment_date', 'desc');
    }

    /**
     * Scope query to active subscriptions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    /**
     * Scope query to filter by status.
     */
    public function scopeStatus(Builder $query, string|SubscriptionStatus $status): Builder
    {
        $value = $status instanceof SubscriptionStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    /**
     * Scope query to subscriptions for a specific store.
     */
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Check if the subscription is currently valid and active (considering dates and status).
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->status->isOperable()) {
            return false;
        }

        $today = Carbon::today();

        return $this->start_date <= $today && $this->end_date >= $today;
    }

    /**
     * Check if the subscription is currently in trial.
     */
    public function isCurrentlyInTrial(): bool
    {
        if ($this->status !== SubscriptionStatus::TRIAL) {
            return false;
        }

        if (! $this->trial_ends_at) {
            return false;
        }

        $today = Carbon::today();

        return $this->start_date <= $today && $this->trial_ends_at >= $today;
    }

    /**
     * Check if the subscription has expired (by status or end date passing).
     */
    public function isExpired(): bool
    {
        if ($this->status === SubscriptionStatus::EXPIRED) {
            return true;
        }

        if ($this->status->isOperable() && $this->end_date < Carbon::today()) {
            return true;
        }

        return false;
    }

    /**
     * Get the dynamically calculated effective status.
     */
    public function effectiveStatus(): SubscriptionStatus
    {
        if ($this->status->isOperable() && $this->end_date < Carbon::today()) {
            return SubscriptionStatus::EXPIRED;
        }

        return $this->status;
    }

    /**
     * Get the human label of the effective status.
     */
    public function effectiveStatusLabel(): string
    {
        return $this->effectiveStatus()->label();
    }

    /**
     * Get badge styling classes for effective status.
     */
    public function effectiveBadgeClasses(): string
    {
        return $this->effectiveStatus()->badgeClasses();
    }

    /**
     * Calculate days remaining until expiration.
     */
    public function daysRemaining(): int
    {
        $today = Carbon::today();
        if ($this->end_date < $today) {
            return 0;
        }

        return (int) $today->diffInDays($this->end_date, false);
    }
}
