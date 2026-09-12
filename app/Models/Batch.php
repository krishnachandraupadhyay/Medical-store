<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'medicine_id',
        'batch_number',
        'barcode',
        'secondary_barcode',
        'manufacturing_date',
        'expiry_date',
        'purchase_price',
        'selling_price',
        'mrp',
        'quantity',
        'reserved_quantity',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'batch_id')->latest('id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'batch_id');
    }

    public function salesReturnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'batch_id');
    }

    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'batch_id');
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('batch_number', 'like', "%{$term}%")
                ->orWhereHas('medicine', function (Builder $mq) use ($term) {
                    $mq->where('name', 'like', "%{$term}%")
                        ->orWhere('generic_name', 'like', "%{$term}%")
                        ->orWhere('brand_name', 'like', "%{$term}%");
                });
        });
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $alertDays = 90): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->expiry_date->diffInDays(Carbon::today(), false) >= -$alertDays;
    }

    public function daysUntilExpiry(): int
    {
        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }

    public function expiryStatus(int $alertDays = 90): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isExpiringSoon($alertDays)) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function expiryBadgeClasses(int $alertDays = 90): string
    {
        return match ($this->expiryStatus($alertDays)) {
            'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
            'expiring_soon' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    public function expiryStatusLabel(int $alertDays = 90): string
    {
        return match ($this->expiryStatus($alertDays)) {
            'expired' => 'Expired',
            'expiring_soon' => 'Expiring Soon',
            default => 'Valid',
        };
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isDepleted(): bool
    {
        return $this->status === 'depleted' || $this->quantity <= 0;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canBeSold(): bool
    {
        return $this->isActive() && ! $this->isExpired() && $this->quantity > 0;
    }

    public function scopeByBarcode(Builder $query, string $barcode, int $storeId): Builder
    {
        return $query->where('store_id', $storeId)
            ->where(function (Builder $q) use ($barcode) {
                $q->where('barcode', $barcode)
                    ->orWhere('secondary_barcode', $barcode);
            });
    }

    public function scopeAvailableForSale(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', Carbon::today()->toDateString());
    }
}
