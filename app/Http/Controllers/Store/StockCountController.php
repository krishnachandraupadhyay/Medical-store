<?php

namespace App\Http\Controllers\Store;

use App\Enums\StockCountStatus;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Inventory\AddStockCountItemRequest;
use App\Http\Requests\Store\Inventory\StoreStockCountRequest;
use App\Models\Batch;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockCountController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Display listing of stock reconciliation sessions.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $query = StockCount::forStore($store->id)->with(['creator', 'approver']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where('count_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('count_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('count_date', '<=', $dateTo);
        }

        $stockCounts = $query->latest('id')->paginate(15)->withQueryString();

        $metrics = [
            'total' => StockCount::forStore($store->id)->count(),
            'draft' => StockCount::forStore($store->id)->where('status', StockCountStatus::DRAFT)->count(),
            'completed' => StockCount::forStore($store->id)->where('status', StockCountStatus::COMPLETED)->count(),
            'cancelled' => StockCount::forStore($store->id)->where('status', StockCountStatus::CANCELLED)->count(),
        ];

        return view('store.inventory.stock-counts.index', compact('store', 'stockCounts', 'metrics'));
    }

    /**
     * Show creation form for a new stock count reconciliation.
     */
    public function create(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $activeBatches = Batch::forStore($store->id)
            ->with('medicine')
            ->where('quantity', '>', 0)
            ->orderBy('batch_number')
            ->get();

        return view('store.inventory.stock-counts.create', compact('store', 'activeBatches'));
    }

    /**
     * Store newly created draft stock count.
     */
    public function store(StoreStockCountRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $items = $request->input('items', []);

        $stockCount = $this->inventoryService->createStockCount(
            $store,
            $request->validated(),
            $items,
            auth()->id()
        );

        return redirect()->route('store.inventory.stock-counts.show', $stockCount)
            ->with('success', "Stock Count draft #{$stockCount->count_number} created. Add physical counts and reconcile.");
    }

    /**
     * Show details of a stock count reconciliation.
     */
    public function show(StockCount $stockCount): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_if($stockCount->store_id !== $store->id, 403, 'Unauthorized access to stock count.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $stockCount->load(['creator', 'approver', 'items.medicine', 'items.batch']);

        $availableBatches = [];
        if ($stockCount->isDraft()) {
            $existingBatchIds = $stockCount->items->pluck('batch_id')->toArray();
            $availableBatches = Batch::forStore($store->id)
                ->with('medicine')
                ->whereNotIn('id', $existingBatchIds)
                ->orderBy('batch_number')
                ->get();
        }

        return view('store.inventory.stock-counts.show', compact('store', 'stockCount', 'availableBatches'));
    }

    /**
     * Add or update an item count in a draft session.
     */
    public function addItem(AddStockCountItemRequest $request, StockCount $stockCount): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_if($stockCount->store_id !== $store->id, 403, 'Unauthorized access.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $this->inventoryService->addItemToStockCount(
            $store,
            $stockCount,
            (int) $request->input('batch_id'),
            (int) $request->input('physical_quantity'),
            $request->input('notes')
        );

        return redirect()->route('store.inventory.stock-counts.show', $stockCount)
            ->with('success', 'Count item updated successfully.');
    }

    /**
     * Remove an item from a draft session.
     */
    public function removeItem(StockCount $stockCount, StockCountItem $item): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_if($stockCount->store_id !== $store->id || $item->store_id !== $store->id, 403, 'Unauthorized.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $this->inventoryService->removeItemFromStockCount($store, $stockCount, $item->id);

        return redirect()->route('store.inventory.stock-counts.show', $stockCount)
            ->with('success', 'Count item removed from session.');
    }

    /**
     * Complete and reconcile stock count session.
     */
    public function complete(StockCount $stockCount): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_if($stockCount->store_id !== $store->id, 403, 'Unauthorized.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $this->inventoryService->completeStockCount($store, $stockCount, auth()->id());

        return redirect()->route('store.inventory.stock-counts.show', $stockCount)
            ->with('success', "Stock Count #{$stockCount->count_number} successfully completed and reconciled.");
    }

    /**
     * Cancel a draft stock count session.
     */
    public function cancel(Request $request, StockCount $stockCount): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_if($stockCount->store_id !== $store->id, 403, 'Unauthorized.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $reason = trim((string) $request->input('reason', 'Cancelled by user'));
        $this->inventoryService->cancelStockCount($store, $stockCount, $reason, auth()->id());

        return redirect()->route('store.inventory.stock-counts.show', $stockCount)
            ->with('success', "Stock Count #{$stockCount->count_number} has been cancelled.");
    }
}
