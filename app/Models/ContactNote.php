<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'notable_type',
        'notable_id',
        'type',
        'subject',
        'body',
        'follow_up_date',
        'follow_up_done',
        'follow_up_done_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
            'follow_up_done' => 'boolean',
            'follow_up_done_at' => 'datetime',
        ];
    }

    // ─── Type Labels ──────────────────────────────────────────────────────────

    public function typeLabel(): string
    {
        return match ($this->type) {
            'note' => 'Note',
            'call' => 'Phone Call',
            'meeting' => 'Meeting',
            'email' => 'Email',
            'followup' => 'Follow-up',
            default => ucfirst($this->type),
        };
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'note' => '📝',
            'call' => '📞',
            'meeting' => '🤝',
            'email' => '📧',
            'followup' => '📅',
            default => '💬',
        };
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            'note' => 'badge-note',
            'call' => 'badge-call',
            'meeting' => 'badge-meeting',
            'email' => 'badge-email',
            'followup' => 'badge-followup',
            default => 'badge-note',
        };
    }

    // ─── State Helpers ────────────────────────────────────────────────────────

    public function hasFollowUp(): bool
    {
        return ! is_null($this->follow_up_date);
    }

    public function isFollowUpOverdue(): bool
    {
        return $this->hasFollowUp()
            && ! $this->follow_up_done
            && $this->follow_up_date->isPast();
    }

    public function isFollowUpDueToday(): bool
    {
        return $this->hasFollowUp()
            && ! $this->follow_up_done
            && $this->follow_up_date->isToday();
    }

    public function followUpStatusBadge(): string
    {
        if ($this->follow_up_done) {
            return 'done';
        }
        if ($this->isFollowUpOverdue()) {
            return 'overdue';
        }
        if ($this->isFollowUpDueToday()) {
            return 'today';
        }

        return 'upcoming';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function scopePendingFollowUps(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_date')
            ->where('follow_up_done', false);
    }

    public function scopeOverdueFollowUps(Builder $query): Builder
    {
        return $query->pendingFollowUps()
            ->where('follow_up_date', '<', now()->toDateString());
    }
}
