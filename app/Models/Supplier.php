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
        'name',
        'company_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'email',
        'gst_number',
        'drug_license_no',
        'address',
        'city',
        'state',
        'pincode',
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
            'credit_limit' => 'float',
        ];
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
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('contact_person', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('gst_number', 'like', "%{$term}%");
        });
    }

    // ─── Financial Helpers ────────────────────────────────────────────────────

    public function totalPurchased(): float
    {
        return (float) $this->purchases()->where('status', 'completed')->sum('grand_total');
    }

    public function totalPaid(): float
    {
        return (float) $this->purchases()->where('status', 'completed')->sum('paid_amount');
    }

    public function outstandingAmount(): float
    {
        return max(0, $this->totalPurchased() - $this->totalPaid());
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
}
