<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PaymentMethod;
use App\Enums\ReturnStatus;
use App\Enums\StorePaymentType;
use App\Events\PurchaseReturnCompleted;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Store;
use App\Models\StorePayment;
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
                    'purchase_id' => 'Returns can only be created for completed purchases.',
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

                /** @var PurchaseItem|null $purchaseItem */
                $purchaseItem = $lockedPurchase->items->firstWhere('id', $purchaseItemId);
                if (! $purchaseItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid purchase line item selected.',
                    ]);
                }

                $returnableQty = $purchaseItem->returnableQuantity();
                if ($qty > $returnableQty) {
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$qty} units of {$purchaseItem->medicine->name}. Maximum returnable is {$returnableQty}.",
                    ]);
                }

                $purchasePrice = (float) $purchaseItem->purchase_price;
                $mrp = (float) ($purchaseItem->mrp ?? 0.00);
                $discount = (float) ($purchaseItem->discount ?? 0.00);
                $gstRate = (float) ($purchaseItem->gst_rate ?? 0.00);
                $lineSubtotal = round($purchasePrice * $qty, 2);

                // Proportional tax per unit
                $taxPerUnit = $purchaseItem->quantity > 0
                    ? round((float) $purchaseItem->tax_amount / (float) $purchaseItem->quantity, 4)
                    : 0.00;
                $lineTax = round($taxPerUnit * $qty, 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                $processedItems[] = [
                    'purchase_item_id' => $purchaseItem->id,
                    'medicine_id' => $purchaseItem->medicine_id,
                    'batch_id' => $purchaseItem->batch_id,
                    'quantity' => $qty,
                    'purchase_price' => $purchasePrice,
                    'mrp' => $mrp,
                    'discount' => $discount,
                    'gst_rate' => $gstRate,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                    'reason' => ! empty($itemInput['reason']) ? trim($itemInput['reason']) : $reason,
                ];
            }

            if (empty($processedItems)) {
                throw ValidationException::withMessages([
                    'items' => 'At least one item must have a return quantity greater than zero.',
                ]);
            }

            $grandTotal = round($subtotal + $totalTax, 2);

            // Calculate settlement: on-account adjustment vs cash/bank refund
            $purchaseDue = $lockedPurchase->outstandingAmount();
            $settlementMode = $data['settlement_mode'] ?? $data['settlement_type'] ?? 'credit';

            if (isset($data['adjustment_amount']) && $data['adjustment_amount'] !== null && $data['adjustment_amount'] !== '') {
                $adjustmentAmount = min((float) $data['adjustment_amount'], $grandTotal);
            } elseif ($settlementMode === 'refund') {
                $adjustmentAmount = 0.00;
            } else {
                // By default, if purchase has dues, adjust against the unpaid purchase due up to grandTotal
                $adjustmentAmount = $purchaseDue > 0 ? min($purchaseDue, $grandTotal) : 0.00;
            }
            $adjustmentAmount = round($adjustmentAmount, 2);

            $maxRefundPossible = round(max(0.00, $grandTotal - $adjustmentAmount), 2);

            if (isset($data['refund_amount']) && $data['refund_amount'] !== null && $data['refund_amount'] !== '') {
                $refundAmount = min((float) $data['refund_amount'], $maxRefundPossible);
            } elseif ($settlementMode === 'refund') {
                $refundAmount = $maxRefundPossible;
            } else {
                $refundAmount = 0.00;
            }
            $refundAmount = round($refundAmount, 2);

            $refundMethod = $data['refund_method'] ?? ($refundAmount > 0 ? PaymentMethod::BANK_TRANSFER->value : null);
            $refundStatus = $refundAmount > 0 ? 'refunded' : ($adjustmentAmount > 0 ? 'adjusted' : 'completed');

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
                'refund_amount' => $refundAmount,
                'adjustment_amount' => $adjustmentAmount,
                'refund_method' => $refundMethod,
                'refund_status' => $refundStatus,
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

            // Deduct stock from batch & log movements
            $this->inventoryService->deductStockForPurchaseReturn($store, $purchaseReturn, $userId);

            // Update purchase payment status now that return is created
            $lockedPurchase->updatePaymentStatus();

            // Record incoming supplier refund transaction if refund amount was returned to store
            if ($refundAmount > 0) {
                StorePayment::create([
                    'store_id' => $store->id,
                    'payment_number' => StorePayment::generatePaymentNumber($store->id),
                    'type' => StorePaymentType::PURCHASE_REFUND,
                    'payment_date' => $returnDate,
                    'amount' => $refundAmount,
                    'payment_method' => $refundMethod ?? PaymentMethod::BANK_TRANSFER->value,
                    'purchase_id' => $lockedPurchase->id,
                    'supplier_id' => $lockedPurchase->supplier_id,
                    'reference_number' => $returnNumber,
                    'notes' => "Refund received from supplier for Purchase Return #{$returnNumber} (Purchase #{$lockedPurchase->invoice_number})",
                    'status' => 'completed',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::PURCHASE_RETURNS,
                "Created and completed Purchase Return #{$purchaseReturn->return_number} for Purchase #{$lockedPurchase->invoice_number} (total ₹{$grandTotal}, adjusted ₹{$adjustmentAmount}, refund ₹{$refundAmount}).",
                $purchaseReturn,
                null,
                $purchaseReturn->toArray()
            );

            DB::afterCommit(fn () => event(new PurchaseReturnCompleted($purchaseReturn)));

            return $purchaseReturn;
        });
    }
}
