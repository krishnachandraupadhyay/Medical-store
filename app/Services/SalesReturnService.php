<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\ReturnStatus;
use App\Enums\StorePaymentType;
use App\Events\SalesReturnCompleted;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Store;
use App\Models\StorePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create and complete a sales return.
     *
     * @throws ValidationException
     */
    public function createSalesReturn(Store $store, Sale $sale, array $data, ?int $userId = null): SalesReturn
    {
        return DB::transaction(function () use ($store, $sale, $data, $userId) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $sale->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedSale->isCompleted()) {
                throw ValidationException::withMessages([
                    'sale' => 'Returns can only be created for completed sales.',
                ]);
            }

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Sales return must include at least one item.',
                ]);
            }

            $returnNumber = SalesReturn::generateReturnNumber($store->id);
            $returnDate = $data['return_date'] ?? now()->toDateString();
            $refundAmount = (float) ($data['refund_amount'] ?? 0.00);
            $reason = trim($data['reason'] ?? 'Customer Return');

            $subtotal = 0.00;
            $totalTax = 0.00;
            $processedItems = [];

            foreach ($itemsData as $itemInput) {
                $saleItemId = (int) $itemInput['sale_item_id'];
                $qty = (int) ($itemInput['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                /** @var SaleItem $saleItem */
                $saleItem = SaleItem::where('id', $saleItemId)
                    ->where('sale_id', $lockedSale->id)
                    ->firstOrFail();

                $returnableQty = $saleItem->returnableQuantity();
                if ($qty > $returnableQty) {
                    $medName = $saleItem->medicine?->name ?? 'Medicine';
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$qty} units of {$medName}. Maximum returnable quantity is {$returnableQty}.",
                    ]);
                }

                $unitPrice = (float) $saleItem->unit_price;
                $gstRate = (float) $saleItem->gst_rate;
                $itemSubtotal = $unitPrice * $qty;
                $itemTax = round($itemSubtotal * ($gstRate / 100), 2);
                $itemTotal = round($itemSubtotal + $itemTax, 2);

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $processedItems[] = [
                    'sale_item_id' => $saleItem->id,
                    'medicine_id' => $saleItem->medicine_id,
                    'batch_id' => $saleItem->batch_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'mrp' => $saleItem->mrp,
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

            $saleDue = $lockedSale->outstandingAmount();

            if (isset($data['adjustment_amount'])) {
                $adjustmentAmount = min((float) $data['adjustment_amount'], $saleDue, $grandTotal);
            } else {
                $adjustmentAmount = $saleDue > 0 ? min($saleDue, $grandTotal) : 0.00;
            }
            $adjustmentAmount = round($adjustmentAmount, 2);

            $maxRefundPossible = round($grandTotal - $adjustmentAmount, 2);

            if (isset($data['refund_amount']) && $data['refund_amount'] !== null && $data['refund_amount'] !== '') {
                $refundAmount = min((float) $data['refund_amount'], $maxRefundPossible);
            } else {
                $refundAmount = $maxRefundPossible;
            }
            $refundAmount = round($refundAmount, 2);

            $refundMethod = $data['refund_method'] ?? 'cash';
            $refundStatus = $refundAmount > 0 ? 'completed' : ($adjustmentAmount > 0 ? 'adjusted' : 'completed');

            $salesReturn = SalesReturn::create([
                'store_id' => $store->id,
                'sale_id' => $lockedSale->id,
                'customer_id' => $lockedSale->customer_id,
                'return_number' => $returnNumber,
                'return_date' => $returnDate,
                'status' => ReturnStatus::COMPLETED,
                'subtotal' => $subtotal,
                'discount' => 0.00,
                'tax' => $totalTax,
                'grand_total' => $grandTotal,
                'refund_amount' => $refundAmount,
                'adjustment_amount' => $adjustmentAmount,
                'refund_method' => $refundAmount > 0 ? $refundMethod : null,
                'refund_status' => $refundStatus,
                'reason' => $reason,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'completed_at' => now(),
            ]);

            foreach ($processedItems as $itemRow) {
                $itemRow['sales_return_id'] = $salesReturn->id;
                SalesReturnItem::create($itemRow);
            }

            // Create refund payment ledger record if refund amount paid out
            if ($refundAmount > 0) {
                StorePayment::create([
                    'store_id' => $store->id,
                    'payment_number' => StorePayment::generatePaymentNumber($store->id),
                    'type' => StorePaymentType::SALE_REFUND,
                    'payment_date' => $returnDate,
                    'amount' => $refundAmount,
                    'payment_method' => $refundMethod,
                    'sale_id' => $lockedSale->id,
                    'customer_id' => $lockedSale->customer_id,
                    'reference_number' => $returnNumber,
                    'notes' => "Refund for Sales Return #{$returnNumber} (Invoice #{$lockedSale->invoice_number})",
                    'status' => 'completed',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // Update original sale payment status factoring in due adjustment
            $lockedSale->updatePaymentStatus();

            // Restore inventory
            $this->inventoryService->restoreStockForSalesReturn($store, $salesReturn, $userId);

            // Recalculate customer loyalty tier if customer is linked
            if ($lockedSale->customer) {
                $lockedSale->customer->recalculateLoyaltyTier();
            }

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::SALES_RETURNS,
                "Created and completed Sales Return #{$salesReturn->return_number} for Sale #{$lockedSale->invoice_number} (total ₹{$grandTotal}, refund ₹{$refundAmount}, due adjusted ₹{$adjustmentAmount}).",
                $salesReturn,
                null,
                $salesReturn->toArray()
            );

            DB::afterCommit(fn () => event(new SalesReturnCompleted($salesReturn)));

            return $salesReturn;
        });
    }
}
