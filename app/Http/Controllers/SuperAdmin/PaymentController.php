<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Payment\StorePaymentRequest;
use App\Http\Requests\SuperAdmin\Payment\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = strtolower(trim((string) $request->get('status', '')));
        $method = strtolower(trim((string) $request->get('payment_method', '')));
        $planId = $request->get('plan_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Payment::with(['store.owners', 'subscription.plan', 'subscriptionPlan', 'creator'])
            ->latest('payment_date');

        // Search Filter (Transaction ID, Store Name, Store Code, Owner Name, Email)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($storeQuery) use ($search) {
                        $storeQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhereHas('owners', function ($ownerQuery) use ($search) {
                                $ownerQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('subscriptionPlan', function ($planQuery) use ($search) {
                        $planQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($status !== '' && in_array($status, PaymentStatus::values(), true)) {
            $query->where('status', $status);
        }

        // Payment Method Filter
        if ($method !== '' && in_array($method, PaymentMethod::values(), true)) {
            $query->where('payment_method', $method);
        }

        // Plan Filter
        if (! empty($planId)) {
            $query->where(function ($q) use ($planId) {
                $q->where('subscription_plan_id', $planId)
                    ->orWhereHas('subscription', fn ($subQuery) => $subQuery->where('subscription_plan_id', $planId));
            });
        }

        // Date Range Filter
        if (! empty($dateFrom)) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $payments = $query->paginate(15)->withQueryString();

        // High-level statistics
        $stats = [
            'total_revenue' => Payment::where('status', PaymentStatus::PAID->value)->sum('amount'),
            'paid_count' => Payment::where('status', PaymentStatus::PAID->value)->count(),
            'pending_count' => Payment::where('status', PaymentStatus::PENDING->value)->count(),
            'failed_count' => Payment::where('status', PaymentStatus::FAILED->value)->count(),
            'refunded_amount' => Payment::where('status', PaymentStatus::REFUNDED->value)->sum('amount'),
            'total_transactions' => Payment::count(),
        ];

        $plans = SubscriptionPlan::orderBy('name')->get(['id', 'name']);
        $paymentStatuses = PaymentStatus::cases();
        $paymentMethods = PaymentMethod::cases();

        return view('super-admin.payments.index', compact(
            'payments',
            'stats',
            'plans',
            'paymentStatuses',
            'paymentMethods',
            'search',
            'status',
            'method',
            'planId',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for recording a new payment.
     */
    public function create(Request $request): View
    {
        $selectedStoreId = $request->get('store_id');
        $selectedSubscriptionId = $request->get('subscription_id');

        $stores = Store::with(['activeSubscription.plan', 'latestSubscription.plan', 'owner'])
            ->orderBy('name')
            ->get();

        $plans = SubscriptionPlan::where('status', PlanStatus::ACTIVE)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::cases();
        $paymentStatuses = PaymentStatus::cases();
        $generatedTxnId = Payment::generateTransactionId();

        return view('super-admin.payments.create', compact(
            'stores',
            'plans',
            'paymentMethods',
            'paymentStatuses',
            'selectedStoreId',
            'selectedSubscriptionId',
            'generatedTxnId'
        ));
    }

    /**
     * Store a newly created payment in database.
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $store = Store::findOrFail($validated['store_id']);

        // Auto-resolve subscription plan if not directly selected
        $subscription = null;
        if (! empty($validated['subscription_id'])) {
            $subscription = Subscription::with('plan')->find($validated['subscription_id']);
        }

        $subscriptionPlanId = $validated['subscription_plan_id']
            ?? $subscription?->subscription_plan_id
            ?? $store->activeSubscription?->subscription_plan_id;

        $payment = Payment::create([
            'store_id' => $store->id,
            'subscription_id' => $subscription?->id ?? $store->activeSubscription?->id,
            'subscription_plan_id' => $subscriptionPlanId,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'INR',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'],
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('super-admin.payments.show', $payment)
            ->with('success', "Payment record [{$payment->transaction_id}] of ₹".number_format($payment->amount, 2).' recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): View
    {
        $payment->load([
            'store.owners',
            'subscription.plan',
            'subscriptionPlan',
            'creator',
            'updater',
        ]);

        return view('super-admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the payment record.
     */
    public function edit(Payment $payment): View
    {
        $payment->load(['store', 'subscription.plan', 'subscriptionPlan']);
        $paymentMethods = PaymentMethod::cases();
        $paymentStatuses = PaymentStatus::cases();

        return view('super-admin.payments.edit', compact('payment', 'paymentMethods', 'paymentStatuses'));
    }

    /**
     * Update the specified payment in database.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        $payment->update($validated);

        return redirect()->route('super-admin.payments.show', $payment)
            ->with('success', "Payment record [{$payment->transaction_id}] updated successfully.");
    }

    /**
     * Quick status update for a payment record.
     */
    public function updateStatus(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::enum(PaymentStatus::class)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $payment->status->label();
        $newStatusEnum = PaymentStatus::from($request->status);

        $payment->status = $newStatusEnum;
        $payment->updated_by = auth()->id();
        if ($request->filled('notes')) {
            $payment->notes = trim(($payment->notes ? $payment->notes."\n" : '')."[Status changed from {$oldStatus} to {$newStatusEnum->label()}]: ".$request->notes);
        }
        $payment->save();

        return redirect()->back()
            ->with('success', "Payment [{$payment->transaction_id}] status changed from {$oldStatus} to {$newStatusEnum->label()}.");
    }
}
