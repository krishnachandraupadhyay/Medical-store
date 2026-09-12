<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    public const BLOOD_GROUPS = [
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-',
    ];

    protected $fillable = [
        'store_id',
        'customer_code',
        'name',
        'phone',
        'alternate_phone',
        'email',
        'gender',
        'date_of_birth',
        'blood_group',
        'address',
        'city',
        'state',
        'pincode',
        'emergency_contact_name',
        'emergency_contact_phone',
        'tax_number',
        'doctor_name',
        'notes',
        'status',
        'loyalty_tier',
        'tags',
        'last_contacted_at',
        'visit_count',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'tags' => 'array',
            'last_contacted_at' => 'datetime',
            'visit_count' => 'integer',
        ];
    }

    // ─── Code Generator ───────────────────────────────────────────────────────

    public static function generateCustomerCode(int $storeId): string
    {
        $prefix = 'CUS-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('customer_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->customer_code, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    // ─── Loyalty Tier ─────────────────────────────────────────────────────────

    public function loyaltyTierLabel(): string
    {
        return match ($this->loyalty_tier) {
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
            default => 'Regular',
        };
    }

    public function loyaltyTierBadgeClass(): string
    {
        return match ($this->loyalty_tier) {
            'silver' => 'tier-silver',
            'gold' => 'tier-gold',
            'platinum' => 'tier-platinum',
            default => 'tier-regular',
        };
    }

    /**
     * Auto-calculate and update loyalty tier based on total purchases.
     */
    public function recalculateLoyaltyTier(): void
    {
        $total = $this->totalPurchases();

        $tier = match (true) {
            $total >= 100000 => 'platinum',
            $total >= 50000 => 'gold',
            $total >= 10000 => 'silver',
            default => 'regular',
        };

        if ($this->loyalty_tier !== $tier) {
            $this->updateQuietly(['loyalty_tier' => $tier]);
        }
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'customer_id');
    }

    public function contactNotes(): MorphMany
    {
        return $this->morphMany(ContactNote::class, 'notable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function toggleStatus(): void
    {
        $this->status = $this->isActive() ? 'inactive' : 'active';
        $this->save();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeWithOutstanding(Builder $query): Builder
    {
        return $query->whereHas('sales', function (Builder $sq) {
            $sq->where('status', 'completed')
                ->whereRaw('grand_total > paid_amount');
        });
    }

    public function scopeCleared(Builder $query): Builder
    {
        return $query->whereDoesntHave('sales', function (Builder $sq) {
            $sq->where('status', 'completed')
                ->whereRaw('grand_total > paid_amount');
        });
    }

    public function scopeBloodGroup(Builder $query, ?string $bloodGroup): Builder
    {
        if (! $bloodGroup) {
            return $query;
        }

        return $query->where('blood_group', $bloodGroup);
    }

    public function scopeCity(Builder $query, ?string $city): Builder
    {
        if (! $city) {
            return $query;
        }

        return $query->where('city', $city);
    }

    public function scopeGender(Builder $query, ?string $gender): Builder
    {
        if (! $gender) {
            return $query;
        }

        return $query->where('gender', $gender);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('alternate_phone', 'like', "%{$term}%")
                ->orWhere('customer_code', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('doctor_name', 'like', "%{$term}%")
                ->orWhere('emergency_contact_name', 'like', "%{$term}%");
        });
    }

    // ─── Financial Helpers ────────────────────────────────────────────────────

    public function totalPurchases(): float
    {
        return (float) $this->sales()->where('status', 'completed')->sum('grand_total');
    }

    public function totalPaid(): float
    {
        return (float) $this->sales()->where('status', 'completed')->sum('paid_amount');
    }

    public function outstandingAmount(): float
    {
        return max(0, $this->totalPurchases() - $this->totalPaid());
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->outstandingAmount();
    }

    public function salesCount(): int
    {
        return $this->sales()->where('status', 'completed')->count();
    }

    public function lastSaleDate(): ?string
    {
        $sale = $this->sales()->where('status', 'completed')->latest('sale_date')->first();

        return $sale ? $sale->sale_date->format('d M Y') : null;
    }
}
