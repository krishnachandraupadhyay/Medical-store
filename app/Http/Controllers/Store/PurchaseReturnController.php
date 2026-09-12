<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Return\StorePurchaseReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Services\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $purchaseReturnService
    ) {}

    /**
     * Display a listing of purchase returns with KPI metrics.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = PurchaseReturn::forStore($store->id)->with(['purchase', 'supplier', 'items']);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('return_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('return_date', '<=', $toDate);
        }

        // Summary KPI statistics
        $allReturns = PurchaseReturn::forStore($store->id)->where('status', 'completed')->get();
        $totalReturnsCount = $allReturns->count();
        $totalReturnValue = (float) $allReturns->sum('grand_total');
        $totalAdjustedValue = (float) $allReturns->sum('adjustment_amount');
        $totalRefundedValue = (float) $allReturns->sum('refund_amount');

        $returns = $query->latest('return_date')->latest('id')->paginate(15)->withQueryString();

        return view('store.purchase-returns.index', compact(
            'store',
            'returns',
            'totalReturnsCount',
            'totalReturnValue',
            'totalAdjustedValue',
            'totalRefundedValue'
        ));
    }

    /**
     * Show the form for creating a new purchase return.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseId = $request->input('purchase_id');
        if (! $purchaseId) {
            return redirect()->route('store.purchases.index')
                ->with('error', 'Please select a completed purchase to initiate a return.');
        }

        $purchase = Purchase::forStore($store->id)
            ->with(['supplier', 'items.medicine', 'items.batch', 'returns'])
            ->findOrFail($purchaseId);

        if (! $purchase->isCompleted()) {
            return redirect()->route('store.purchases.show', $purchase->id)
                ->with('error', 'Only completed purchases can be returned.');
        }

        // Verify that there is at least one returnable item
        $hasReturnable = false;
        foreach ($purchase->items as $item) {
            if ($item->returnableQuantity() > 0) {
                $hasReturnable = true;
                break;
            }
        }

        if (! $hasReturnable) {
            return redirect()->route('store.purchases.show', $purchase->id)
                ->with('error', 'All items in this purchase order have already been returned, or no stock remains in the batch.');
        }

        return view('store.purchase-returns.create', compact('store', 'purchase'));
    }

    /**
     * Store a newly created purchase return in storage.
     */
    public function store(StorePurchaseReturnRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseId = (int) $request->input('purchase_id');
        $purchase = Purchase::forStore($store->id)->findOrFail($purchaseId);

        try {
            $return = $this->purchaseReturnService->createPurchaseReturn(
                $store,
                $purchase,
                $request->validated(),
                auth()->id()
            );

            return redirect()->route('store.purchase-returns.show', $return->id)
                ->with('status', "Purchase Return #{$return->return_number} created and processed successfully.");
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified purchase return details.
     */
    public function show(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseReturn = PurchaseReturn::forStore($store->id)
            ->with(['purchase', 'supplier', 'items.medicine', 'items.batch', 'creator'])
            ->findOrFail($id);

        return view('store.purchase-returns.show', compact('store', 'purchaseReturn'));
    }

    /**
     * Print the purchase return debit note document.
     */
    public function printReturn(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseReturn = PurchaseReturn::forStore($store->id)
            ->with(['purchase', 'supplier', 'items.medicine', 'items.batch', 'creator'])
            ->findOrFail($id);

        return view('store.purchase-returns.print', compact('store', 'purchaseReturn'));
    }
}
