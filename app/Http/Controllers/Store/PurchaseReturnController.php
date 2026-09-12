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

    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = PurchaseReturn::forStore($store->id)->with(['purchase', 'supplier', 'items']);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('return_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('return_date', '<=', $toDate);
        }

        $returns = $query->latest('return_date')->latest('id')->paginate(15)->withQueryString();

        return view('store.purchase-returns.index', compact('store', 'returns'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseId = $request->input('purchase_id');
        if (! $purchaseId) {
            return redirect()->route('store.purchases.index')->with('error', 'Select a completed purchase to initiate a return.');
        }

        $purchase = Purchase::forStore($store->id)
            ->with(['supplier', 'items.medicine', 'items.batch'])
            ->findOrFail($purchaseId);

        if (! $purchase->isCompleted()) {
            return redirect()->route('store.purchases.show', $purchase->id)
                ->with('error', 'Only completed purchases can be returned.');
        }

        return view('store.purchase-returns.create', compact('store', 'purchase'));
    }

    public function store(StorePurchaseReturnRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseId = (int) $request->input('purchase_id');
        $purchase = Purchase::forStore($store->id)->findOrFail($purchaseId);

        try {
            $return = $this->purchaseReturnService->createPurchaseReturn($store, $purchase, $request->validated(), auth()->id());

            return redirect()->route('store.purchase-returns.show', $return->id)
                ->with('success', "Purchase Return #{$return->return_number} processed successfully.");
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $purchaseReturn = PurchaseReturn::forStore($store->id)
            ->with(['purchase', 'supplier', 'items.medicine', 'items.batch'])
            ->findOrFail($id);

        return view('store.purchase-returns.show', compact('store', 'purchaseReturn'));
    }
}
