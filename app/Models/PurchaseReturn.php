<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'purchase_id',
        'supplier_id',
        'return_number',
        'return_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
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
            'completed_at' => 'datetime',
        ];
    }

    public static function generateReturnNumber(int $storeId): string
    {
        $prefix = 'PR-'.date('Y').'-';
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

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id');
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
                ->orWhereHas('purchase', function (Builder $pq) use ($term) {
                    $pq->where('invoice_number', 'like', "%{$term}%");
                })
                ->orWhereHas('supplier', function (Builder $sq) use ($term) {
                    $sq->where('name', 'like', "%{$term}%");
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
