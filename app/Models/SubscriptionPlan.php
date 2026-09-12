<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'status',
        'limits',
        'features',
        'is_popular',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'billing_cycle' => BillingCycle::class,
            'status' => PlanStatus::class,
            'trial_days' => 'integer',
            'limits' => 'array',
            'features' => 'array',
            'is_popular' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Dictionary of all supported platform features.
     */
    public static function supportedFeatures(): array
    {
        return [
            'medicine_management' => [
                'name' => 'Medicine Master Management',
                'description' => 'Medicine master catalog, categories, formulations, drug strengths, and HSN codes.',
            ],
            'inventory_management' => [
                'name' => 'Inventory Management',
                'description' => 'Real-time medicine stock tracking, batch numbers, expiry alerts, and stock adjustments.',
            ],
            'advanced_inventory_control' => [
                'name' => 'Advanced Inventory Control',
                'description' => 'Stock reconciliation, physical audits, loss/damage write-offs, batch blocking, and inventory valuation.',
            ],
            'purchase_management' => [
                'name' => 'Purchase Management',
                'description' => 'Supplier purchase orders, GRN receiving, vendor bills, and supplier ledger.',
            ],
            'supplier_management' => [
                'name' => 'Supplier Management',
                'description' => 'Supplier directory, drug license tracking, vendor payment balances, and supplier profiles.',
            ],
            'customer_management' => [
                'name' => 'Customer Management',
                'description' => 'Customer directory, purchase history, prescription records, and credit tracking.',
            ],
            'pos' => [
                'name' => 'Point of Sale (POS)',
                'description' => 'Barcode scanner support, shortcut POS terminal, thermal receipt printing.',
            ],
            'sales_management' => [
                'name' => 'Sales & Billing',
                'description' => 'Fast billing checkout, discount rules, GST tax calculation, and digital invoices.',
            ],
            'sales_return' => [
                'name' => 'Sales Return',
                'description' => 'Customer medicine returns, batch re-stocking, and credit note issuance.',
            ],
            'purchase_return' => [
                'name' => 'Purchase Return',
                'description' => 'Damaged/expired stock return to supplier and vendor debit notes.',
            ],
            'staff_management' => [
                'name' => 'Staff Management',
                'description' => 'Pharmacist and cashier role assignments, shift tracking, and staff activity audits.',
            ],
            'expense_management' => [
                'name' => 'Expense Management',
                'description' => 'Pharmacy store operating expenses, utilities, petty cash, and expense category records.',
            ],
            'reports' => [
                'name' => 'Reports & Analytics',
                'description' => 'Daily sales summaries, profit/loss overview, and tax GST return reports.',
            ],
            'advanced_reports' => [
                'name' => 'Advanced Analytics & AI Forecasting',
                'description' => 'Medicine demand prediction, dead-stock warnings, and multi-branch comparative graphs.',
            ],
        ];
    }

    /**
     * Dictionary of all supported limit definitions.
     */
    public static function supportedLimits(): array
    {
        return [
            'max_staff' => [
                'label' => 'Maximum Staff Members',
                'description' => 'Staff & cashier user accounts per store (-1 for unlimited)',
                'default' => 5,
            ],
            'max_medicines' => [
                'label' => 'Maximum Medicine Products',
                'description' => 'Catalog item capacity in medicine master (-1 for unlimited)',
                'default' => 500,
            ],
            'max_invoices' => [
                'label' => 'Monthly Invoices Limit',
                'description' => 'Max sales invoices processed per month (-1 for unlimited)',
                'default' => 1000,
            ],
            'max_customers' => [
                'label' => 'Maximum Customer Records',
                'description' => 'Patient & customer profiles stored (-1 for unlimited)',
                'default' => 500,
            ],
            'max_suppliers' => [
                'label' => 'Maximum Suppliers',
                'description' => 'Supplier profiles stored per store (-1 for unlimited)',
                'default' => 50,
            ],
            'max_storage' => [
                'label' => 'Document & File Storage (MB)',
                'description' => 'Storage limit for invoices and attachments in MB (-1 for unlimited)',
                'default' => 1024,
            ],
        ];
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PlanStatus::ACTIVE);
    }

    /**
     * Scope a query to filter by billing cycle.
     */
    public function scopeBillingCycle(Builder $query, string|BillingCycle $cycle): Builder
    {
        $value = $cycle instanceof BillingCycle ? $cycle->value : $cycle;

        return $query->where('billing_cycle', $value);
    }

    /**
     * User who created the plan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated the plan.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all store subscriptions under this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }

    /**
     * Get all payments under this subscription plan.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'subscription_plan_id');
    }

    /**
     * Check if a specific feature is enabled in this plan.
     */
    public function hasFeature(string $featureKey): bool
    {
        $features = $this->features ?? [];

        return in_array($featureKey, $features, true);
    }

    /**
     * Get numeric limit for a key. Returns -1 for unlimited.
     */
    public function getLimit(string $limitKey, int $default = -1): int
    {
        $limits = $this->limits ?? [];
        if (! isset($limits[$limitKey]) || $limits[$limitKey] === null || $limits[$limitKey] === '') {
            return $default;
        }

        return (int) $limits[$limitKey];
    }

    /**
     * Check if a limit is unlimited (-1).
     */
    public function isUnlimited(string $limitKey): bool
    {
        return $this->getLimit($limitKey) === -1;
    }

    /**
     * Formatted limit string for display.
     */
    public function displayLimit(string $limitKey): string
    {
        $limit = $this->getLimit($limitKey);

        return $limit === -1 ? 'Unlimited' : number_format($limit);
    }

    /**
     * Get formatted price with currency symbol.
     */
    public function formattedPrice(): string
    {
        if ((float) $this->price === 0.0) {
            return 'Free';
        }

        return '₹'.number_format((float) $this->price, 2);
    }

    /**
     * Check if plan is active.
     */
    public function isActive(): bool
    {
        return $this->status === PlanStatus::ACTIVE;
    }

    /**
     * Automatically generate unique slug when setting name if slug is empty.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
