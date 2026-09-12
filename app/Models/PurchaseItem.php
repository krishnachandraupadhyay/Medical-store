<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'batch_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'quantity',
        'free_quantity',
        'purchase_price',
        'mrp',
        'selling_price',
        'discount',
        'gst_rate',
        'tax_amount',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            'quantity' => 'integer',
            'free_quantity' => 'integer',
            'purchase_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_item_id');
    }

    public function totalQuantity(): int
    {
        return (int) ($this->quantity + $this->free_quantity);
    }

    public function alreadyReturnedQuantity(): int
    {
        return (int) $this->returnItems()
            ->whereHas('purchaseReturn', function ($q) {
                $q->where('status', ReturnStatus::COMPLETED);
            })
            ->sum('quantity');
    }

    public function returnableQuantity(): int
    {
        $purchasedQty = $this->totalQuantity();
        $returnedQty = $this->alreadyReturnedQuantity();
        $availableInBatch = $this->batch ? $this->batch->quantity : 0;

        return max(0, min($purchasedQty - $returnedQty, $availableInBatch));
    }
}
