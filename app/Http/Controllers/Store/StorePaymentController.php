<?php

namespace App\Http\Controllers\Store;

use App\Enums\PaymentMethod;
use App\Enums\StorePaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Sale\RecordPaymentRequest;
use App\Models\Purchase;
use App\Models\StorePayment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorePaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = StorePayment::forStore($store->id)->with(['sale', 'purchase', 'expense', 'customer', 'supplier']);

        if ($type = $request->input('payment_type')) {
            $query->where('type', $type);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('payment_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('payment_date', '<=', $toDate);
        }

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->withQueryString();

        $paymentTypes = StorePaymentType::cases();
        $paymentMethods = PaymentMethod::cases();

        return view('store.payments.index', compact('store', 'payments', 'paymentTypes', 'paymentMethods'));
    }

    public function show(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $payment = StorePayment::forStore($store->id)
            ->with(['sale.customer', 'purchase.supplier', 'expense', 'customer', 'supplier', 'creator'])
            ->findOrFail($id);

        return view('store.payments.show', compact('store', 'payment'));
    }

    /**
     * Record payment to supplier on a purchase invoice.
     */
    public function recordPurchasePayment(RecordPaymentRequest $request, int $purchaseId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchase = Purchase::forStore($store->id)->findOrFail($purchaseId);

        try {
            $payment = $this->paymentService->recordPurchasePayment($store, $purchase, $request->validated(), auth()->id());

            return redirect()->route('store.purchases.show', $purchase->id)
                ->with('success', "Supplier payment #{$payment->payment_number} of ₹{$payment->amount} recorded successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
