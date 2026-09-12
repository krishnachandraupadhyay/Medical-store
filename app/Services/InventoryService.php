<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\BatchStatus;
use App\Enums\MovementType;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\StockCountStatus;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(
        protected ?NotificationService $notificationService = null
    ) {
        $this->notificationService ??= app(NotificationService::class);
    }

    /**
     * Create a new batch record for a medicine in the store.
     * Optionally records opening stock movement if opening_stock > 0.
     */
    public function createBatch(Store $store, Medicine $medicine, array $data, ?int $userId = null): Batch
    {
        return DB::transaction(function () use ($store, $medicine, $data, $userId) {
            $openingStock = max(0, (int) ($data['opening_stock'] ?? 0));

            $batch = Batch::create([
                'store_id' => $store->id,
                'medicine_id' => $medicine->id,
                'batch_number' => trim($data['batch_number']),
                'barcode' => ! empty($data['barcode']) ? trim($data['barcode']) : null,
                'secondary_barcode' => ! empty($data['secondary_barcode']) ? trim($data['secondary_barcode']) : null,
                'manufacturing_date' => $data['manufacturing_date'] ?? null,
                'expiry_date' => $data['expiry_date'],
                'purchase_price' => $data['purchase_price'] ?? 0.00,
                'selling_price' => $data['selling_price'] ?? 0.00,
                'mrp' => $data['mrp'] ?? 0.00,
                'quantity' => $openingStock,
                'reserved_quantity' => 0,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if ($openingStock > 0) {
                StockMovement::create([
                    'store_id' => $store->id,
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'type' => MovementType::OPENING_STOCK,
                    'quantity' => $openingStock,
                    'before_quantity' => 0,
                    'after_quantity' => $openingStock,
                    'reason' => 'Opening Stock',
                    'notes' => 'Initial opening stock recorded at batch creation.',
                    'reference_type' => Batch::class,
                    'reference_id' => $batch->id,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::INVENTORY,
                "Created batch [{$batch->batch_number}] for medicine [{$medicine->name}] with initial stock {$openingStock}.",
                $batch,
                null,
                $batch->toArray()
            );

            return $batch;
        });
    }

    /**
     * Adjust stock for an existing batch with atomic concurrency lock.
     *
     * @throws ValidationException
     */
    public function adjustStock(
        Store $store,
        Batch $batch,
        MovementType $type,
        int $quantity,
        string $reason,
        ?string $notes = null,
        ?int $userId = null,
        ?Model $reference = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Adjustment quantity must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($store, $batch, $type, $quantity, $reason, $notes, $userId, $reference) {
            // Lock batch record for update to guarantee strict concurrency safety
            /** @var Batch $lockedBatch */
            $lockedBatch = Batch::where('id', $batch->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            $beforeQuantity = $lockedBatch->quantity;

            if ($type === MovementType::ADJUSTMENT_OUT || $type === MovementType::STOCK_OUT) {
                if ($quantity > $beforeQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => "Cannot adjust out {$quantity} units. Available stock is only {$beforeQuantity}.",
                    ]);
                }
                $afterQuantity = $beforeQuantity - $quantity;
            } elseif ($type === MovementType::ADJUSTMENT_IN || $type === MovementType::STOCK_IN || $type === MovementType::OPENING_STOCK) {
                $afterQuantity = $beforeQuantity + $quantity;
            } else {
                throw ValidationException::withMessages([
                    'type' => 'Unsupported stock adjustment type.',
                ]);
            }

            $lockedBatch->quantity = $afterQuantity;
            $lockedBatch->updated_by = $userId;
            $lockedBatch->save();

            $movement = StockMovement::create([
                'store_id' => $store->id,
                'medicine_id' => $lockedBatch->medicine_id,
                'batch_id' => $lockedBatch->id,
                'type' => $type,
                'quantity' => $quantity,
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $afterQuantity,
                'reason' => $reason,
                'notes' => $notes,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->getKey(),
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::INVENTORY,
                "Stock adjusted ({$type->value}) for batch [{$lockedBatch->batch_number}]: {$beforeQuantity} -> {$afterQuantity} (qty: {$quantity}). Reason: {$reason}",
                $lockedBatch,
                ['quantity' => $beforeQuantity],
                ['quantity' => $afterQuantity]
            );

            return $movement;
        });
    }

    /**
     * Process stock intake from a completed purchase.
     */
    public function addStockFromPurchase(Store $store, Purchase $purchase, ?int $userId = null): void
    {
        DB::transaction(function () use ($store, $purchase, $userId) {
            $purchase->loadMissing('items.medicine');

            foreach ($purchase->items as $item) {
                $totalQuantity = $item->totalQuantity();
                if ($totalQuantity <= 0) {
                    continue;
                }

                // Check if a batch with this number already exists for this medicine and store
                $batch = Batch::where('store_id', $store->id)
                    ->where('medicine_id', $item->medicine_id)
                    ->where('batch_number', $item->batch_number)
                    ->lockForUpdate()
                    ->first();

                if (! $batch) {
                    $batch = Batch::create([
                        'store_id' => $store->id,
                        'medicine_id' => $item->medicine_id,
                        'batch_number' => $item->batch_number,
                        'manufacturing_date' => $item->manufacturing_date,
                        'expiry_date' => $item->expiry_date,
                        'purchase_price' => $item->purchase_price,
                        'selling_price' => $item->selling_price ?? $item->mrp,
                        'mrp' => $item->mrp,
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'status' => 'active',
                        'notes' => "Created via Purchase Invoice #{$purchase->invoice_number}",
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                } else {
                    // Update latest purchase price & MRP on existing batch
                    $batch->purchase_price = $item->purchase_price;
                    $batch->mrp = $item->mrp;
                    if ($item->selling_price) {
                        $batch->selling_price = $item->selling_price;
                    }
                    if ($item->expiry_date) {
                        $batch->expiry_date = $item->expiry_date;
                    }
                }

                $beforeQuantity = $batch->quantity;
                $afterQuantity = $beforeQuantity + $totalQuantity;

                $batch->quantity = $afterQuantity;
                $batch->updated_by = $userId;
                $batch->save();

                // Link batch back to purchase item
                $item->batch_id = $batch->id;
                $item->save();

                // Create Stock Movement Ledger entry
                StockMovement::create([
                    'store_id' => $store->id,
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $batch->id,
                    'type' => MovementType::PURCHASE_IN,
                    'quantity' => $totalQuantity,
                    'before_quantity' => $beforeQuantity,
                    'after_quantity' => $afterQuantity,
                    'reason' => "Purchase Invoice #{$purchase->invoice_number}",
                    'notes' => "Received {$item->quantity} paid + {$item->free_quantity} free units.",
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Deduct stock for a completed sale.
     *
     * @throws ValidationException
     */
    public function deductStockForSale(Store $store, Sale $sale, ?int $userId = null): void
    {
        DB::transaction(function () use ($store, $sale, $userId) {
            $sale->loadMissing('items.medicine');

            foreach ($sale->items as $item) {
                if ($item->quantity <= 0) {
                    continue;
                }

                /** @var Batch $batch */
                $batch = Batch::where('id', $item->batch_id)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $batch->canBeSold()) {
                    $medName = $item->medicine?->name ?? 'Medicine';
                    throw ValidationException::withMessages([
                        'stock' => "Batch [{$batch->batch_number}] for {$medName} cannot be sold because it is {$batch->status} or expired.",
                    ]);
                }

                $beforeQuantity = $batch->quantity;

                if ($batch->quantity < $item->quantity) {
                    $medName = $item->medicine?->name ?? 'Medicine';
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock in batch [{$batch->batch_number}] for {$medName}. Available: {$beforeQuantity}, Requested: {$item->quantity}.",
                    ]);
                }

                $afterQuantity = $beforeQuantity - $item->quantity;
                $batch->quantity = $afterQuantity;
                $batch->updated_by = $userId;
                $batch->save();

                StockMovement::create([
                    'store_id' => $store->id,
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $batch->id,
                    'type' => MovementType::SALE_OUT,
                    'quantity' => $item->quantity,
                    'before_quantity' => $beforeQuantity,
                    'after_quantity' => $afterQuantity,
                    'reason' => "Sale Invoice #{$sale->invoice_number}",
                    'notes' => "Sold {$item->quantity} units to {$sale->customer_name}.",
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Restore stock into batches for a completed sales return.
     */
    public function restoreStockForSalesReturn(Store $store, SalesReturn $salesReturn, ?int $userId = null): void
    {
        DB::transaction(function () use ($store, $salesReturn, $userId) {
            $salesReturn->loadMissing('items.medicine');

            foreach ($salesReturn->items as $item) {
                if ($item->quantity <= 0) {
                    continue;
                }

                /** @var Batch $batch */
                $batch = Batch::where('id', $item->batch_id)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $beforeQuantity = $batch->quantity;
                $afterQuantity = $beforeQuantity + $item->quantity;

                $batch->quantity = $afterQuantity;
                $batch->updated_by = $userId;
                $batch->save();

                StockMovement::create([
                    'store_id' => $store->id,
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $batch->id,
                    'type' => MovementType::SALES_RETURN_IN,
                    'quantity' => $item->quantity,
                    'before_quantity' => $beforeQuantity,
                    'after_quantity' => $afterQuantity,
                    'reason' => "Sales Return #{$salesReturn->return_number}",
                    'notes' => "Customer returned {$item->quantity} units. Reason: {$item->reason}",
                    'reference_type' => SalesReturn::class,
                    'reference_id' => $salesReturn->id,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Deduct stock from batches for a completed purchase return.
     *
     * @throws ValidationException
     */
    public function deductStockForPurchaseReturn(Store $store, PurchaseReturn $purchaseReturn, ?int $userId = null): void
    {
        DB::transaction(function () use ($store, $purchaseReturn, $userId) {
            $purchaseReturn->loadMissing('items.medicine');

            foreach ($purchaseReturn->items as $item) {
                if ($item->quantity <= 0) {
                    continue;
                }

                /** @var Batch $batch */
                $batch = Batch::where('id', $item->batch_id)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $beforeQuantity = $batch->quantity;

                if ($batch->quantity < $item->quantity) {
                    $medName = $item->medicine?->name ?? 'Medicine';
                    throw ValidationException::withMessages([
                        'stock' => "Cannot return {$item->quantity} units of batch [{$batch->batch_number}] for {$medName}. Current stock is only {$beforeQuantity}.",
                    ]);
                }

                $afterQuantity = $beforeQuantity - $item->quantity;
                $batch->quantity = $afterQuantity;
                $batch->updated_by = $userId;
                $batch->save();

                StockMovement::create([
                    'store_id' => $store->id,
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $batch->id,
                    'type' => MovementType::PURCHASE_RETURN_OUT,
                    'quantity' => $item->quantity,
                    'before_quantity' => $beforeQuantity,
                    'after_quantity' => $afterQuantity,
                    'reason' => "Purchase Return #{$purchaseReturn->return_number}",
                    'notes' => "Returned {$item->quantity} units to supplier. Reason: {$item->reason}",
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $purchaseReturn->id,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Create a stock count reconciliation draft.
     */
    public function createStockCount(Store $store, array $data, array $items = [], ?int $userId = null): StockCount
    {
        return DB::transaction(function () use ($store, $data, $items, $userId) {
            $countNumber = StockCount::generateStockCountNumber($store->id);

            $stockCount = StockCount::create([
                'store_id' => $store->id,
                'count_number' => $countNumber,
                'count_date' => $data['count_date'] ?? now()->toDateString(),
                'status' => StockCountStatus::DRAFT,
                'scope' => $data['scope'] ?? 'full',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $totalItems = 0;
            $totalVarianceUnits = 0;
            $totalVarianceCost = 0.00;

            foreach ($items as $itemData) {
                $batchId = (int) $itemData['batch_id'];
                $batch = Batch::where('id', $batchId)
                    ->where('store_id', $store->id)
                    ->first();

                if (! $batch) {
                    continue;
                }

                $physicalQuantity = max(0, (int) ($itemData['physical_quantity'] ?? 0));
                $systemQuantity = (int) $batch->quantity;
                $variance = $physicalQuantity - $systemQuantity;
                $unitCost = (float) $batch->purchase_price;
                $varianceCost = round($variance * $unitCost, 2);

                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'store_id' => $store->id,
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'variance_quantity' => $variance,
                    'unit_cost' => $unitCost,
                    'variance_cost' => $varianceCost,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalItems++;
                $totalVarianceUnits += $variance;
                $totalVarianceCost += $varianceCost;
            }

            $stockCount->update([
                'total_items' => $totalItems,
                'total_variance_quantity' => $totalVarianceUnits,
                'total_variance_cost' => $totalVarianceCost,
            ]);

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::INVENTORY,
                "Created Stock Count Draft #{$stockCount->count_number} with {$totalItems} items.",
                $stockCount,
                null,
                $stockCount->toArray()
            );

            return $stockCount;
        });
    }

    /**
     * Add or update an item in a draft stock count.
     *
     * @throws ValidationException
     */
    public function addItemToStockCount(
        Store $store,
        StockCount $stockCount,
        int $batchId,
        int $physicalQuantity,
        ?string $notes = null
    ): StockCountItem {
        if (! $stockCount->isDraft() || $stockCount->store_id !== $store->id) {
            throw ValidationException::withMessages([
                'stock_count' => 'Only draft stock counts for the current store can be modified.',
            ]);
        }

        if ($physicalQuantity < 0) {
            throw ValidationException::withMessages([
                'physical_quantity' => 'Physical quantity cannot be negative.',
            ]);
        }

        $batch = Batch::where('id', $batchId)
            ->where('store_id', $store->id)
            ->firstOrFail();

        $systemQuantity = (int) $batch->quantity;
        $variance = $physicalQuantity - $systemQuantity;
        $unitCost = (float) $batch->purchase_price;
        $varianceCost = round($variance * $unitCost, 2);

        $item = StockCountItem::updateOrCreate(
            [
                'stock_count_id' => $stockCount->id,
                'batch_id' => $batch->id,
            ],
            [
                'store_id' => $store->id,
                'medicine_id' => $batch->medicine_id,
                'system_quantity' => $systemQuantity,
                'physical_quantity' => $physicalQuantity,
                'variance_quantity' => $variance,
                'unit_cost' => $unitCost,
                'variance_cost' => $varianceCost,
                'notes' => $notes,
            ]
        );

        $stockCount->update([
            'total_items' => $stockCount->items()->count(),
            'total_variance_quantity' => (int) $stockCount->items()->sum('variance_quantity'),
            'total_variance_cost' => (float) $stockCount->items()->sum('variance_cost'),
        ]);

        return $item;
    }

    /**
     * Remove an item from a draft stock count.
     *
     * @throws ValidationException
     */
    public function removeItemFromStockCount(Store $store, StockCount $stockCount, int $itemId): void
    {
        if (! $stockCount->isDraft() || $stockCount->store_id !== $store->id) {
            throw ValidationException::withMessages([
                'stock_count' => 'Only draft stock counts for the current store can be modified.',
            ]);
        }

        $item = StockCountItem::where('id', $itemId)
            ->where('stock_count_id', $stockCount->id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        $item->delete();

        $stockCount->update([
            'total_items' => $stockCount->items()->count(),
            'total_variance_quantity' => (int) $stockCount->items()->sum('variance_quantity'),
            'total_variance_cost' => (float) $stockCount->items()->sum('variance_cost'),
        ]);
    }

    /**
     * Complete and atomically reconcile stock counts with row locks.
     *
     * @throws ValidationException
     */
    public function completeStockCount(Store $store, StockCount $stockCount, ?int $userId = null): StockCount
    {
        return DB::transaction(function () use ($store, $stockCount, $userId) {
            /** @var StockCount $lockedCount */
            $lockedCount = StockCount::where('id', $stockCount->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedCount->isDraft()) {
                throw ValidationException::withMessages([
                    'stock_count' => 'Only draft stock counts can be completed.',
                ]);
            }

            $items = StockCountItem::where('stock_count_id', $lockedCount->id)
                ->where('store_id', $store->id)
                ->with('medicine')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Stock count must contain at least one medicine batch item before completion.',
                ]);
            }

            $totalVarianceUnits = 0;
            $totalVarianceCost = 0.00;

            foreach ($items as $item) {
                /** @var Batch $batch */
                $batch = Batch::where('id', $item->batch_id)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentSystemQty = (int) $batch->quantity;
                $physicalQty = (int) $item->physical_quantity;
                $finalVariance = $physicalQty - $currentSystemQty;
                $unitCost = (float) $batch->purchase_price;
                $varianceCost = round($finalVariance * $unitCost, 2);

                $item->system_quantity = $currentSystemQty;
                $item->variance_quantity = $finalVariance;
                $item->unit_cost = $unitCost;
                $item->variance_cost = $varianceCost;
                $item->save();

                $totalVarianceUnits += $finalVariance;
                $totalVarianceCost += $varianceCost;

                if ($finalVariance !== 0) {
                    if ($finalVariance > 0) {
                        $type = MovementType::STOCK_RECONCILIATION_IN;
                        $movementQty = $finalVariance;
                        $newQty = $currentSystemQty + $movementQty;
                    } else {
                        $shortage = abs($finalVariance);
                        if ($shortage > $currentSystemQty) {
                            $medName = $item->medicine?->name ?? 'Medicine';
                            throw ValidationException::withMessages([
                                'stock' => "Cannot deduct {$shortage} units from batch [{$batch->batch_number}] for {$medName}. Only {$currentSystemQty} units are in stock.",
                            ]);
                        }
                        $type = MovementType::STOCK_RECONCILIATION_OUT;
                        $movementQty = $shortage;
                        $newQty = $currentSystemQty - $movementQty;
                    }

                    $batch->quantity = $newQty;
                    if ($newQty === 0 && $batch->status === 'active') {
                        $batch->status = 'depleted';
                    } elseif ($newQty > 0 && $batch->status === 'depleted') {
                        $batch->status = 'active';
                    }
                    $batch->updated_by = $userId;
                    $batch->save();

                    StockMovement::create([
                        'store_id' => $store->id,
                        'medicine_id' => $item->medicine_id,
                        'batch_id' => $batch->id,
                        'type' => $type,
                        'quantity' => $movementQty,
                        'before_quantity' => $currentSystemQty,
                        'after_quantity' => $newQty,
                        'reason' => "Stock Reconciliation #{$lockedCount->count_number}",
                        'notes' => $item->notes ?? "Physical count reconciled: physical={$physicalQty}, was={$currentSystemQty}",
                        'reference_type' => StockCount::class,
                        'reference_id' => $lockedCount->id,
                        'created_by' => $userId,
                        'created_at' => now(),
                    ]);
                }
            }

            $lockedCount->update([
                'status' => StockCountStatus::COMPLETED,
                'completed_at' => now(),
                'approved_by' => $userId,
                'total_items' => $items->count(),
                'total_variance_quantity' => $totalVarianceUnits,
                'total_variance_cost' => $totalVarianceCost,
            ]);

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::INVENTORY,
                "Completed Stock Count #{$lockedCount->count_number} with net variance of {$totalVarianceUnits} units.",
                $lockedCount,
                null,
                $lockedCount->toArray()
            );

            // In-app notification
            $this->notificationService?->notify([
                'store_id' => $store->id,
                'user_id' => $userId,
                'type' => NotificationType::STOCK_RECONCILIATION_COMPLETED,
                'title' => 'Stock Reconciliation Completed',
                'message' => "Stock count #{$lockedCount->count_number} completed. Reconciled {$items->count()} items with net variance {$totalVarianceUnits} units.",
                'priority' => NotificationPriority::NORMAL,
                'action_url' => route('store.inventory.stock-counts.show', $lockedCount),
                'reference_type' => StockCount::class,
                'reference_id' => $lockedCount->id,
                'alert_key' => "stock_count_completed_{$lockedCount->id}",
            ]);

            return $lockedCount;
        });
    }

    /**
     * Cancel a draft stock count.
     *
     * @throws ValidationException
     */
    public function cancelStockCount(Store $store, StockCount $stockCount, ?string $reason = null, ?int $userId = null): StockCount
    {
        return DB::transaction(function () use ($store, $stockCount, $reason) {
            /** @var StockCount $lockedCount */
            $lockedCount = StockCount::where('id', $stockCount->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedCount->isDraft()) {
                throw ValidationException::withMessages([
                    'stock_count' => 'Only draft stock counts can be cancelled.',
                ]);
            }

            $notes = $lockedCount->notes;
            if ($reason) {
                $notes = trim($notes."\nCancellation reason: ".$reason);
            }

            $lockedCount->update([
                'status' => StockCountStatus::CANCELLED,
                'notes' => $notes,
            ]);

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::INVENTORY,
                "Cancelled Stock Count Draft #{$lockedCount->count_number}. Reason: {$reason}",
                $lockedCount,
                null,
                $lockedCount->toArray()
            );

            return $lockedCount;
        });
    }

    /**
     * Record damaged stock write-off.
     *
     * @throws ValidationException
     */
    public function recordDamagedStock(
        Store $store,
        Batch $batch,
        int $quantity,
        string $reason,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        return $this->performControlledStockOut(
            $store,
            $batch,
            MovementType::DAMAGED_STOCK_OUT,
            $quantity,
            $reason,
            $notes,
            $userId,
            NotificationType::DAMAGED_STOCK_RECORDED,
            'Damaged Stock Recorded'
        );
    }

    /**
     * Record lost or missing stock write-off.
     *
     * @throws ValidationException
     */
    public function recordLostStock(
        Store $store,
        Batch $batch,
        int $quantity,
        string $reason,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        return $this->performControlledStockOut(
            $store,
            $batch,
            MovementType::LOST_STOCK_OUT,
            $quantity,
            $reason,
            $notes,
            $userId,
            NotificationType::LOST_STOCK_RECORDED,
            'Lost Stock Recorded'
        );
    }

    /**
     * Process expired stock disposal write-off.
     *
     * @throws ValidationException
     */
    public function processExpiredStock(
        Store $store,
        Batch $batch,
        int $quantity,
        string $reason,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        if (! $batch->isExpired() && $batch->expiry_date->isFuture()) {
            throw ValidationException::withMessages([
                'expiry' => "Batch [{$batch->batch_number}] has not reached expiry date ({$batch->expiry_date->format('Y-m-d')}). Only expired batches can be processed through expiry disposal.",
            ]);
        }

        return $this->performControlledStockOut(
            $store,
            $batch,
            MovementType::EXPIRED_STOCK_OUT,
            $quantity,
            $reason,
            $notes,
            $userId,
            NotificationType::EXPIRED_STOCK_PROCESSED,
            'Expired Stock Disposal Processed'
        );
    }

    /**
     * Common helper for controlled stock-out operations (Damaged, Lost, Expired).
     *
     * @throws ValidationException
     */
    protected function performControlledStockOut(
        Store $store,
        Batch $batch,
        MovementType $type,
        int $quantity,
        string $reason,
        ?string $notes = null,
        ?int $userId = null,
        ?NotificationType $notifType = null,
        ?string $notifTitle = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity to write off must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($store, $batch, $type, $quantity, $reason, $notes, $userId, $notifType, $notifTitle) {
            /** @var Batch $lockedBatch */
            $lockedBatch = Batch::where('id', $batch->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            $beforeQuantity = (int) $lockedBatch->quantity;

            if ($beforeQuantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Cannot write off {$quantity} units. Batch [{$lockedBatch->batch_number}] only has {$beforeQuantity} available units.",
                ]);
            }

            $afterQuantity = $beforeQuantity - $quantity;
            $lockedBatch->quantity = $afterQuantity;
            if ($afterQuantity === 0) {
                $lockedBatch->status = $type === MovementType::EXPIRED_STOCK_OUT ? 'expired' : 'depleted';
            }
            $lockedBatch->updated_by = $userId;
            $lockedBatch->save();

            $movement = StockMovement::create([
                'store_id' => $store->id,
                'medicine_id' => $lockedBatch->medicine_id,
                'batch_id' => $lockedBatch->id,
                'type' => $type,
                'quantity' => $quantity,
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $afterQuantity,
                'reason' => $reason,
                'notes' => $notes,
                'reference_type' => Batch::class,
                'reference_id' => $lockedBatch->id,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::INVENTORY,
                "{$type->label()}: {$quantity} units from batch [{$lockedBatch->batch_number}]. Reason: {$reason}",
                $movement,
                null,
                $movement->toArray()
            );

            if ($notifType && $notifTitle) {
                $this->notificationService?->notify([
                    'store_id' => $store->id,
                    'user_id' => $userId,
                    'type' => $notifType,
                    'title' => $notifTitle,
                    'message' => "{$quantity} units written off from batch [{$lockedBatch->batch_number}]. Reason: {$reason}",
                    'priority' => NotificationPriority::NORMAL,
                    'action_url' => route('store.inventory.show', $lockedBatch),
                    'reference_type' => StockMovement::class,
                    'reference_id' => $movement->id,
                ]);
            }

            return $movement;
        });
    }

    /**
     * Change batch status (e.g. block, unblock, mark inactive).
     *
     * @throws ValidationException
     */
    public function setBatchStatus(
        Store $store,
        Batch $batch,
        BatchStatus|string $status,
        ?string $reason = null,
        ?int $userId = null
    ): Batch {
        $newStatus = $status instanceof BatchStatus ? $status->value : (string) $status;

        return DB::transaction(function () use ($store, $batch, $newStatus, $reason, $userId) {
            /** @var Batch $lockedBatch */
            $lockedBatch = Batch::where('id', $batch->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $lockedBatch->status;
            $lockedBatch->status = $newStatus;
            $lockedBatch->updated_by = $userId;
            $lockedBatch->save();

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::INVENTORY,
                "Changed batch [{$lockedBatch->batch_number}] status from {$oldStatus} to {$newStatus}. Reason: {$reason}",
                $lockedBatch,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'reason' => $reason]
            );

            if ($newStatus === BatchStatus::BLOCKED->value) {
                $this->notificationService?->notify([
                    'store_id' => $store->id,
                    'user_id' => $userId,
                    'type' => NotificationType::BATCH_BLOCKED,
                    'title' => 'Batch Quarantined / Blocked',
                    'message' => "Batch [{$lockedBatch->batch_number}] has been blocked from sales. Reason: {$reason}",
                    'priority' => NotificationPriority::HIGH,
                    'action_url' => route('store.inventory.show', $lockedBatch),
                    'reference_type' => Batch::class,
                    'reference_id' => $lockedBatch->id,
                    'alert_key' => "batch_blocked_{$lockedBatch->id}",
                ]);
            }

            return $lockedBatch;
        });
    }

    /**
     * Calculate comprehensive inventory valuation metrics for the store.
     *
     * @return array<string, mixed>
     */
    public function calculateInventoryValuation(Store $store): array
    {
        $today = now()->toDateString();
        $ninetyDays = now()->addDays(90)->toDateString();

        $batches = Batch::where('store_id', $store->id)
            ->where('quantity', '>', 0)
            ->with(['medicine.category'])
            ->get();

        $totalUnits = 0;
        $purchaseValuation = 0.00;
        $sellingValuation = 0.00;
        $expiredValuation = 0.00;
        $expiredUnits = 0;
        $expiringSoonValuation = 0.00;
        $expiringSoonUnits = 0;
        $blockedValuation = 0.00;
        $blockedUnits = 0;
        $categoryBreakdown = [];

        foreach ($batches as $batch) {
            $qty = (int) $batch->quantity;
            $purchasePrice = (float) $batch->purchase_price;
            $sellingPrice = (float) $batch->selling_price;
            $linePurchaseVal = $qty * $purchasePrice;
            $lineSellingVal = $qty * $sellingPrice;

            $totalUnits += $qty;
            $purchaseValuation += $linePurchaseVal;
            $sellingValuation += $lineSellingVal;

            if ($batch->expiry_date->toDateString() < $today) {
                $expiredValuation += $linePurchaseVal;
                $expiredUnits += $qty;
            } elseif ($batch->expiry_date->toDateString() <= $ninetyDays) {
                $expiringSoonValuation += $linePurchaseVal;
                $expiringSoonUnits += $qty;
            }

            if ($batch->status === 'blocked') {
                $blockedValuation += $linePurchaseVal;
                $blockedUnits += $qty;
            }

            $categoryName = $batch->medicine?->category?->name ?? 'Uncategorized';
            if (! isset($categoryBreakdown[$categoryName])) {
                $categoryBreakdown[$categoryName] = [
                    'category' => $categoryName,
                    'total_units' => 0,
                    'purchase_value' => 0.00,
                    'selling_value' => 0.00,
                ];
            }
            $categoryBreakdown[$categoryName]['total_units'] += $qty;
            $categoryBreakdown[$categoryName]['purchase_value'] += $linePurchaseVal;
            $categoryBreakdown[$categoryName]['selling_value'] += $lineSellingVal;
        }

        // Loss write-offs summary from StockMovement in past 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $lossMovements = StockMovement::where('store_id', $store->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('type', [
                MovementType::DAMAGED_STOCK_OUT,
                MovementType::LOST_STOCK_OUT,
                MovementType::EXPIRED_STOCK_OUT,
            ])
            ->with('batch')
            ->get();

        $lossSummary = [
            'damaged_units' => 0,
            'damaged_cost' => 0.00,
            'lost_units' => 0,
            'lost_cost' => 0.00,
            'expired_units' => 0,
            'expired_cost' => 0.00,
            'total_loss_cost' => 0.00,
        ];

        foreach ($lossMovements as $mov) {
            $cost = (float) ($mov->batch?->purchase_price ?? 0.00) * $mov->quantity;
            $lossSummary['total_loss_cost'] += $cost;

            if ($mov->type === MovementType::DAMAGED_STOCK_OUT) {
                $lossSummary['damaged_units'] += $mov->quantity;
                $lossSummary['damaged_cost'] += $cost;
            } elseif ($mov->type === MovementType::LOST_STOCK_OUT) {
                $lossSummary['lost_units'] += $mov->quantity;
                $lossSummary['lost_cost'] += $cost;
            } elseif ($mov->type === MovementType::EXPIRED_STOCK_OUT) {
                $lossSummary['expired_units'] += $mov->quantity;
                $lossSummary['expired_cost'] += $cost;
            }
        }

        return [
            'total_batches' => $batches->count(),
            'total_units' => $totalUnits,
            'purchase_valuation' => round($purchaseValuation, 2),
            'selling_valuation' => round($sellingValuation, 2),
            'potential_profit' => round($sellingValuation - $purchaseValuation, 2),
            'expired_units' => $expiredUnits,
            'expired_valuation' => round($expiredValuation, 2),
            'expiring_soon_units' => $expiringSoonUnits,
            'expiring_soon_valuation' => round($expiringSoonValuation, 2),
            'blocked_units' => $blockedUnits,
            'blocked_valuation' => round($blockedValuation, 2),
            'category_breakdown' => array_values($categoryBreakdown),
            'loss_summary_30d' => $lossSummary,
        ];
    }
}
