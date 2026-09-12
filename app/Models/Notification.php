<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'target_type',
        'store_id',
        'user_id',
        'status',
        'read_at',
        'scheduled_at',
        'sent_at',
        'metadata',
        'reference_type',
        'reference_id',
        'action_url',
        'alert_key',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'priority' => NotificationPriority::class,
        'target_type' => NotificationTargetType::class,
        'status' => NotificationStatus::class,
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where(function (Builder $q) use ($storeId) {
            $q->where('store_id', $storeId)
                ->orWhere('target_type', NotificationTargetType::ALL_STORES);
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        if ($this->read_at) {
            $this->update(['read_at' => null]);
        }
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function isDraft(): bool
    {
        return $this->status === NotificationStatus::DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === NotificationStatus::SCHEDULED;
    }

    public function isSent(): bool
    {
        return $this->status === NotificationStatus::SENT;
    }

    public function isCancelled(): bool
    {
        return $this->status === NotificationStatus::CANCELLED;
    }

    public function canBeEdited(): bool
    {
        return ! $this->isSent();
    }

    public function canBeCancelled(): bool
    {
        return $this->isScheduled();
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isSent();
    }
}
