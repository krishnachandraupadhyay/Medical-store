<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PurchaseStatus;
use App\Events\PurchaseCompleted;
use App\Models\Purchase;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create a new purchase invoice with items.
     */
    public function createPurchase(Store $store, array $data, array $itemsData, ?int $userId = null): Purchase
    {
        return DB::transaction(function () use ($store, $data, $itemsData, $userId) {
            $status = isset($data['status']) && $data['status'] === 'completed'
                ? PurchaseStatus::COMPLETED
                : PurchaseStatus::DRAFT;

            $totals = $this->calculateTotals($itemsData, (float) ($data['discount'] ?? 0));

            $purchase = Purchase::create([
                'store_id' => $store->id,
                'supplier_id' => $data['supplier_id'],
                'invoice_number' => trim($data['invoice_number']),
                'purchase_date' => $data['purchase_date'],
                'status' => $status,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'grand_total' => $totals['grand_total'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'completed_at' => $status === PurchaseStatus::COMPLETED ? now() : null,
            ]);

            foreach ($totals['items'] as $itemData) {
                $purchase->items()->create($itemData);
            }

            // If created directly in completed status, ingest stock immediately
            if ($status === PurchaseStatus::COMPLETED) {
                $this->inventoryService->addStockFromPurchase($store, $purchase, $userId);
                DB::afterCommit(fn () => event(new PurchaseCompleted($purchase)));
            }

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::PURCHASES,
                "Created purchase invoice #{$purchase->invoice_number} (status: {$purchase->status->value}, total: {$purchase->grand_total}) for store [{$store->name}].",
                $purchase,
                null,
                $purchase->toArray()
            );

            return $purchase;
        });
    }

    /**
     * Update an existing draft purchase invoice and items.
     *
     * @throws ValidationException
     */
    public function updatePurchase(Store $store, Purchase $purchase, array $data, array $itemsData, ?int $userId = null): Purchase
    {
        if (! $purchase->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Only draft purchases can be edited.',
            ]);
        }

        return DB::transaction(function () use ($purchase, $data, $itemsData, $userId) {
            $totals = $this->calculateTotals($itemsData, (float) ($data['discount'] ?? 0));

            $oldValues = $purchase->toArray();

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'invoice_number' => trim($data['invoice_number']),
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'grand_total' => $totals['grand_total'],
                'notes' => $data['notes'] ?? null,
                'updated_by' => $userId,
            ]);

            // Replace line items
            $purchase->items()->delete();
            foreach ($totals['items'] as $itemData) {
                $purchase->items()->create($itemData);
            }

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::PURCHASES,
                "Updated draft purchase invoice #{$purchase->invoice_number}.",
                $purchase,
                $oldValues,
                $purchase->fresh()->toArray()
            );

            return $purchase;
        });
    }

    /**
     * Mark a draft purchase as completed and ingest items into batch inventory.
     *
     * @throws ValidationException
     */
    public function completePurchase(Store $store, Purchase $purchase, ?int $userId = null): Purchase
    {
        if (! $purchase->isDraft()) {
            throw ValidationException::withMessages([
                'status' => "Purchase #{$purchase->invoice_number} is already {$purchase->status->value} and cannot be completed again.",
            ]);
        }

        return DB::transaction(function () use ($store, $purchase, $userId) {
            // Lock purchase record
            $lockedPurchase = Purchase::where('id', $purchase->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Ingest batch stock
            $this->inventoryService->addStockFromPurchase($store, $lockedPurchase, $userId);

            $lockedPurchase->status = PurchaseStatus::COMPLETED;
            $lockedPurchase->completed_at = now();
            $lockedPurchase->updated_by = $userId;
            $lockedPurchase->save();

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::PURCHASES,
                "Completed purchase invoice #{$lockedPurchase->invoice_number} and updated inventory.",
                $lockedPurchase,
                ['status' => 'draft'],
                ['status' => 'completed', 'completed_at' => $lockedPurchase->completed_at]
            );

            DB::afterCommit(fn () => event(new PurchaseCompleted($lockedPurchase)));

            return $lockedPurchase;
        });
    }

    /**
     * Cancel a draft purchase.
     *
     * @throws ValidationException
     */
    public function cancelPurchase(Store $store, Purchase $purchase, ?string $reason = null, ?int $userId = null): Purchase
    {
        if (! $purchase->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Only draft purchases can be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($store, $purchase, $reason, $userId) {
            $lockedPurchase = Purchase::where('id', $purchase->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldNotes = $lockedPurchase->notes;
            $cancellationNote = $reason ? "[Cancelled: {$reason}]" : '[Cancelled]';
            $newNotes = trim(($oldNotes ? $oldNotes."\n" : '').$cancellationNote);

            $lockedPurchase->status = PurchaseStatus::CANCELLED;
            $lockedPurchase->notes = $newNotes;
            $lockedPurchase->updated_by = $userId;
            $lockedPurchase->save();

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::PURCHASES,
                "Cancelled draft purchase invoice #{$lockedPurchase->invoice_number}. Reason: {$reason}",
                $lockedPurchase,
                ['status' => 'draft'],
                ['status' => 'cancelled']
            );

            return $lockedPurchase;
        });
    }

    /**
     * Calculate line totals and overall purchase totals.
     */
    protected function calculateTotals(array $itemsData, float $overallDiscount = 0.00): array
    {
        $processedItems = [];
        $runningSubtotal = 0.00;
        $runningTax = 0.00;

        foreach ($itemsData as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $freeQty = (int) ($item['free_quantity'] ?? 0);
            $purchasePrice = (float) ($item['purchase_price'] ?? 0);
            $mrp = (float) ($item['mrp'] ?? 0);
            $sellingPrice = ! empty($item['selling_price']) ? (float) $item['selling_price'] : $mrp;
            $lineDiscount = (float) ($item['discount'] ?? 0);
            $gstRate = (float) ($item['gst_rate'] ?? 0);

            $baseAmount = max(0, ($qty * $purchasePrice) - $lineDiscount);
            $taxAmount = round($baseAmount * ($gstRate / 100), 2);
            $lineTotal = round($baseAmount + $taxAmount, 2);

            $runningSubtotal += $baseAmount;
            $runningTax += $taxAmount;

            $processedItems[] = [
                'medicine_id' => $item['medicine_id'],
                'batch_number' => trim($item['batch_number']),
                'manufacturing_date' => $item['manufacturing_date'] ?? null,
                'expiry_date' => $item['expiry_date'],
                'quantity' => $qty,
                'free_quantity' => $freeQty,
                'purchase_price' => $purchasePrice,
                'mrp' => $mrp,
                'selling_price' => $sellingPrice,
                'discount' => $lineDiscount,
                'gst_rate' => $gstRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ];
        }

        $grandTotal = round(max(0, ($runningSubtotal + $runningTax) - $overallDiscount), 2);

        return [
            'subtotal' => round($runningSubtotal, 2),
            'discount' => round($overallDiscount, 2),
            'tax' => round($runningTax, 2),
            'grand_total' => $grandTotal,
            'items' => $processedItems,
        ];
    }
}
