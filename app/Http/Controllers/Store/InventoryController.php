<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\MovementType;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Inventory\AdjustStockRequest;
use App\Http\Requests\Store\Inventory\StoreBatchRequest;
use App\Http\Requests\Store\Inventory\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\StockMovement;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Display current store inventory and batches.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Batch::forStore($store->id)->with('medicine.unit');

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($medicineId = $request->input('medicine_id')) {
            $query->where('medicine_id', $medicineId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by Stock Status
        $stockFilter = $request->input('stock_status');
        if ($stockFilter === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        } elseif ($stockFilter === 'low_stock') {
            $query->where('quantity', '>', 0)
                ->whereHas('medicine', function ($mq) {
                    $mq->whereColumn('batches.quantity', '<=', 'medicines.reorder_level');
                });
        } elseif ($stockFilter === 'in_stock') {
            $query->where('quantity', '>', 0);
        }

        // Filter by Expiry Status
        $expiryFilter = $request->input('expiry_status');
        $today = Carbon::today()->toDateString();
        $soonThreshold = Carbon::today()->addDays(90)->toDateString();

        if ($expiryFilter === 'expired') {
            $query->where('expiry_date', '<', $today);
        } elseif ($expiryFilter === 'expiring_soon') {
            $query->where('expiry_date', '>=', $today)
                ->where('expiry_date', '<=', $soonThreshold);
        } elseif ($expiryFilter === 'valid') {
            $query->where('expiry_date', '>', $soonThreshold);
        }

        $batches = $query->orderBy('expiry_date')->paginate(20)->withQueryString();

        // Medicines list for filter dropdown
        $medicines = Medicine::forStore($store->id)->active()->orderBy('name')->get();

        // Summary counts
        $totalItemsCount = Batch::forStore($store->id)->sum('quantity');
        $outOfStockCount = Batch::forStore($store->id)->where('quantity', '<=', 0)->count();
        $expiredCount = Batch::forStore($store->id)->where('expiry_date', '<', $today)->count();
        $expiringSoonCount = Batch::forStore($store->id)
            ->where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', $soonThreshold)
            ->count();

        return view('store.inventory.index', compact(
            'store',
            'batches',
            'medicines',
            'totalItemsCount',
            'outOfStockCount',
            'expiredCount',
            'expiringSoonCount'
        ));
    }

    /**
     * Show form to create a new medicine batch.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::checkLimit($store, 'max_batches', 1)) {
            return redirect()->route('store.inventory.index')
                ->with('error', 'You have reached the maximum batch limit allowed by your plan. Please upgrade your plan.');
        }

        $medicines = Medicine::forStore($store->id)->active()->orderBy('name')->get();
        $selectedMedicineId = $request->input('medicine_id');

        return view('store.inventory.create', compact('store', 'medicines', 'selectedMedicineId'));
    }

    /**
     * Store a newly created batch.
     */
    public function store(StoreBatchRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::checkLimit($store, 'max_batches', 1)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You have reached the maximum batch limit allowed by your plan. Please upgrade your plan.');
        }

        $medicine = Medicine::forStore($store->id)->findOrFail($request->input('medicine_id'));

        $batch = $this->inventoryService->createBatch(
            $store,
            $medicine,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('store.inventory.show', $batch)
            ->with('status', "Batch [{$batch->batch_number}] for [{$medicine->name}] created successfully.");
    }

    /**
     * Display batch details and its stock movement history.
     */
    public function show(Batch $batch): View
    {
        $store = current_store();
        abort_unless($store && $batch->store_id === $store->id, 404);

        $batch->load(['medicine.category', 'medicine.unit', 'creator']);

        $movements = $batch->movements()
            ->with(['creator'])
            ->paginate(15);

        return view('store.inventory.show', compact('store', 'batch', 'movements'));
    }

    /**
     * Show the form for editing a batch (metadata & prices only, NOT direct stock quantity).
     */
    public function edit(Batch $batch): View
    {
        $store = current_store();
        abort_unless($store && $batch->store_id === $store->id, 404);

        $batch->load('medicine');

        return view('store.inventory.edit', compact('store', 'batch'));
    }

    /**
     * Update the specified batch.
     */
    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $batch->store_id === $store->id, 404);

        $oldValues = $batch->toArray();
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $batch->update($data);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::INVENTORY,
            "Updated batch [{$batch->batch_number}] details for [{$batch->medicine->name}].",
            $batch,
            $oldValues,
            $batch->fresh()->toArray()
        );

        return redirect()->route('store.inventory.show', $batch)
            ->with('status', "Batch [{$batch->batch_number}] updated successfully.");
    }

    /**
     * Perform an atomic stock adjustment on the batch.
     */
    public function adjust(AdjustStockRequest $request, Batch $batch): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $batch->store_id === $store->id, 404);

        $type = MovementType::from($request->input('type'));
        $quantity = (int) $request->input('quantity');
        $reason = $request->input('reason');
        $notes = $request->input('notes');

        $this->inventoryService->adjustStock(
            $store,
            $batch,
            $type,
            $quantity,
            $reason,
            $notes,
            auth()->id()
        );

        return redirect()->route('store.inventory.show', $batch)
            ->with('status', "Stock adjusted successfully ({$type->label()}: {$quantity}).");
    }

    /**
     * Display the overall stock movement history ledger for the store.
     */
    public function history(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = StockMovement::forStore($store->id)
            ->with(['medicine', 'batch', 'creator'])
            ->latest('id');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($medicineId = $request->input('medicine_id')) {
            $query->where('medicine_id', $medicineId);
        }

        if ($batchId = $request->input('batch_id')) {
            $query->where('batch_id', $batchId);
        }

        $movements = $query->paginate(25)->withQueryString();
        $medicines = Medicine::forStore($store->id)->orderBy('name')->get();

        return view('store.inventory.history', compact('store', 'movements', 'medicines'));
    }
}
