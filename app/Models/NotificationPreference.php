<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $table = 'notification_preferences';

    protected $fillable = [
        'store_id',
        'user_id',
        'notification_type',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isEnabled(int $storeId, ?int $userId, string $type): bool
    {
        $preference = static::where('store_id', $storeId)
            ->where(function ($query) use ($userId) {
                if ($userId !== null) {
                    $query->where('user_id', $userId)
                        ->orWhereNull('user_id');
                } else {
                    $query->whereNull('user_id');
                }
            })
            ->where('notification_type', $type)
            ->orderByDesc('user_id')
            ->first();

        return $preference ? (bool) $preference->is_enabled : true;
    }
}
