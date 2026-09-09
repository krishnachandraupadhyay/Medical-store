<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'subscription_id',
        'subscription_plan_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'payment_date',
        'status',
        'notes',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_signature',
        'gateway_response',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'gateway_response' => 'array',
    ];

    /**
     * Generate a unique sequential transaction ID (e.g. TXN-20260909-0001).
     */
    public static function generateTransactionId(): string
    {
        $datePrefix = 'TXN-'.date('Ymd').'-';
        $latestPayment = static::withTrashed()
            ->where('transaction_id', 'like', $datePrefix.'%')
            ->orderByDesc('id')
            ->first();

        if ($latestPayment) {
            $lastNumber = (int) substr($latestPayment->transaction_id, strlen($datePrefix));
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $datePrefix.$nextNumber;
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === PaymentStatus::REFUNDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PaymentStatus::CANCELLED;
    }
}
