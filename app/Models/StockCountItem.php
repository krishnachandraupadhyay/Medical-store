<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    use HasFactory;

    protected $table = 'stock_count_items';

    protected $fillable = [
        'stock_count_id',
        'store_id',
        'medicine_id',
        'batch_id',
        'system_quantity',
        'physical_quantity',
        'variance_quantity',
        'unit_cost',
        'variance_cost',
        'adjustment_type',
        'reason',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'physical_quantity' => 'integer',
        'variance_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'variance_cost' => 'decimal:2',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class, 'stock_count_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function isMatch(): bool
    {
        return $this->variance_quantity === 0;
    }

    public function isSurplus(): bool
    {
        return $this->variance_quantity > 0;
    }

    public function isShortage(): bool
    {
        return $this->variance_quantity < 0;
    }

    public function getVarianceAttribute(): int
    {
        return (int) ($this->variance_quantity ?? 0);
    }

    public function setVarianceAttribute(int $value): void
    {
        $this->attributes['variance_quantity'] = $value;
    }

    public function getUnitCostAttribute(): float
    {
        return isset($this->attributes['unit_cost'])
            ? (float) $this->attributes['unit_cost']
            : (float) ($this->batch?->purchase_price ?? 0.00);
    }

    public function getVarianceCostAttribute(): float
    {
        return isset($this->attributes['variance_cost'])
            ? (float) $this->attributes['variance_cost']
            : round($this->variance * $this->unit_cost, 2);
    }
}
