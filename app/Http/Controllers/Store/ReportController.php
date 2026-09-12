<?php

namespace App\Http\Controllers\Store;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Enums\SaleStatus;
use App\Enums\StorePaymentType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\BusinessReportService;
use App\Services\OutstandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected BusinessReportService $businessReportService,
        protected OutstandingService $outstandingService
    ) {}

    /**
     * Report Hub / Index.
     */
    public function index(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        return view('store.reports.index', compact('store'));
    }

    /**
     * Sales Report.
     */
    public function sales(Request $request): View|StreamedResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'customer_id', 'status', 'payment_status', 'invoice_number', 'medicine_id']);

        if ($request->input('export') === 'csv') {
            return $this->exportSalesCsv($store, $filters);
        }

        $sales = $this->businessReportService->getSalesReport($store, $filters, 15);
        $customers = Customer::forStore($store->id)->orderBy('name')->get();
        $medicines = Medicine::forStore($store->id)->orderBy('name')->get();
        $saleStatuses = SaleStatus::cases();
        $paymentStatuses = PaymentStatus::cases();

        return view('store.reports.sales', compact('store', 'sales', 'customers', 'medicines', 'saleStatuses', 'paymentStatuses', 'filters'));
    }

    /**
     * Purchase Report.
     */
    public function purchases(Request $request): View|StreamedResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'supplier_id', 'status', 'payment_status', 'invoice_number', 'medicine_id']);

        if ($request->input('export') === 'csv') {
            return $this->exportPurchasesCsv($store, $filters);
        }

        $purchases = $this->businessReportService->getPurchaseReport($store, $filters, 15);
        $suppliers = Supplier::forStore($store->id)->orderBy('name')->get();
        $medicines = Medicine::forStore($store->id)->orderBy('name')->get();
        $purchaseStatuses = PurchaseStatus::cases();
        $paymentStatuses = PaymentStatus::cases();

        return view('store.reports.purchases', compact('store', 'purchases', 'suppliers', 'medicines', 'purchaseStatuses', 'paymentStatuses', 'filters'));
    }

    /**
     * Sales Return Report.
     */
    public function salesReturns(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'customer_id', 'return_number', 'status']);
        $returns = $this->businessReportService->getSalesReturnReport($store, $filters, 15);
        $customers = Customer::forStore($store->id)->orderBy('name')->get();

        return view('store.reports.sales-returns', compact('store', 'returns', 'customers', 'filters'));
    }

    /**
     * Purchase Return Report.
     */
    public function purchaseReturns(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'supplier_id', 'return_number', 'status']);
        $returns = $this->businessReportService->getPurchaseReturnReport($store, $filters, 15);
        $suppliers = Supplier::forStore($store->id)->orderBy('name')->get();

        return view('store.reports.purchase-returns', compact('store', 'returns', 'suppliers', 'filters'));
    }

    /**
     * Expense Report.
     */
    public function expenses(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'category_id', 'payment_method', 'status']);
        $expenses = $this->businessReportService->getExpenseReport($store, $filters, 15);
        $categories = ExpenseCategory::forStore($store->id)->orderBy('name')->get();

        return view('store.reports.expenses', compact('store', 'expenses', 'categories', 'filters'));
    }

    /**
     * Payments Report.
     */
    public function payments(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['payment_type', 'from_date', 'to_date', 'payment_method', 'customer_id', 'supplier_id']);
        $payments = $this->businessReportService->getPaymentReport($store, $filters, 15);
        $paymentTypes = StorePaymentType::cases();
        $paymentMethods = PaymentMethod::cases();

        return view('store.reports.payments', compact('store', 'payments', 'paymentTypes', 'paymentMethods', 'filters'));
    }

    /**
     * Inventory Report.
     */
    public function inventory(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['medicine_id', 'category_id', 'manufacturer_id', 'batch_number', 'stock_status', 'expiry_status']);
        $batches = $this->businessReportService->getInventoryReport($store, $filters, 20);
        $categories = Category::forStore($store->id)->orderBy('name')->get();
        $manufacturers = Manufacturer::forStore($store->id)->orderBy('name')->get();
        $medicines = Medicine::forStore($store->id)->orderBy('name')->get();

        return view('store.reports.inventory', compact('store', 'batches', 'categories', 'manufacturers', 'medicines', 'filters'));
    }

    /**
     * Stock Movements Report.
     */
    public function stockMovements(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'medicine_id', 'batch_id', 'movement_type']);
        $movements = $this->businessReportService->getStockMovementReport($store, $filters, 20);
        $medicines = Medicine::forStore($store->id)->orderBy('name')->get();

        return view('store.reports.stock-movements', compact('store', 'movements', 'medicines', 'filters'));
    }

    /**
     * Medicine Sales Report.
     */
    public function medicineSales(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $filters = $request->only(['from_date', 'to_date', 'medicine_id', 'category_id', 'search']);
        $medicinesReport = $this->businessReportService->getMedicineSalesReport($store, $filters, 15);
        $categories = Category::forStore($store->id)->orderBy('name')->get();

        return view('store.reports.medicine-sales', compact('store', 'medicinesReport', 'categories', 'filters'));
    }

    /**
     * Stream CSV export for filtered sales.
     */
    protected function exportSalesCsv($store, array $filters): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($store, $filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice Number', 'Sale Date', 'Customer', 'Items Count', 'Subtotal', 'Discount', 'Tax', 'Grand Total', 'Paid Amount', 'Outstanding', 'Payment Status', 'Status']);

            $query = Sale::with(['customer', 'items'])
                ->where('store_id', $store->id)
                ->latest('sale_date');

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

            $query->chunk(200, function ($sales) use ($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->invoice_number,
                        $sale->sale_date->format('Y-m-d'),
                        $sale->customer_name ?: 'Walk-in',
                        $sale->items->count(),
                        $sale->subtotal,
                        $sale->discount,
                        $sale->tax,
                        $sale->grand_total,
                        $sale->paid_amount,
                        $sale->outstandingAmount(),
                        $sale->payment_status->value,
                        $sale->status->value,
                    ]);
                }
            });

            fclose($handle);
        });

        $filename = 'sales_report_'.date('Ymd_His').'.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    /**
     * Stream CSV export for filtered purchases.
     */
    protected function exportPurchasesCsv($store, array $filters): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($store, $filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Purchase Invoice', 'Purchase Date', 'Supplier', 'Items Count', 'Subtotal', 'Discount', 'Tax', 'Grand Total', 'Paid Amount', 'Outstanding', 'Payment Status', 'Status']);

            $query = Purchase::with(['supplier', 'items'])
                ->where('store_id', $store->id)
                ->latest('purchase_date');

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

            $query->chunk(200, function ($purchases) use ($handle) {
                foreach ($purchases as $purchase) {
                    fputcsv($handle, [
                        $purchase->invoice_number,
                        $purchase->purchase_date->format('Y-m-d'),
                        $purchase->supplier?->name ?: 'N/A',
                        $purchase->items->count(),
                        $purchase->subtotal,
                        $purchase->discount,
                        $purchase->tax,
                        $purchase->grand_total,
                        $purchase->paid_amount,
                        $purchase->outstandingAmount(),
                        $purchase->payment_status?->value ?? 'unpaid',
                        $purchase->status->value,
                    ]);
                }
            });

            fclose($handle);
        });

        $filename = 'purchase_report_'.date('Ymd_His').'.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
