<?php

namespace App\Http\Controllers\Store;

use App\Enums\MovementType;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Inventory\ChangeBatchStatusRequest;
use App\Http\Requests\Store\Inventory\ProcessExpiredStockRequest;
use App\Http\Requests\Store\Inventory\RecordDamagedStockRequest;
use App\Http\Requests\Store\Inventory\RecordLostStockRequest;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockOperationController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Display damaged stock write-off history and form.
     */
    public function damagedIndex(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $movements = StockMovement::forStore($store->id)
            ->where('type', MovementType::DAMAGED_STOCK_OUT)
            ->with(['medicine', 'batch', 'creator'])
            ->latest('id')
            ->paginate(15);

        $availableBatches = Batch::forStore($store->id)
            ->where('quantity', '>', 0)
            ->with('medicine')
            ->orderBy('batch_number')
            ->get();

        $totalDamagedUnits = (int) StockMovement::forStore($store->id)
            ->where('type', MovementType::DAMAGED_STOCK_OUT)
            ->sum('quantity');

        return view('store.inventory.damaged.index', compact('store', 'movements', 'availableBatches', 'totalDamagedUnits'));
    }

    /**
     * Record damaged stock write-off.
     */
    public function damagedStore(RecordDamagedStockRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $batch = Batch::where('id', $request->input('batch_id'))
            ->where('store_id', $store->id)
            ->firstOrFail();

        $this->inventoryService->recordDamagedStock(
            $store,
            $batch,
            (int) $request->input('quantity'),
            $request->input('reason'),
            $request->input('notes'),
            auth()->id()
        );

        return redirect()->route('store.inventory.damaged.index')
            ->with('success', "Damaged stock of {$request->input('quantity')} units recorded successfully.");
    }

    /**
     * Display lost/missing stock write-off history and form.
     */
    public function lostIndex(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $movements = StockMovement::forStore($store->id)
            ->where('type', MovementType::LOST_STOCK_OUT)
            ->with(['medicine', 'batch', 'creator'])
            ->latest('id')
            ->paginate(15);

        $availableBatches = Batch::forStore($store->id)
            ->where('quantity', '>', 0)
            ->with('medicine')
            ->orderBy('batch_number')
            ->get();

        $totalLostUnits = (int) StockMovement::forStore($store->id)
            ->where('type', MovementType::LOST_STOCK_OUT)
            ->sum('quantity');

        return view('store.inventory.lost.index', compact('store', 'movements', 'availableBatches', 'totalLostUnits'));
    }

    /**
     * Record lost/missing stock write-off.
     */
    public function lostStore(RecordLostStockRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $batch = Batch::where('id', $request->input('batch_id'))
            ->where('store_id', $store->id)
            ->firstOrFail();

        $this->inventoryService->recordLostStock(
            $store,
            $batch,
            (int) $request->input('quantity'),
            $request->input('reason'),
            $request->input('notes'),
            auth()->id()
        );

        return redirect()->route('store.inventory.lost.index')
            ->with('success', "Lost stock of {$request->input('quantity')} units recorded successfully.");
    }

    /**
     * Display expired stock disposal management.
     */
    public function expiredIndex(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $expiredBatches = Batch::forStore($store->id)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', now()->toDateString())
            ->with('medicine')
            ->orderBy('expiry_date')
            ->get();

        $movements = StockMovement::forStore($store->id)
            ->where('type', MovementType::EXPIRED_STOCK_OUT)
            ->with(['medicine', 'batch', 'creator'])
            ->latest('id')
            ->paginate(15);

        $totalDisposedUnits = (int) StockMovement::forStore($store->id)
            ->where('type', MovementType::EXPIRED_STOCK_OUT)
            ->sum('quantity');

        return view('store.inventory.expired.index', compact('store', 'expiredBatches', 'movements', 'totalDisposedUnits'));
    }

    /**
     * Process disposal write-off for an expired batch.
     */
    public function expiredProcess(ProcessExpiredStockRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $batch = Batch::where('id', $request->input('batch_id'))
            ->where('store_id', $store->id)
            ->firstOrFail();

        $this->inventoryService->processExpiredStock(
            $store,
            $batch,
            (int) $request->input('quantity'),
            $request->input('reason'),
            $request->input('notes'),
            auth()->id()
        );

        return redirect()->route('store.inventory.expired.index')
            ->with('success', "Expired stock disposal of {$request->input('quantity')} units processed.");
    }

    /**
     * Change batch status (e.g. block / quarantine or activate).
     */
    public function changeBatchStatus(ChangeBatchStatusRequest $request, Batch $batch): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_if($batch->store_id !== $store->id, 403, 'Unauthorized batch.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $this->inventoryService->setBatchStatus(
            $store,
            $batch,
            $request->input('status'),
            $request->input('reason'),
            auth()->id()
        );

        return back()->with('success', "Batch [{$batch->batch_number}] status updated to {$request->input('status')}.");
    }

    /**
     * Display real-time Inventory Valuation & Metrics.
     */
    public function valuation(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        SubscriptionAccess::authorizeFeature('inventory_management');

        $valuation = $this->inventoryService->calculateInventoryValuation($store);

        return view('store.inventory.valuation', compact('store', 'valuation'));
    }
}
