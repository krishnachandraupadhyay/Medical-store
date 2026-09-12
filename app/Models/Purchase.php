<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'supplier_id',
        'invoice_number',
        'purchase_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'payment_status',
        'notes',
        'created_by',
        'updated_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'status' => PurchaseStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'purchase_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'purchase_id');
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
            $q->where('invoice_number', 'like', "%{$term}%")
                ->orWhereHas('supplier', function (Builder $sq) use ($term) {
                    $sq->where('name', 'like', "%{$term}%")
                        ->orWhere('company_name', 'like', "%{$term}%");
                });
        });
    }

    public function isDraft(): bool
    {
        return $this->status === PurchaseStatus::DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === PurchaseStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PurchaseStatus::CANCELLED;
    }

    public function outstandingAmount(): float
    {
        $returned = $this->totalReturned();

        return max(0.00, round((float) $this->grand_total - (float) $this->paid_amount - $returned, 2));
    }

    public function totalReturned(): float
    {
        return (float) $this->returns()
            ->where('status', 'completed')
            ->sum('grand_total');
    }

    public function isReturnable(): bool
    {
        if (! $this->isCompleted()) {
            return false;
        }

        return $this->items->some(fn ($item) => $item->returnableQuantity() > 0);
    }

    public function updatePaymentStatus(): void
    {
        $paid = (float) $this->paid_amount;
        $returned = $this->totalReturned();
        $total = (float) $this->grand_total;

        if (($paid + $returned) <= 0) {
            $this->payment_status = PaymentStatus::UNPAID;
        } elseif (($paid + $returned) >= $total) {
            $this->payment_status = PaymentStatus::PAID;
        } else {
            $this->payment_status = PaymentStatus::PARTIAL;
        }

        $this->save();
    }
}
