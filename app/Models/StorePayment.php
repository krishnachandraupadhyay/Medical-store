<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\StorePaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'payment_number',
        'type',
        'sale_id',
        'purchase_id',
        'expense_id',
        'customer_id',
        'supplier_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'type' => StorePaymentType::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public static function generatePaymentNumber(int $storeId): string
    {
        $prefix = 'PAY-'.date('Y').'-';
        $latest = static::withTrashed()
            ->where('store_id', $storeId)
            ->where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->payment_number, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
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
        return $query->where('status', 'completed');
    }

    public function partyName(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }
        if ($this->supplier) {
            return $this->supplier->name;
        }
        if ($this->sale && $this->sale->customer_name) {
            return $this->sale->customer_name;
        }
        if ($this->expense) {
            return $this->expense->title;
        }

        return 'Direct';
    }

    public function referenceDocument(): string
    {
        if ($this->sale) {
            return 'Invoice #'.$this->sale->invoice_number;
        }
        if ($this->purchase) {
            return 'Bill #'.$this->purchase->invoice_number;
        }
        if ($this->expense) {
            return 'Exp #'.$this->expense->expense_number;
        }

        return 'N/A';
    }
}
