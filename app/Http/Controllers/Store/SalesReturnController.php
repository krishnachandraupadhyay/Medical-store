<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Return\StoreSalesReturnRequest;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesReturnController extends Controller
{
    public function __construct(
        protected SalesReturnService $salesReturnService
    ) {}

    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = SalesReturn::forStore($store->id)->with(['sale', 'customer', 'items']);

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

        return view('store.sales-returns.index', compact('store', 'returns'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $saleId = $request->input('sale_id');
        if (! $saleId) {
            return redirect()->route('store.sales.index')->with('error', 'Select a completed sale to initiate a return.');
        }

        $sale = Sale::forStore($store->id)
            ->with(['customer', 'items.medicine', 'items.batch'])
            ->findOrFail($saleId);

        if (! $sale->isCompleted()) {
            return redirect()->route('store.sales.show', $sale->id)
                ->with('error', 'Only completed sales can be returned.');
        }

        return view('store.sales-returns.create', compact('store', 'sale'));
    }

    public function store(StoreSalesReturnRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $saleId = (int) $request->input('sale_id');
        $sale = Sale::forStore($store->id)->findOrFail($saleId);

        try {
            $return = $this->salesReturnService->createSalesReturn($store, $sale, $request->validated(), auth()->id());

            return redirect()->route('store.sales-returns.show', $return->id)
                ->with('success', "Sales Return #{$return->return_number} processed successfully.");
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

        $salesReturn = SalesReturn::forStore($store->id)
            ->with(['sale', 'customer', 'items.medicine', 'items.batch'])
            ->findOrFail($id);

        return view('store.sales-returns.show', compact('store', 'salesReturn'));
    }
}
