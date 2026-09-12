<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Models\Batch;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    public function __construct(
        protected OutstandingService $outstandingService
    ) {}

    /**
     * Resolve date range from request parameters.
     */
    public function resolveDateRange(array $params): array
    {
        $preset = $params['period'] ?? 'this_month';
        $today = Carbon::today();

        switch ($preset) {
            case 'today':
                $start = $today->copy()->startOfDay();
                $end = $today->copy()->endOfDay();
                $label = 'Today';
                break;
            case 'yesterday':
                $start = $today->copy()->subDay()->startOfDay();
                $end = $today->copy()->subDay()->endOfDay();
                $label = 'Yesterday';
                break;
            case 'this_week':
                $start = $today->copy()->startOfWeek();
                $end = $today->copy()->endOfWeek();
                $label = 'This Week';
                break;
            case 'last_month':
                $start = $today->copy()->subMonth()->startOfMonth();
                $end = $today->copy()->subMonth()->endOfMonth();
                $label = 'Last Month';
                break;
            case 'this_year':
                $start = $today->copy()->startOfYear();
                $end = $today->copy()->endOfYear();
                $label = 'This Year';
                break;
            case 'custom':
                if (! empty($params['from_date']) && ! empty($params['to_date'])) {
                    $start = Carbon::parse($params['from_date'])->startOfDay();
                    $end = Carbon::parse($params['to_date'])->endOfDay();
                    if ($start->gt($end)) {
                        $temp = $start;
                        $start = $end->copy()->startOfDay();
                        $end = $temp->copy()->endOfDay();
                    }
                    $label = $start->format('d M Y').' - '.$end->format('d M Y');
                    break;
                }
                // Fallback to this month
            case 'this_month':
            default:
                $preset = 'this_month';
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                $label = 'This Month';
                break;
        }

        return [
            'preset' => $preset,
            'start' => $start,
            'end' => $end,
            'from_date' => $start->toDateString(),
            'to_date' => $end->toDateString(),
            'label' => $label,
        ];
    }

    /**
     * Get dashboard summary cards.
     */
    public function getSummaryCards(Store $store, array $range): array
    {
        $startDate = $range['from_date'];
        $endDate = $range['to_date'];

        // 1. Sales
        $totalSales = (float) Sale::where('store_id', $store->id)
            ->where('status', SaleStatus::COMPLETED)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('grand_total');

        $completedSalesCount = Sale::where('store_id', $store->id)
            ->where('status', SaleStatus::COMPLETED)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();

        // 2. Purchases
        $totalPurchases = (float) Purchase::where('store_id', $store->id)
            ->where('status', PurchaseStatus::COMPLETED)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('grand_total');

        $completedPurchasesCount = Purchase::where('store_id', $store->id)
            ->where('status', PurchaseStatus::COMPLETED)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->count();

        // 3. Sales Returns
        $totalSalesReturns = (float) SalesReturn::where('store_id', $store->id)
            ->where('status', ReturnStatus::COMPLETED)
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('grand_total');

        // 4. Purchase Returns
        $totalPurchaseReturns = (float) PurchaseReturn::where('store_id', $store->id)
            ->where('status', ReturnStatus::COMPLETED)
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('grand_total');

        // 5. Expenses
        $totalExpenses = (float) Expense::where('store_id', $store->id)
            ->where('status', ExpenseStatus::PAID)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // 6. Outstanding (Current balances)
        $customerOutstanding = $this->outstandingService->getTotalCustomerOutstanding($store);
        $supplierOutstanding = $this->outstandingService->getTotalSupplierOutstanding($store);

        // 7. Neutral difference (NOT Net Profit)
        $activityDifference = round($totalSales - $totalPurchases - $totalExpenses, 2);

        return [
            'total_sales' => $totalSales,
            'completed_sales_count' => $completedSalesCount,
            'total_purchases' => $totalPurchases,
            'completed_purchases_count' => $completedPurchasesCount,
            'total_sales_returns' => $totalSalesReturns,
            'total_purchase_returns' => $totalPurchaseReturns,
            'total_expenses' => $totalExpenses,
            'customer_outstanding' => $customerOutstanding,
            'supplier_outstanding' => $supplierOutstanding,
            'receivables' => $customerOutstanding,
            'payables' => $supplierOutstanding,
            'activity_difference' => $activityDifference,
        ];
    }

    /**
     * Get Sales vs Purchase daily trend data.
     */
    public function getTrends(Store $store, array $range): array
    {
        $startDate = $range['from_date'];
        $endDate = $range['to_date'];

        $salesTrend = DB::table('sales')
            ->where('store_id', $store->id)
            ->where('status', SaleStatus::COMPLETED->value)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->groupBy('sale_date')
            ->select('sale_date', DB::raw('SUM(grand_total) as total'))
            ->pluck('total', 'sale_date')
            ->toArray();

        $purchaseTrend = DB::table('purchases')
            ->where('store_id', $store->id)
            ->where('status', PurchaseStatus::COMPLETED->value)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->groupBy('purchase_date')
            ->select('purchase_date', DB::raw('SUM(grand_total) as total'))
            ->pluck('total', 'purchase_date')
            ->toArray();

        $expenseTrend = DB::table('expenses')
            ->where('store_id', $store->id)
            ->where('status', ExpenseStatus::PAID->value)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->groupBy('expense_date')
            ->select('expense_date', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'expense_date')
            ->toArray();

        // Build continuous date array
        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate);

        $labels = [];
        $salesData = [];
        $purchasesData = [];
        $expensesData = [];

        // Cap chart data points to 60 days max for clarity
        $diffDays = $periodStart->diffInDays($periodEnd);
        $step = max(1, (int) ceil($diffDays / 45));

        $curr = $periodStart->copy();
        while ($curr->lte($periodEnd)) {
            $dateStr = $curr->toDateString();
            $labels[] = $curr->format('d M');
            $salesData[] = (float) ($salesTrend[$dateStr] ?? 0.00);
            $purchasesData[] = (float) ($purchaseTrend[$dateStr] ?? 0.00);
            $expensesData[] = (float) ($expenseTrend[$dateStr] ?? 0.00);
            $curr->addDays($step);
        }

        return [
            'labels' => $labels,
            'sales' => $salesData,
            'purchases' => $purchasesData,
            'expenses' => $expensesData,
        ];
    }

    /**
     * Get top selling medicines in the date range.
     */
    public function getTopSellingMedicines(Store $store, array $range, int $limit = 5): array
    {
        $startDate = $range['from_date'];
        $endDate = $range['to_date'];

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
            ->where('sales.store_id', $store->id)
            ->where('sales.status', SaleStatus::COMPLETED->value)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereNull('sales.deleted_at')
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength')
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.strength',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_value')
            )
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get low stock medicines (stock <= reorder_level).
     */
    public function getLowStockMedicines(Store $store, int $limit = 5): array
    {
        return DB::table('medicines')
            ->leftJoin('batches', function ($join) {
                $join->on('medicines.id', '=', 'batches.medicine_id')
                    ->where('batches.status', '=', 'active')
                    ->whereNull('batches.deleted_at');
            })
            ->where('medicines.store_id', $store->id)
            ->where('medicines.status', 'active')
            ->whereNull('medicines.deleted_at')
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength', 'medicines.reorder_level')
            ->havingRaw('COALESCE(SUM(batches.quantity), 0) <= medicines.reorder_level AND medicines.reorder_level > 0')
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.strength',
                'medicines.reorder_level',
                DB::raw('COALESCE(SUM(batches.quantity), 0) as current_stock')
            )
            ->orderBy('current_stock')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get out of stock medicines (stock == 0).
     */
    public function getOutOfStockMedicines(Store $store, int $limit = 5): array
    {
        return DB::table('medicines')
            ->leftJoin('batches', function ($join) {
                $join->on('medicines.id', '=', 'batches.medicine_id')
                    ->where('batches.status', '=', 'active')
                    ->whereNull('batches.deleted_at');
            })
            ->where('medicines.store_id', $store->id)
            ->where('medicines.status', 'active')
            ->whereNull('medicines.deleted_at')
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength')
            ->havingRaw('COALESCE(SUM(batches.quantity), 0) = 0')
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.strength',
                DB::raw('0 as current_stock')
            )
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get expiry summary and alert lists.
     */
    public function getExpirySummary(Store $store, int $limit = 5): array
    {
        $today = Carbon::today()->toDateString();
        $soonThreshold = Carbon::today()->addDays(90)->toDateString();

        $expiredCount = Batch::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('expiry_date', '<', $today)
            ->where('quantity', '>', 0)
            ->count();

        $expiringSoonCount = Batch::where('store_id', $store->id)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [$today, $soonThreshold])
            ->where('quantity', '>', 0)
            ->count();

        $expiringBatches = Batch::with('medicine')
            ->where('store_id', $store->id)
            ->where('status', 'active')
            ->where('expiry_date', '<=', $soonThreshold)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->limit($limit)
            ->get();

        return [
            'expired_count' => $expiredCount,
            'expiring_soon_count' => $expiringSoonCount,
            'expiring_batches' => $expiringBatches,
        ];
    }

    /**
     * Operational payments breakdown by payment method for the date range.
     */
    public function getPaymentMethodSummary(Store $store, array $range): array
    {
        $startDate = $range['from_date'];
        $endDate = $range['to_date'];

        return DB::table('store_payments')
            ->where('store_id', $store->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->groupBy('payment_method')
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as count'))
            ->orderByDesc('total_amount')
            ->get()
            ->toArray();
    }

    /**
     * Get combined recent transactions (Sales, Purchases, Returns, Expenses, Payments).
     */
    public function getRecentTransactions(Store $store, int $limit = 10): array
    {
        $sales = Sale::where('store_id', $store->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($s) => [
                'type' => 'Sale',
                'type_badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'reference' => $s->invoice_number,
                'entity' => $s->customer_name ?: 'Walk-in Customer',
                'amount' => $s->grand_total,
                'date' => $s->sale_date->format('d M Y'),
                'status' => ucfirst($s->status->value),
                'status_badge' => $s->isCompleted() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                'raw_date' => $s->created_at,
                'url' => route('store.sales.show', $s->id),
            ]);

        $purchases = Purchase::with('supplier')
            ->where('store_id', $store->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'type' => 'Purchase',
                'type_badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                'reference' => $p->invoice_number,
                'entity' => $p->supplier?->name ?: 'Supplier',
                'amount' => $p->grand_total,
                'date' => $p->purchase_date->format('d M Y'),
                'status' => ucfirst($p->status->value),
                'status_badge' => $p->isCompleted() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                'raw_date' => $p->created_at,
                'url' => route('store.purchases.show', $p->id),
            ]);

        $expenses = Expense::where('store_id', $store->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($e) => [
                'type' => 'Expense',
                'type_badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'reference' => $e->expense_number,
                'entity' => $e->title,
                'amount' => $e->amount,
                'date' => $e->expense_date->format('d M Y'),
                'status' => ucfirst($e->status->value),
                'status_badge' => $e->isPaid() ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-700',
                'raw_date' => $e->created_at,
                'url' => route('store.expenses.show', $e->id),
            ]);

        return $sales->concat($purchases)->concat($expenses)
            ->sortByDesc('raw_date')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
