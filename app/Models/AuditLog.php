<?php

namespace App\Models;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'module' => AuditModule::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The administrative user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the target entity (subject) that was acted upon.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get human-readable subject name/identifier.
     */
    public function getSubjectDisplayNameAttribute(): string
    {
        if (! $this->subject_type) {
            return 'System';
        }

        $baseName = class_basename($this->subject_type);

        if ($this->subject) {
            if (isset($this->subject->name)) {
                return "{$baseName}: {$this->subject->name}";
            }
            if (isset($this->subject->title)) {
                return "{$baseName}: {$this->subject->title}";
            }
            if (isset($this->subject->code)) {
                return "{$baseName}: {$this->subject->code}";
            }
            if (isset($this->subject->transaction_id)) {
                return "{$baseName}: {$this->subject->transaction_id}";
            }
        }

        return "{$baseName} #{$this->subject_id}";
    }

    /**
     * Compute field-level diffs between old and new values.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function getDiffsAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $diffs = [];

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $diffs[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        return $diffs;
    }
}
