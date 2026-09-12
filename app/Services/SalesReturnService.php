<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\ReturnStatus;
use App\Events\SalesReturnCompleted;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Store;
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
                'refund_amount' => $refundAmount > 0 ? $refundAmount : $grandTotal,
                'refund_status' => $refundAmount > 0 ? 'refunded' : 'pending',
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

            // Restore inventory
            $this->inventoryService->restoreStockForSalesReturn($store, $salesReturn, $userId);

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::SALES_RETURNS,
                "Created and completed Sales Return #{$salesReturn->return_number} for Sale #{$lockedSale->invoice_number} (total ₹{$grandTotal}).",
                $salesReturn,
                null,
                $salesReturn->toArray()
            );

            DB::afterCommit(fn () => event(new SalesReturnCompleted($salesReturn)));

            return $salesReturn;
        });
    }
}
