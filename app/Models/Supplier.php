<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'supplier_code',
        'name',
        'company_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'email',
        'gst_number',
        'pan_number',
        'drug_license_no',
        'address',
        'city',
        'state',
        'pincode',
        'opening_balance',
        'opening_balance_type',
        'status',
        'notes',
        'tags',
        'last_contacted_at',
        'credit_limit',
        'payment_terms',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'last_contacted_at' => 'datetime',
            'credit_limit' => 'decimal:2',
            'opening_balance' => 'decimal:2',
        ];
    }

    public static function generateSupplierCode(int $storeId): string
    {
        $prefix = 'SUP-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('supplier_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->supplier_code, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'supplier_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'supplier_id');
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
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('supplier_code', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('contact_person', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('alternate_phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('gst_number', 'like', "%{$term}%")
                ->orWhere('pan_number', 'like', "%{$term}%");
        });
    }

    // ─── Financial Helpers ────────────────────────────────────────────────────

    public function openingBalancePayable(): float
    {
        $amount = (float) ($this->opening_balance ?? 0.00);
        if ($this->opening_balance_type === 'advance') {
            return -$amount;
        }

        return $amount;
    }

    public function totalPurchased(): float
    {
        return (float) $this->purchases()->where('status', 'completed')->sum('grand_total');
    }

    public function totalPaid(): float
    {
        $purchasePaid = (float) $this->purchases()->where('status', 'completed')->sum('paid_amount');
        $directPaid = (float) $this->payments()->whereNull('purchase_id')->where(function ($q) {
            $q->where('status', 'completed')->orWhereNull('status');
        })->sum('amount');

        return round($purchasePaid + $directPaid, 2);
    }

    public function totalReturned(): float
    {
        return (float) $this->purchaseReturns()->where('status', 'completed')->sum('grand_total');
    }

    public function outstandingAmount(): float
    {
        $opening = $this->openingBalancePayable();
        $purchased = $this->totalPurchased();
        $returned = $this->totalReturned();
        $paid = $this->totalPaid();

        return round($opening + $purchased - $returned - $paid, 2);
    }

    public function availableCredit(): ?float
    {
        if ($this->credit_limit === null || (float) $this->credit_limit <= 0) {
            return null;
        }

        return round(max(0.00, (float) $this->credit_limit - $this->outstandingAmount()), 2);
    }

    public function purchasesCount(): int
    {
        return $this->purchases()->where('status', 'completed')->count();
    }

    public function lastPurchaseDate(): ?string
    {
        $purchase = $this->purchases()->where('status', 'completed')->latest('purchase_date')->first();

        return $purchase ? $purchase->purchase_date->format('d M Y') : null;
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
}
