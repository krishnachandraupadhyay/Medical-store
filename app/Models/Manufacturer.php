<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'status',
    ];

    /**
     * Store this custom manufacturer belongs to (null if global system manufacturer).
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Medicines produced by this manufacturer.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'manufacturer_id');
    }

    /**
     * Scope to active manufacturers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to manufacturers accessible by a given store (global + store-owned).
     */
    public function scopeForStore(Builder $query, ?int $storeId = null): Builder
    {
        return $query->where(function ($q) use ($storeId) {
            $q->whereNull('store_id');
            if ($storeId) {
                $q->orWhere('store_id', $storeId);
            }
        });
    }
}
