<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\SaleStatus;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OutstandingService
{
    /**
     * Get total customer receivables across all completed sales for a store.
     */
    public function getTotalCustomerOutstanding(Store $store): float
    {
        return (float) Sale::where('store_id', $store->id)
            ->where('status', SaleStatus::COMPLETED)
            ->selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as total')
            ->value('total');
    }

    /**
     * Get total supplier payables across all completed purchases for a store.
     */
    public function getTotalSupplierOutstanding(Store $store): float
    {
        return (float) Purchase::where('store_id', $store->id)
            ->where('status', PurchaseStatus::COMPLETED)
            ->selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as total')
            ->value('total');
    }

    /**
     * Get top outstanding customers (for dashboard or summary).
     */
    public function getTopCustomerOutstanding(Store $store, int $limit = 5): array
    {
        return DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.store_id', $store->id)
            ->where('sales.status', SaleStatus::COMPLETED->value)
            ->whereNull('sales.deleted_at')
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->havingRaw('SUM(sales.grand_total - sales.paid_amount) > 0')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                DB::raw('SUM(sales.grand_total) as total_sales'),
                DB::raw('SUM(sales.paid_amount) as total_paid'),
                DB::raw('SUM(sales.grand_total - sales.paid_amount) as outstanding')
            )
            ->orderByDesc('outstanding')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get top outstanding suppliers (for dashboard or summary).
     */
    public function getTopSupplierOutstanding(Store $store, int $limit = 5): array
    {
        return DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->where('purchases.store_id', $store->id)
            ->where('purchases.status', PurchaseStatus::COMPLETED->value)
            ->whereNull('purchases.deleted_at')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.company_name')
            ->havingRaw('SUM(purchases.grand_total - purchases.paid_amount) > 0')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                DB::raw('SUM(purchases.grand_total) as total_purchases'),
                DB::raw('SUM(purchases.paid_amount) as total_paid'),
                DB::raw('SUM(purchases.grand_total - purchases.paid_amount) as outstanding')
            )
            ->orderByDesc('outstanding')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Paginated customer outstanding list.
     */
    public function getPaginatedCustomerOutstanding(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.store_id', $store->id)
            ->where('sales.status', SaleStatus::COMPLETED->value)
            ->whereNull('sales.deleted_at')
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')
            ->havingRaw('SUM(sales.grand_total - sales.paid_amount) > 0')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                DB::raw('SUM(sales.grand_total) as total_sales'),
                DB::raw('SUM(sales.paid_amount) as total_paid'),
                DB::raw('SUM(sales.grand_total - sales.paid_amount) as outstanding'),
                DB::raw('COUNT(sales.id) as invoices_count')
            )
            ->orderByDesc('outstanding');

        if ($search) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('customers.name', 'like', "%{$term}%")
                    ->orWhere('customers.phone', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated supplier outstanding list.
     */
    public function getPaginatedSupplierOutstanding(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->where('purchases.store_id', $store->id)
            ->where('purchases.status', PurchaseStatus::COMPLETED->value)
            ->whereNull('purchases.deleted_at')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.company_name', 'suppliers.phone')
            ->havingRaw('SUM(purchases.grand_total - purchases.paid_amount) > 0')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                'suppliers.phone',
                DB::raw('SUM(purchases.grand_total) as total_purchases'),
                DB::raw('SUM(purchases.paid_amount) as total_paid'),
                DB::raw('SUM(purchases.grand_total - purchases.paid_amount) as outstanding'),
                DB::raw('COUNT(purchases.id) as purchases_count')
            )
            ->orderByDesc('outstanding');

        if ($search) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('suppliers.name', 'like', "%{$term}%")
                    ->orWhere('suppliers.company_name', 'like', "%{$term}%")
                    ->orWhere('suppliers.phone', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
