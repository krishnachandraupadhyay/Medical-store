<?php

namespace App\Models;

use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'logo',
        'email',
        'mobile',
        'alternate_mobile',
        'address',
        'city',
        'state',
        'pincode',
        'gstin',
        'tax_number',
        'drug_license_no',
        'dl_number',
        'license_expiry_date',
        'store_type',
        'status',
        'settings',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StoreStatus::class,
            'license_expiry_date' => 'date',
            'settings' => 'array',
        ];
    }

    /**
     * Get all users assigned to this store.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'store_id');
    }

    /**
     * Get the store owners associated with this store.
     */
    public function owners(): HasMany
    {
        return $this->hasMany(User::class, 'store_id')->where('role', UserRole::STORE_OWNER);
    }

    /**
     * Get the staff members associated with this store.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'store_id')->where('role', UserRole::STORE_STAFF);
    }

    /**
     * Get custom and assigned roles for this store.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'store_id');
    }

    /**
     * Get the primary store owner (first active owner).
     */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'store_id')
            ->where('role', UserRole::STORE_OWNER)
            ->where('is_active', true);
    }

    /**
     * Get all subscriptions history for this store.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'store_id')->orderBy('id', 'desc');
    }

    /**
     * Get all payments for this store.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'store_id')->orderBy('payment_date', 'desc');
    }

    /**
     * Get all targeted notifications for this store.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'store_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get all medicines belonging to this store.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'store_id');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'store_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'store_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'store_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'store_id');
    }

    public function stockCounts(): HasMany
    {
        return $this->hasMany(StockCount::class, 'store_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'store_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'store_id');
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class, 'store_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'store_id');
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'store_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'store_id');
    }

    public function storePayments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'store_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'store_id');
    }

    /**
     * Get the currently active subscription for this store.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'store_id')
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->where('end_date', '>=', now()->toDateString())
            ->latestOfMany();
    }

    /**
     * Get the latest subscription record regardless of status.
     */
    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'store_id')->latestOfMany();
    }

    /**
     * Check if store is active.
     */
    public function isActive(): bool
    {
        return $this->status === StoreStatus::ACTIVE;
    }

    /**
     * Check if store is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === StoreStatus::SUSPENDED;
    }

    /**
     * Check if store is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === StoreStatus::INACTIVE;
    }

    /**
     * Generate a unique sequential store code (e.g. MED-000001).
     */
    public static function generateUniqueCode(): string
    {
        return DB::transaction(function () {
            $latestStore = self::withTrashed()
                ->orderByRaw('CAST(SUBSTRING(code, 5) AS UNSIGNED) DESC, id DESC')
                ->first();

            $nextNumber = 1;

            if ($latestStore && preg_match('/MED-(\d+)/', $latestStore->code, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            } else {
                $maxId = self::withTrashed()->max('id') ?? 0;
                $nextNumber = $maxId + 1;
            }

            do {
                $candidateCode = sprintf('MED-%06d', $nextNumber);
                $exists = self::withTrashed()->where('code', $candidateCode)->exists();
                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);

            return $candidateCode;
        });
    }

    /**
     * Get a store preference setting with fallback default.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];

        return $settings[$key] ?? $default;
    }

    /**
     * Get store invoice prefix (default: 'INV').
     */
    public function invoicePrefix(): string
    {
        return (string) $this->getSetting('invoice_prefix', 'INV');
    }

    /**
     * Get store timezone (default: 'Asia/Kolkata').
     */
    public function timezone(): string
    {
        return (string) $this->getSetting('timezone', 'Asia/Kolkata');
    }

    /**
     * Get store currency code (default: 'INR').
     */
    public function currency(): string
    {
        return (string) $this->getSetting('currency', 'INR');
    }

    /**
     * Get store date format (default: 'd-m-Y').
     */
    public function dateFormat(): string
    {
        return (string) $this->getSetting('date_format', 'd-m-Y');
    }

    /**
     * Accessor for tax_number mapping to gstin.
     */
    public function getTaxNumberAttribute(): ?string
    {
        return $this->attributes['gstin'] ?? null;
    }

    /**
     * Mutator for tax_number mapping to gstin.
     */
    public function setTaxNumberAttribute(?string $value): void
    {
        $this->attributes['gstin'] = $value;
    }

    /**
     * Accessor for dl_number mapping to drug_license_no.
     */
    public function getDlNumberAttribute(): ?string
    {
        return $this->attributes['drug_license_no'] ?? null;
    }

    /**
     * Mutator for dl_number mapping to drug_license_no.
     */
    public function setDlNumberAttribute(?string $value): void
    {
        $this->attributes['drug_license_no'] = $value;
    }
}
