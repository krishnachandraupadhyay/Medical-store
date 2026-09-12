<?php

namespace App\Models;

use App\Enums\StockCountStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_counts';

    protected $fillable = [
        'store_id',
        'stock_count_number',
        'count_date',
        'status',
        'notes',
        'total_items',
        'matched_items',
        'discrepant_items',
        'total_variance_quantity',
        'total_variance_units',
        'total_variance_cost',
        'created_by',
        'approved_by',
        'completed_at',
    ];

    protected $casts = [
        'count_date' => 'date',
        'completed_at' => 'datetime',
        'total_items' => 'integer',
        'matched_items' => 'integer',
        'discrepant_items' => 'integer',
        'total_variance_quantity' => 'integer',
        'total_variance_cost' => 'decimal:2',
        'status' => StockCountStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (StockCount $stockCount) {
            if (empty($stockCount->stock_count_number)) {
                $stockCount->stock_count_number = static::generateStockCountNumber($stockCount->store_id);
            }
        });
    }

    public function getCountNumberAttribute(): ?string
    {
        return $this->stock_count_number;
    }

    public function setCountNumberAttribute(?string $value): void
    {
        $this->attributes['stock_count_number'] = $value;
    }

    public function getTotalVarianceUnitsAttribute(): int
    {
        return (int) ($this->total_variance_quantity ?? 0);
    }

    public function setTotalVarianceUnitsAttribute(int $value): void
    {
        $this->attributes['total_variance_quantity'] = $value;
    }

    public static function generateStockCountNumber(int $storeId): string
    {
        $prefix = 'SC-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('stock_count_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->stock_count_number, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class, 'stock_count_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function isDraft(): bool
    {
        return $this->status === StockCountStatus::DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === StockCountStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === StockCountStatus::CANCELLED;
    }
}
