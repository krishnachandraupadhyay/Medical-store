<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'invoice_number',
        'sale_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'payment_status',
        'payment_method',
        'notes',
        'hold_reference',
        'created_by',
        'updated_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'status' => SaleStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public static function generateInvoiceNumber(int $storeId): string
    {
        $yearPrefix = 'INV-'.date('Y').'-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('invoice_number', 'like', $yearPrefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->invoice_number, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $yearPrefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class, 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'sale_id');
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

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::COMPLETED);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('invoice_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_phone', 'like', "%{$term}%")
                ->orWhereHas('customer', function (Builder $cq) use ($term) {
                    $cq->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
        });
    }

    public function isDraft(): bool
    {
        return $this->status === SaleStatus::DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === SaleStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === SaleStatus::CANCELLED;
    }

    public function isHeld(): bool
    {
        return $this->status === SaleStatus::DRAFT && ! empty($this->hold_reference);
    }

    public function scopeHeld(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::DRAFT)
            ->whereNotNull('hold_reference');
    }

    public function outstandingAmount(): float
    {
        return max(0.00, round((float) $this->grand_total - (float) $this->paid_amount, 2));
    }

    public function updatePaymentStatus(): void
    {
        $paid = (float) $this->paid_amount;
        $total = (float) $this->grand_total;

        if ($paid <= 0) {
            $this->payment_status = PaymentStatus::UNPAID;
        } elseif ($paid >= $total) {
            $this->payment_status = PaymentStatus::PAID;
        } else {
            $this->payment_status = PaymentStatus::PARTIAL;
        }

        $this->save();
    }
}
