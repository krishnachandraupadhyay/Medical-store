<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'expense_category_id',
        'expense_number',
        'expense_date',
        'title',
        'description',
        'amount',
        'payment_method',
        'reference_number',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'status' => ExpenseStatus::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public static function generateExpenseNumber(int $storeId): string
    {
        $prefix = 'EXP-'.date('Y').'-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('expense_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->expense_number, $matches)) {
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'expense_id');
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
            $q->where('expense_number', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('category', function (Builder $cq) use ($term) {
                    $cq->where('name', 'like', "%{$term}%");
                });
        });
    }

    public function isPaid(): bool
    {
        return $this->status === ExpenseStatus::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === ExpenseStatus::CANCELLED;
    }

    public function isDraft(): bool
    {
        return $this->status === ExpenseStatus::DRAFT;
    }
}
