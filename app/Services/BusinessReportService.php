<?php

namespace App\Services;

use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Models\Batch;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StorePayment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BusinessReportService
{
    /**
     * Paginated Sales Report.
     */
    public function getSalesReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Sale::with(['customer', 'items.medicine'])
            ->where('store_id', $store->id)
            ->latest('sale_date')
            ->latest('id');

        if (! empty($filters['from_date'])) {
            $query->whereDate('sale_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('sale_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (! empty($filters['invoice_number'])) {
            $query->where('invoice_number', 'like', '%'.trim($filters['invoice_number']).'%');
        }
        if (! empty($filters['medicine_id'])) {
            $query->whereHas('items', function ($iq) use ($filters) {
                $iq->where('medicine_id', $filters['medicine_id']);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Purchase Report.
     */
    public function getPurchaseReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Purchase::with(['supplier', 'items.medicine'])
            ->where('store_id', $store->id)
            ->latest('purchase_date')
            ->latest('id');

        if (! empty($filters['from_date'])) {
            $query->whereDate('purchase_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('purchase_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (! empty($filters['invoice_number'])) {
            $query->where('invoice_number', 'like', '%'.trim($filters['invoice_number']).'%');
        }
        if (! empty($filters['medicine_id'])) {
            $query->whereHas('items', function ($iq) use ($filters) {
                $iq->where('medicine_id', $filters['medicine_id']);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Sales Return Report.
     */
    public function getSalesReturnReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = SalesReturn::with(['sale', 'customer', 'items.medicine'])
            ->where('store_id', $store->id)
            ->latest('return_date')
            ->latest('id');

        if (! empty($filters['from_date'])) {
            $query->whereDate('return_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('return_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (! empty($filters['return_number'])) {
            $query->where('return_number', 'like', '%'.trim($filters['return_number']).'%');
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Purchase Return Report.
     */
    public function getPurchaseReturnReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PurchaseReturn::with(['purchase', 'supplier', 'items.medicine'])
            ->where('store_id', $store->id)
            ->latest('return_date')
            ->latest('id');

        if (! empty($filters['from_date'])) {
            $query->whereDate('return_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('return_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (! empty($filters['return_number'])) {
            $query->where('return_number', 'like', '%'.trim($filters['return_number']).'%');
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Expense Report.
     */
    public function getExpenseReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Expense::with('category')
            ->where('store_id', $store->id)
            ->latest('expense_date')
            ->latest('id');

        if (! empty($filters['from_date'])) {
            $query->whereDate('expense_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('expense_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Store Payments Report.
     */
    public function getPaymentReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = StorePayment::with(['sale', 'purchase', 'expense', 'customer', 'supplier'])
            ->where('store_id', $store->id)
            ->latest('payment_date')
            ->latest('id');

        if (! empty($filters['payment_type'])) {
            $query->where('type', $filters['payment_type']);
        }
        if (! empty($filters['from_date'])) {
            $query->whereDate('payment_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('payment_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Inventory Valuation and Batch Status Report.
     */
    public function getInventoryReport(Store $store, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Batch::with(['medicine.category', 'medicine.manufacturer'])
            ->where('store_id', $store->id)
            ->latest('id');

        if (! empty($filters['medicine_id'])) {
            $query->where('medicine_id', $filters['medicine_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->whereHas('medicine', function ($mq) use ($filters) {
                $mq->where('category_id', $filters['category_id']);
            });
        }
        if (! empty($filters['manufacturer_id'])) {
            $query->whereHas('medicine', function ($mq) use ($filters) {
                $mq->where('manufacturer_id', $filters['manufacturer_id']);
            });
        }
        if (! empty($filters['batch_number'])) {
            $query->where('batch_number', 'like', '%'.trim($filters['batch_number']).'%');
        }
        if (! empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'in_stock') {
                $query->where('quantity', '>', 0);
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            }
        }
        if (! empty($filters['expiry_status'])) {
            $today = Carbon::today()->toDateString();
            $soon = Carbon::today()->addDays(90)->toDateString();
            if ($filters['expiry_status'] === 'expired') {
                $query->where('expiry_date', '<', $today);
            } elseif ($filters['expiry_status'] === 'expiring_soon') {
                $query->whereBetween('expiry_date', [$today, $soon]);
            } elseif ($filters['expiry_status'] === 'valid') {
                $query->where('expiry_date', '>', $soon);
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Stock Movement Audit Report.
     */
    public function getStockMovementReport(Store $store, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = StockMovement::with(['medicine', 'batch'])
            ->where('store_id', $store->id)
            ->latest('id');

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        if (! empty($filters['medicine_id'])) {
            $query->where('medicine_id', $filters['medicine_id']);
        }
        if (! empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (! empty($filters['movement_type'])) {
            $query->where('type', $filters['movement_type']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Paginated Medicine Sales and Returns Report.
     */
    public function getMedicineSalesReport(Store $store, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $query = DB::table('medicines')
            ->where('medicines.store_id', $store->id)
            ->whereNull('medicines.deleted_at')
            ->leftJoin('sale_items', function ($join) use ($fromDate, $toDate) {
                $join->on('medicines.id', '=', 'sale_items.medicine_id')
                    ->join('sales', function ($sq) use ($fromDate, $toDate) {
                        $sq->on('sale_items.sale_id', '=', 'sales.id')
                            ->where('sales.status', '=', SaleStatus::COMPLETED->value)
                            ->whereNull('sales.deleted_at');
                        if ($fromDate) {
                            $sq->whereDate('sales.sale_date', '>=', $fromDate);
                        }
                        if ($toDate) {
                            $sq->whereDate('sales.sale_date', '<=', $toDate);
                        }
                    });
            })
            ->leftJoin('sales_return_items', function ($join) use ($fromDate, $toDate) {
                $join->on('medicines.id', '=', 'sales_return_items.medicine_id')
                    ->join('sales_returns', function ($rq) use ($fromDate, $toDate) {
                        $rq->on('sales_return_items.sales_return_id', '=', 'sales_returns.id')
                            ->where('sales_returns.status', '=', ReturnStatus::COMPLETED->value)
                            ->whereNull('sales_returns.deleted_at');
                        if ($fromDate) {
                            $rq->whereDate('sales_returns.return_date', '>=', $fromDate);
                        }
                        if ($toDate) {
                            $rq->whereDate('sales_returns.return_date', '<=', $toDate);
                        }
                    });
            })
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength', 'medicines.generic_name')
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.strength',
                'medicines.generic_name',
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as sold_quantity'),
                DB::raw('COALESCE(SUM(sale_items.line_total), 0) as sales_value'),
                DB::raw('COALESCE(SUM(sales_return_items.quantity), 0) as return_quantity'),
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) - COALESCE(SUM(sales_return_items.quantity), 0) as net_quantity')
            )
            ->orderByDesc('sold_quantity');

        if (! empty($filters['medicine_id'])) {
            $query->where('medicines.id', $filters['medicine_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('medicines.category_id', $filters['category_id']);
        }
        if (! empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('medicines.name', 'like', "%{$term}%")
                    ->orWhere('medicines.generic_name', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
