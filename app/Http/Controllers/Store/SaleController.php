<?php

namespace App\Http\Controllers\Store;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Sale\RecordPaymentRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\PaymentService;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Display listing of sales invoices.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Sale::forStore($store->id)->with(['customer', 'items', 'creator']);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('sale_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('sale_date', '<=', $toDate);
        }

        $sales = $query->latest('sale_date')->latest('id')->paginate(15)->withQueryString();
        $customers = Customer::forStore($store->id)->orderBy('name')->get();

        return view('store.sales.index', compact('store', 'sales', 'customers'));
    }

    /**
     * Show single sale invoice details.
     */
    public function show(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $sale = Sale::forStore($store->id)
            ->with(['customer', 'items.medicine', 'items.batch', 'payments', 'returns.items'])
            ->findOrFail($id);

        $paymentMethods = PaymentMethod::cases();

        return view('store.sales.show', compact('store', 'sale', 'paymentMethods'));
    }

    /**
     * Print-friendly invoice view.
     */
    public function invoice(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $sale = Sale::forStore($store->id)
            ->with(['customer', 'items.medicine', 'items.batch', 'payments'])
            ->findOrFail($id);

        return view('store.sales.invoice', compact('store', 'sale'));
    }

    /**
     * Complete a draft sale.
     */
    public function complete(Request $request, int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $sale = Sale::forStore($store->id)->findOrFail($id);

        try {
            $paymentData = $request->only(['paid_amount', 'payment_method', 'payment_reference']);
            $this->salesService->completeDraftSale($store, $sale, $paymentData, auth()->id());

            return redirect()->route('store.sales.show', $sale->id)
                ->with('success', "Sale Invoice #{$sale->invoice_number} completed successfully.");
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a draft sale.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $sale = Sale::forStore($store->id)->findOrFail($id);

        try {
            $reason = $request->input('cancellation_reason');
            $this->salesService->cancelDraftSale($store, $sale, $reason, auth()->id());

            return redirect()->route('store.sales.show', $sale->id)
                ->with('success', "Sale Invoice #{$sale->invoice_number} cancelled.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record customer payment against outstanding invoice balance.
     */
    public function recordPayment(RecordPaymentRequest $request, int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $sale = Sale::forStore($store->id)->findOrFail($id);

        try {
            $payment = $this->paymentService->recordSalePayment($store, $sale, $request->validated(), auth()->id());

            return redirect()->route('store.sales.show', $sale->id)
                ->with('success', "Payment #{$payment->payment_number} of ₹{$payment->amount} recorded successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
