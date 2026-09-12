<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'sale_id',
        'customer_id',
        'return_number',
        'return_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'refund_amount',
        'adjustment_amount',
        'refund_method',
        'refund_status',
        'reason',
        'notes',
        'created_by',
        'updated_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'status' => ReturnStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public static function generateReturnNumber(int $storeId): string
    {
        $prefix = 'SR-'.date('Y').'-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('return_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->return_number, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
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
        return $query->where('store_id', $storeId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('return_number', 'like', "%{$term}%")
                ->orWhereHas('sale', function (Builder $sq) use ($term) {
                    $sq->where('invoice_number', 'like', "%{$term}%");
                })
                ->orWhereHas('customer', function (Builder $cq) use ($term) {
                    $cq->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
        });
    }

    public function isDraft(): bool
    {
        return $this->status === ReturnStatus::DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === ReturnStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === ReturnStatus::CANCELLED;
    }
}
