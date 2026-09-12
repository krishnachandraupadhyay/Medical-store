<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\ReturnStatus;
use App\Events\PurchaseReturnCompleted;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create and complete a purchase return.
     *
     * @throws ValidationException
     */
    public function createPurchaseReturn(Store $store, Purchase $purchase, array $data, ?int $userId = null): PurchaseReturn
    {
        return DB::transaction(function () use ($store, $purchase, $data, $userId) {
            /** @var Purchase $lockedPurchase */
            $lockedPurchase = Purchase::where('id', $purchase->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedPurchase->isCompleted()) {
                throw ValidationException::withMessages([
                    'purchase' => 'Returns can only be created for completed purchases.',
                ]);
            }

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Purchase return must include at least one item.',
                ]);
            }

            $returnNumber = PurchaseReturn::generateReturnNumber($store->id);
            $returnDate = $data['return_date'] ?? now()->toDateString();
            $refundAmount = (float) ($data['refund_amount'] ?? 0.00);
            $reason = trim($data['reason'] ?? 'Supplier Return / Damaged Goods');

            $subtotal = 0.00;
            $totalTax = 0.00;
            $processedItems = [];

            foreach ($itemsData as $itemInput) {
                $purchaseItemId = (int) $itemInput['purchase_item_id'];
                $qty = (int) ($itemInput['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                /** @var PurchaseItem $purchaseItem */
                $purchaseItem = PurchaseItem::where('id', $purchaseItemId)
                    ->where('purchase_id', $lockedPurchase->id)
                    ->firstOrFail();

                // Check batch available stock
                $batch = Batch::where('id', $purchaseItem->batch_id)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->first();

                $availableInBatch = $batch ? $batch->quantity : 0;
                $alreadyReturned = $purchaseItem->alreadyReturnedQuantity();
                $maxReturnableFromPurchase = max(0, $purchaseItem->totalQuantity() - $alreadyReturned);

                if ($qty > $maxReturnableFromPurchase) {
                    $medName = $purchaseItem->medicine?->name ?? 'Medicine';
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$qty} units of {$medName}. Only {$maxReturnableFromPurchase} units remain returnable from this purchase.",
                    ]);
                }

                if ($qty > $availableInBatch) {
                    $medName = $purchaseItem->medicine?->name ?? 'Medicine';
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$qty} units of {$medName}. Current batch [{$purchaseItem->batch_number}] stock is only {$availableInBatch} units.",
                    ]);
                }

                $purchasePrice = (float) $purchaseItem->purchase_price;
                $gstRate = (float) $purchaseItem->gst_rate;
                $itemSubtotal = $purchasePrice * $qty;
                $itemTax = round($itemSubtotal * ($gstRate / 100), 2);
                $itemTotal = round($itemSubtotal + $itemTax, 2);

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $processedItems[] = [
                    'purchase_item_id' => $purchaseItem->id,
                    'medicine_id' => $purchaseItem->medicine_id,
                    'batch_id' => $purchaseItem->batch_id,
                    'quantity' => $qty,
                    'purchase_price' => $purchasePrice,
                    'mrp' => $purchaseItem->mrp,
                    'discount' => 0.00,
                    'gst_rate' => $gstRate,
                    'tax_amount' => $itemTax,
                    'line_total' => $itemTotal,
                    'reason' => $itemInput['reason'] ?? $reason,
                ];
            }

            if (empty($processedItems)) {
                throw ValidationException::withMessages([
                    'items' => 'At least one item must have a return quantity greater than zero.',
                ]);
            }

            $grandTotal = round($subtotal + $totalTax, 2);

            $purchaseReturn = PurchaseReturn::create([
                'store_id' => $store->id,
                'purchase_id' => $lockedPurchase->id,
                'supplier_id' => $lockedPurchase->supplier_id,
                'return_number' => $returnNumber,
                'return_date' => $returnDate,
                'status' => ReturnStatus::COMPLETED,
                'subtotal' => $subtotal,
                'discount' => 0.00,
                'tax' => $totalTax,
                'grand_total' => $grandTotal,
                'refund_amount' => $refundAmount > 0 ? $refundAmount : $grandTotal,
                'refund_status' => $refundAmount > 0 ? 'refunded' : 'pending',
                'reason' => $reason,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'completed_at' => now(),
            ]);

            foreach ($processedItems as $itemRow) {
                $itemRow['purchase_return_id'] = $purchaseReturn->id;
                PurchaseReturnItem::create($itemRow);
            }

            // Deduct stock from batch
            $this->inventoryService->deductStockForPurchaseReturn($store, $purchaseReturn, $userId);

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::PURCHASE_RETURNS,
                "Created and completed Purchase Return #{$purchaseReturn->return_number} for Purchase #{$lockedPurchase->invoice_number} (total ₹{$grandTotal}).",
                $purchaseReturn,
                null,
                $purchaseReturn->toArray()
            );

            DB::afterCommit(fn () => event(new PurchaseReturnCompleted($purchaseReturn)));

            return $purchaseReturn;
        });
    }
}
