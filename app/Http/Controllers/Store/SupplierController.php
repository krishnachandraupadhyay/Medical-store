<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PaymentMethod;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Supplier\RecordSupplierPaymentRequest;
use App\Http\Requests\Store\Supplier\StoreSupplierRequest;
use App\Http\Requests\Store\Supplier\UpdateSupplierRequest;
use App\Models\ContactNote;
use App\Models\StorePayment;
use App\Models\Supplier;
use App\Services\AuditLogger;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers for the active store with KPIs.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Supplier::forStore($store->id)->withCount('purchases');

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Summary KPIs across store
        $allSuppliers = Supplier::forStore($store->id)->get();
        $totalSuppliersCount = $allSuppliers->count();
        $activeSuppliersCount = $allSuppliers->where('status', 'active')->count();

        $totalOutstandingDue = 0.00;
        $totalAdvanceGiven = 0.00;
        $suppliersWithDueCount = 0;

        foreach ($allSuppliers as $sup) {
            $bal = $sup->outstandingAmount();
            if ($bal > 0) {
                $totalOutstandingDue += $bal;
                $suppliersWithDueCount++;
            } elseif ($bal < 0) {
                $totalAdvanceGiven += abs($bal);
            }
        }

        // Balance Filter: has_due, advance, zero
        $balanceFilter = $request->input('balance');
        if ($balanceFilter === 'has_due') {
            $suppliers = $query->orderBy('name')->get()->filter(fn ($s) => $s->outstandingAmount() > 0);
            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 15;
            $suppliers = new LengthAwarePaginator(
                $suppliers->forPage($page, $perPage),
                $suppliers->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );
        } elseif ($balanceFilter === 'advance') {
            $suppliers = $query->orderBy('name')->get()->filter(fn ($s) => $s->outstandingAmount() < 0);
            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 15;
            $suppliers = new LengthAwarePaginator(
                $suppliers->forPage($page, $perPage),
                $suppliers->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );
        } else {
            $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();
        }

        return view('store.suppliers.index', compact(
            'store',
            'suppliers',
            'totalSuppliersCount',
            'activeSuppliersCount',
            'totalOutstandingDue',
            'totalAdvanceGiven',
            'suppliersWithDueCount'
        ));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::checkLimit($store, 'max_suppliers', 1)) {
            return redirect()->route('store.suppliers.index')
                ->with('error', 'You have reached the maximum supplier limit allowed by your plan. Please upgrade your plan.');
        }

        return view('store.suppliers.create', compact('store'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::checkLimit($store, 'max_suppliers', 1)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You have reached the maximum supplier limit allowed by your plan. Please upgrade your plan.');
        }

        $data = $request->validated();
        $data['store_id'] = $store->id;
        $data['created_by'] = auth()->id();
        $data['supplier_code'] = Supplier::generateSupplierCode($store->id);

        if (isset($data['is_active'])) {
            $data['status'] = $data['is_active'] ? 'active' : 'inactive';
            unset($data['is_active']);
        } elseif (empty($data['status'])) {
            $data['status'] = 'active';
        }

        $data['opening_balance'] = isset($data['opening_balance']) ? round((float) $data['opening_balance'], 2) : 0.00;
        $data['opening_balance_type'] = $data['opening_balance_type'] ?? 'payable';

        $supplier = Supplier::create($data);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::SUPPLIERS,
            "Created supplier [{$supplier->name}] ({$supplier->supplier_code}) for store [{$store->name}].",
            $supplier,
            null,
            $supplier->toArray()
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', "Supplier [{$supplier->name}] ({$supplier->supplier_code}) created successfully.");
    }

    /**
     * Display the specified supplier — 360° profile with Ledger & Payment options.
     */
    public function show(Supplier $supplier): View
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $totalPurchased = $supplier->totalPurchased();
        $totalPaid = $supplier->totalPaid();
        $outstanding = $supplier->outstandingAmount();
        $availableCredit = $supplier->availableCredit();
        $purchasesCount = $supplier->purchasesCount();
        $lastPurchase = $supplier->lastPurchaseDate();

        // Recent purchases (last 15)
        $recentPurchases = $supplier->purchases()
            ->with('items')
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(10, ['*'], 'purchases_page');

        // Recent payments
        $payments = StorePayment::where('store_id', $store->id)
            ->where('supplier_id', $supplier->id)
            ->with('purchase')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(10, ['*'], 'payments_page');

        // Unpaid or partially paid purchases for direct payment allocation
        $pendingPurchases = $supplier->purchases()
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->orderBy('purchase_date')
            ->get();

        // Notes & follow-ups
        $notes = $supplier->contactNotes()
            ->with('creator')
            ->latest()
            ->get();

        $pendingFollowUps = $notes->filter(fn ($n) => $n->hasFollowUp() && ! $n->follow_up_done);
        $overdueFollowUps = $notes->filter(fn ($n) => $n->isFollowUpOverdue());

        return view('store.suppliers.show', compact(
            'store', 'supplier',
            'totalPurchased', 'totalPaid', 'outstanding', 'availableCredit', 'purchasesCount', 'lastPurchase',
            'recentPurchases', 'payments', 'pendingPurchases',
            'notes', 'pendingFollowUps', 'overdueFollowUps'
        ));
    }

    /**
     * Show the form for editing the supplier.
     */
    public function edit(Supplier $supplier): View
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        return view('store.suppliers.edit', compact('store', 'supplier'));
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $oldValues = $supplier->toArray();
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        if (isset($data['is_active'])) {
            $data['status'] = $data['is_active'] ? 'active' : 'inactive';
            unset($data['is_active']);
        }

        if (isset($data['opening_balance'])) {
            $data['opening_balance'] = round((float) $data['opening_balance'], 2);
        }

        $supplier->update($data);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::SUPPLIERS,
            "Updated supplier [{$supplier->name}] ({$supplier->supplier_code}).",
            $supplier,
            $oldValues,
            $supplier->fresh()->toArray()
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', "Supplier [{$supplier->name}] updated successfully.");
    }

    /**
     * Toggle supplier active/inactive status.
     */
    public function toggleStatus(Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $supplier->toggleStatus();

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::SUPPLIERS,
            "Toggled status of supplier [{$supplier->name}] to {$supplier->status}.",
            $supplier
        );

        return redirect()->back()
            ->with('status', "Supplier status changed to {$supplier->status}.");
    }

    /**
     * Remove the specified supplier (with strict protection against deleting suppliers with financial history).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $hasPurchases = $supplier->purchases()->exists();
        $hasPayments = $supplier->payments()->exists();
        $hasReturns = method_exists($supplier, 'purchaseReturns') && $supplier->purchaseReturns()->exists();

        if ($hasPurchases || $hasPayments || $hasReturns) {
            return redirect()->route('store.suppliers.show', $supplier)
                ->with('error', 'Cannot delete this supplier because there are existing purchase or payment records linked to it. You can mark the supplier as inactive instead.');
        }

        $supplierName = $supplier->name;
        $supplierData = $supplier->toArray();
        $supplier->delete();

        AuditLogger::log(
            AuditAction::DELETED,
            AuditModule::SUPPLIERS,
            "Deleted supplier [{$supplierName}].",
            $supplier,
            $supplierData,
            null
        );

        return redirect()->route('store.suppliers.index')
            ->with('status', "Supplier [{$supplierName}] deleted successfully.");
    }

    /**
     * View supplier ledger statement with date filters and running balances.
     */
    public function ledger(Request $request, Supplier $supplier): View
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $ledgerData = $this->calculateLedgerData($store, $supplier, $request);

        return view('store.suppliers.ledger', array_merge(['store' => $store, 'supplier' => $supplier], $ledgerData));
    }

    /**
     * Print supplier ledger statement (A4 friendly layout).
     */
    public function printLedger(Request $request, Supplier $supplier): View
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $ledgerData = $this->calculateLedgerData($store, $supplier, $request);

        return view('store.suppliers.ledger-print', array_merge(['store' => $store, 'supplier' => $supplier], $ledgerData));
    }

    /**
     * Record a direct or allocated payment to the supplier.
     */
    public function recordPayment(RecordSupplierPaymentRequest $request, Supplier $supplier, PaymentService $paymentService): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $payment = $paymentService->recordSupplierDirectPayment(
            $store,
            $supplier,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', "Payment #{$payment->payment_number} of ₹".number_format($payment->amount, 2).' recorded successfully.');
    }

    /**
     * Store-wide Supplier Outstanding Report.
     */
    public function outstandingReport(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Supplier::forStore($store->id);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $allSuppliers = $query->orderBy('name')->get();

        $rows = [];
        $totalPayable = 0.00;
        $totalAdvance = 0.00;
        $suppliersWithDueCount = 0;
        $suppliersOverLimitCount = 0;

        foreach ($allSuppliers as $sup) {
            $outstanding = $sup->outstandingAmount();
            $creditLimit = $sup->credit_limit ? (float) $sup->credit_limit : null;
            $isOverLimit = $creditLimit !== null && $creditLimit > 0 && $outstanding > $creditLimit;

            if ($outstanding > 0) {
                $totalPayable += $outstanding;
                $suppliersWithDueCount++;
            } elseif ($outstanding < 0) {
                $totalAdvance += abs($outstanding);
            }

            if ($isOverLimit) {
                $suppliersOverLimitCount++;
            }

            $rows[] = [
                'supplier' => $sup,
                'opening_balance' => (float) ($sup->opening_balance ?? 0.00),
                'opening_type' => $sup->opening_balance_type ?? 'payable',
                'total_purchased' => $sup->totalPurchased(),
                'total_paid' => $sup->totalPaid(),
                'outstanding' => $outstanding,
                'credit_limit' => $creditLimit,
                'is_over_limit' => $isOverLimit,
                'last_purchase_date' => $sup->lastPurchaseDate(),
            ];
        }

        // Apply filter tab
        $filter = $request->input('filter', 'all');
        if ($filter === 'has_due') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['outstanding'] > 0));
        } elseif ($filter === 'over_limit') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['is_over_limit']));
        } elseif ($filter === 'advance') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['outstanding'] < 0));
        }

        $netPayable = round($totalPayable - $totalAdvance, 2);

        return view('store.suppliers.outstanding', compact(
            'store',
            'rows',
            'totalPayable',
            'totalAdvance',
            'netPayable',
            'suppliersWithDueCount',
            'suppliersOverLimitCount',
            'filter'
        ));
    }

    /**
     * Helper to compute chronological supplier ledger entries and running balances.
     */
    protected function calculateLedgerData(mixed $store, Supplier $supplier, Request $request): array
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?? now()->toDateString();

        // 1. Calculate Period Opening Balance (Base opening balance + prior purchases - prior payments)
        $baseOpening = $supplier->openingBalancePayable();

        $priorPurchases = 0.00;
        $priorPayments = 0.00;
        $priorReturns = 0.00;

        if ($startDate) {
            $priorPurchases = (float) $supplier->purchases()
                ->where('status', 'completed')
                ->whereDate('purchase_date', '<', $startDate)
                ->sum('grand_total');

            $priorPayments = (float) StorePayment::where('store_id', $store->id)
                ->where('supplier_id', $supplier->id)
                ->where(function ($q) {
                    $q->where('status', 'completed')->orWhereNull('status');
                })
                ->whereDate('payment_date', '<', $startDate)
                ->sum('amount');

            $priorReturns = (float) $supplier->purchaseReturns()
                ->where('status', 'completed')
                ->whereDate('return_date', '<', $startDate)
                ->sum('grand_total');
        }

        $periodOpeningBalance = round($baseOpening + $priorPurchases - $priorPayments - $priorReturns, 2);

        // 2. Fetch purchases in range
        $purchasesQuery = $supplier->purchases()->where('status', 'completed');
        if ($startDate) {
            $purchasesQuery->whereDate('purchase_date', '>=', $startDate);
        }
        if ($endDate) {
            $purchasesQuery->whereDate('purchase_date', '<=', $endDate);
        }
        $purchases = $purchasesQuery->get();

        // 3. Fetch purchase returns in range
        $returnsQuery = $supplier->purchaseReturns()->where('status', 'completed');
        if ($startDate) {
            $returnsQuery->whereDate('return_date', '>=', $startDate);
        }
        if ($endDate) {
            $returnsQuery->whereDate('return_date', '<=', $endDate);
        }
        $returns = $returnsQuery->get();

        // 4. Fetch payments in range
        $paymentsQuery = StorePayment::where('store_id', $store->id)
            ->where('supplier_id', $supplier->id)
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhereNull('status');
            });
        if ($startDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $endDate);
        }
        $payments = $paymentsQuery->get();

        // 5. Merge into unified chronological stream
        $rawEntries = [];

        foreach ($purchases as $p) {
            $dateStr = Carbon::parse($p->purchase_date)->toDateString();
            $statusLabel = $p->payment_status instanceof \BackedEnum ? $p->payment_status->value : (string) $p->payment_status;
            $rawEntries[] = [
                'date' => $dateStr,
                'sort_key' => $dateStr.'_1_'.str_pad((string) $p->id, 8, '0', STR_PAD_LEFT),
                'type' => 'PURCHASE',
                'reference' => $p->invoice_number,
                'description' => "Purchase Invoice #{$p->invoice_number}".($statusLabel ? " ({$statusLabel})" : ''),
                'debit' => round((float) $p->grand_total, 2),
                'credit' => 0.00,
                'model' => $p,
            ];
        }

        foreach ($returns as $ret) {
            $dateStr = Carbon::parse($ret->return_date)->toDateString();
            $purchaseRef = $ret->purchase ? " (Invoice #{$ret->purchase->invoice_number})" : '';
            $rawEntries[] = [
                'date' => $dateStr,
                'sort_key' => $dateStr.'_1_5_'.str_pad((string) $ret->id, 8, '0', STR_PAD_LEFT),
                'type' => 'PURCHASE_RETURN',
                'reference' => $ret->return_number,
                'description' => "Purchase Return #{$ret->return_number}{$purchaseRef} - Debit Note",
                'debit' => 0.00,
                'credit' => round((float) $ret->grand_total, 2),
                'model' => $ret,
            ];
        }

        foreach ($payments as $pay) {
            $dateStr = Carbon::parse($pay->payment_date)->toDateString();
            $methodLabel = $pay->payment_method instanceof PaymentMethod ? $pay->payment_method->label() : ($pay->payment_method ?? 'Payment');
            $refText = $pay->reference_number ? " [Ref: {$pay->reference_number}]" : '';
            $purchaseText = $pay->purchase_id ? " (Against Invoice #{$pay->purchase?->invoice_number})" : ' (Direct / On Account)';

            $rawEntries[] = [
                'date' => $dateStr,
                'sort_key' => $dateStr.'_2_'.str_pad((string) $pay->id, 8, '0', STR_PAD_LEFT),
                'type' => 'PAYMENT',
                'reference' => $pay->payment_number,
                'description' => "Payment #{$pay->payment_number} via {$methodLabel}{$refText}{$purchaseText}",
                'debit' => 0.00,
                'credit' => round((float) $pay->amount, 2),
                'model' => $pay,
            ];
        }

        // Sort ascending by sort_key
        usort($rawEntries, fn ($a, $b) => strcmp($a['sort_key'], $b['sort_key']));

        // Calculate running balance: Balance = Previous Balance + Debit (purchases) - Credit (payments)
        $runningBalance = $periodOpeningBalance;
        $totalDebits = 0.00;
        $totalCredits = 0.00;
        $ledgerEntries = [];

        foreach ($rawEntries as $entry) {
            $totalDebits += $entry['debit'];
            $totalCredits += $entry['credit'];
            $runningBalance = round($runningBalance + $entry['debit'] - $entry['credit'], 2);
            $entry['balance'] = $runningBalance;
            $ledgerEntries[] = $entry;
        }

        $closingBalance = $runningBalance;

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodOpeningBalance' => $periodOpeningBalance,
            'ledgerEntries' => $ledgerEntries,
            'totalDebits' => round($totalDebits, 2),
            'totalCredits' => round($totalCredits, 2),
            'closingBalance' => $closingBalance,
        ];
    }

    // ─── Note Actions ─────────────────────────────────────────────────────────

    public function storeNote(Request $request, Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $data = $request->validate([
            'type' => ['required', 'in:note,call,meeting,email,followup'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $note = $supplier->contactNotes()->create([
            'store_id' => $store->id,
            'type' => $data['type'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $supplier->updateQuietly(['last_contacted_at' => now()]);

        AuditLogger::log(
            AuditAction::NOTE_ADDED,
            AuditModule::CONTACT_NOTES,
            "Added {$note->typeLabel()} note on supplier [{$supplier->name}].",
            $supplier
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', 'Note added successfully.')
            ->withFragment('notes');
    }

    public function markNoteFollowUpDone(Supplier $supplier, int $noteId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $note = ContactNote::forStore($store->id)
            ->where('notable_type', Supplier::class)
            ->where('notable_id', $supplier->id)
            ->findOrFail($noteId);

        $note->update([
            'follow_up_done' => true,
            'follow_up_done_at' => now(),
        ]);

        AuditLogger::log(
            AuditAction::FOLLOWUP_COMPLETED,
            AuditModule::CONTACT_NOTES,
            "Marked follow-up done on supplier [{$supplier->name}] note.",
            $supplier
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', 'Follow-up marked as done.')
            ->withFragment('notes');
    }

    public function destroyNote(Supplier $supplier, int $noteId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $note = ContactNote::forStore($store->id)
            ->where('notable_type', Supplier::class)
            ->where('notable_id', $supplier->id)
            ->findOrFail($noteId);

        $note->delete();

        AuditLogger::log(
            AuditAction::NOTE_DELETED,
            AuditModule::CONTACT_NOTES,
            "Deleted note on supplier [{$supplier->name}].",
            $supplier
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', 'Note deleted.')
            ->withFragment('notes');
    }
}
