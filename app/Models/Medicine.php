<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'generic_name',
        'brand_name',
        'category_id',
        'manufacturer_id',
        'dosage_form_id',
        'unit_id',
        'strength',
        'pack_size',
        'prescription_required',
        'hsn_code',
        'gst_rate',
        'reorder_level',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'prescription_required' => 'boolean',
            'gst_rate' => 'decimal:2',
            'reorder_level' => 'integer',
        ];
    }

    /**
     * Store this medicine belongs to (Tenant isolation).
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Therapeutic category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Manufacturing pharmaceutical company.
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Pharmaceutical dosage form (Tablet, Syrup, etc.).
     */
    public function dosageForm(): BelongsTo
    {
        return $this->belongsTo(DosageForm::class, 'dosage_form_id');
    }

    /**
     * Packaging unit of measurement (Strip, Bottle, Box).
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * User who created this medicine master record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated this medicine master record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'medicine_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'medicine_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'medicine_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'medicine_id');
    }

    public function salesReturnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'medicine_id');
    }

    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'medicine_id');
    }

    public function totalStock(): int
    {
        return (int) $this->batches()->active()->sum('quantity');
    }

    /**
     * Scope to only medicines for a specific store.
     */
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to active medicines.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to search by medicine name, generic name, or brand name.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('generic_name', 'like', "%{$term}%")
                ->orWhere('brand_name', 'like', "%{$term}%");
        });
    }

    /**
     * Check if medicine is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if prescription is required for dispensing.
     */
    public function requiresPrescription(): bool
    {
        return (bool) $this->prescription_required;
    }

    /**
     * Formatted display title with strength.
     */
    public function displayName(): string
    {
        $parts = [$this->name];

        if ($this->strength) {
            $parts[] = "({$this->strength})";
        }

        return implode(' ', $parts);
    }
}
