<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Purchase\StorePurchaseRequest;
use App\Http\Requests\Store\Purchase\UpdatePurchaseRequest;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    /**
     * Display a listing of purchases for the active store.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Purchase::forStore($store->id)->with(['supplier', 'items']);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('purchase_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('purchase_date', '<=', $toDate);
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();

        $suppliers = Supplier::forStore($store->id)->orderBy('name')->get();

        return view('store.purchases.index', compact('store', 'purchases', 'suppliers'));
    }

    /**
     * Show form for creating a new purchase invoice.
     */
    public function create(): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $suppliers = Supplier::forStore($store->id)->active()->orderBy('name')->get();
        if ($suppliers->isEmpty()) {
            return redirect()->route('store.suppliers.create')
                ->with('error', 'Please create at least one active supplier before recording a purchase.');
        }

        $medicines = Medicine::forStore($store->id)->active()->with('unit')->orderBy('name')->get();
        if ($medicines->isEmpty()) {
            return redirect()->route('store.medicines.create')
                ->with('error', 'Please create at least one active medicine before recording a purchase.');
        }

        return view('store.purchases.create', compact('store', 'suppliers', 'medicines'));
    }

    /**
     * Store a newly created purchase.
     */
    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $data = $request->safe()->except('items');
        $itemsData = $request->safe()->only('items')['items'];

        $purchase = $this->purchaseService->createPurchase(
            $store,
            $data,
            $itemsData,
            auth()->id()
        );

        $msg = $purchase->isCompleted()
            ? "Purchase invoice #{$purchase->invoice_number} created and inventory stock updated successfully."
            : "Purchase invoice #{$purchase->invoice_number} saved as draft.";

        return redirect()->route('store.purchases.show', $purchase)
            ->with('status', $msg);
    }

    /**
     * Display the specified purchase details and items.
     */
    public function show(Purchase $purchase): View
    {
        $store = current_store();
        abort_unless($store && $purchase->store_id === $store->id, 404);

        $purchase->load(['supplier', 'items.medicine.unit', 'items.batch', 'creator']);

        return view('store.purchases.show', compact('store', 'purchase'));
    }

    /**
     * Show the form for editing the purchase (drafts only).
     */
    public function edit(Purchase $purchase): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $purchase->store_id === $store->id, 404);

        if (! $purchase->isDraft()) {
            return redirect()->route('store.purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be edited.');
        }

        $purchase->load('items.medicine');
        $suppliers = Supplier::forStore($store->id)->active()->orderBy('name')->get();
        $medicines = Medicine::forStore($store->id)->active()->with('unit')->orderBy('name')->get();

        return view('store.purchases.edit', compact('store', 'purchase', 'suppliers', 'medicines'));
    }

    /**
     * Update the draft purchase.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $purchase->store_id === $store->id, 404);

        if (! $purchase->isDraft()) {
            return redirect()->route('store.purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be edited.');
        }

        $data = $request->safe()->except('items');
        $itemsData = $request->safe()->only('items')['items'];

        $purchase = $this->purchaseService->updatePurchase(
            $store,
            $purchase,
            $data,
            $itemsData,
            auth()->id()
        );

        return redirect()->route('store.purchases.show', $purchase)
            ->with('status', "Draft purchase invoice #{$purchase->invoice_number} updated successfully.");
    }

    /**
     * Complete a draft purchase and ingest stock into batch inventory.
     */
    public function complete(Purchase $purchase): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $purchase->store_id === $store->id, 404);

        if (! $purchase->isDraft()) {
            return redirect()->route('store.purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be marked as completed.');
        }

        $this->purchaseService->completePurchase($store, $purchase, auth()->id());

        return redirect()->route('store.purchases.show', $purchase)
            ->with('status', "Purchase invoice #{$purchase->invoice_number} marked as completed and inventory stock updated successfully.");
    }

    /**
     * Cancel a draft purchase.
     */
    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $purchase->store_id === $store->id, 404);

        if (! $purchase->isDraft()) {
            return redirect()->route('store.purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be cancelled.');
        }

        $reason = $request->input('reason', 'Cancelled by store owner');
        $this->purchaseService->cancelPurchase($store, $purchase, $reason, auth()->id());

        return redirect()->route('store.purchases.show', $purchase)
            ->with('status', "Purchase invoice #{$purchase->invoice_number} has been cancelled.");
    }
}
