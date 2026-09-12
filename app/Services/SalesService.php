<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Enums\StorePaymentType;
use App\Events\SaleCompleted;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\StorePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create a sale (draft or completed directly via POS / checkout).
     *
     * @throws ValidationException
     */
    public function createSale(Store $store, array $data, ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($store, $data, $userId) {
            $isComplete = ($data['status'] ?? 'completed') === 'completed' || ($data['status'] ?? null) === SaleStatus::COMPLETED;
            $itemsData = $data['items'] ?? [];

            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Sale must contain at least one medicine item.',
                ]);
            }

            $invoiceNumber = Sale::generateInvoiceNumber($store->id);
            $saleDate = $data['sale_date'] ?? now()->toDateString();
            $customerId = ! empty($data['customer_id']) ? (int) $data['customer_id'] : null;
            $customerName = trim($data['customer_name'] ?? 'Walk-in Customer');
            $customerPhone = trim($data['customer_phone'] ?? '');

            // Calculate totals
            $subtotal = 0.00;
            $totalTax = 0.00;
            $totalDiscount = (float) ($data['discount'] ?? 0.00);

            $processedItems = [];

            foreach ($itemsData as $itemInput) {
                $medicineId = (int) $itemInput['medicine_id'];
                $batchId = (int) $itemInput['batch_id'];
                $qty = (int) $itemInput['quantity'];

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Item quantity must be greater than zero.',
                    ]);
                }

                /** @var Batch $batch */
                $batch = Batch::where('id', $batchId)
                    ->where('medicine_id', $medicineId)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($isComplete) {
                    if (! $batch->canBeSold()) {
                        $med = Medicine::find($medicineId);
                        throw ValidationException::withMessages([
                            'items' => "Batch [{$batch->batch_number}] for {$med->name} cannot be sold because it is {$batch->status} or expired.",
                        ]);
                    }

                    if ($batch->quantity < $qty) {
                        $med = Medicine::find($medicineId);
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock in batch [{$batch->batch_number}] for {$med->name}. Available: {$batch->quantity}, Requested: {$qty}.",
                        ]);
                    }
                }

                $unitPrice = (float) ($itemInput['unit_price'] ?? $batch->selling_price);
                $mrp = (float) ($batch->mrp ?: $unitPrice);
                $itemDiscount = (float) ($itemInput['discount'] ?? 0.00);
                $gstRate = (float) ($itemInput['gst_rate'] ?? 0.00);

                $priceAfterDiscount = max(0.00, ($unitPrice * $qty) - $itemDiscount);
                $taxAmount = round($priceAfterDiscount * ($gstRate / 100), 2);
                $lineTotal = round($priceAfterDiscount + $taxAmount, 2);

                $subtotal += ($unitPrice * $qty);
                $totalTax += $taxAmount;

                $processedItems[] = [
                    'medicine_id' => $medicineId,
                    'batch_id' => $batchId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'mrp' => $mrp,
                    'discount' => $itemDiscount,
                    'gst_rate' => $gstRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ];
            }

            $grandTotal = max(0.00, round(($subtotal - $totalDiscount) + $totalTax, 2));

            // Payment calculations
            $paidAmount = (float) ($data['paid_amount'] ?? 0.00);
            if ($paidAmount > $grandTotal) {
                // If overpaid in cash, change returned, so recorded paid amount is grand total
                $paidAmount = $grandTotal;
            }

            $paymentStatus = PaymentStatus::UNPAID;
            if ($paidAmount >= $grandTotal && $grandTotal > 0) {
                $paymentStatus = PaymentStatus::PAID;
            } elseif ($paidAmount > 0) {
                $paymentStatus = PaymentStatus::PARTIAL;
            }

            $paymentMethod = $data['payment_method'] ?? PaymentMethod::CASH->value;

            $sale = Sale::create([
                'store_id' => $store->id,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $saleDate,
                'status' => $isComplete ? SaleStatus::COMPLETED : SaleStatus::DRAFT,
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'grand_total' => $grandTotal,
                'paid_amount' => $isComplete ? $paidAmount : 0.00,
                'payment_status' => $isComplete ? $paymentStatus : PaymentStatus::UNPAID,
                'payment_method' => $paymentMethod,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'completed_at' => $isComplete ? now() : null,
            ]);

            foreach ($processedItems as $itemRow) {
                $itemRow['sale_id'] = $sale->id;
                SaleItem::create($itemRow);
            }

            if ($isComplete) {
                // Deduct inventory atomically
                $this->inventoryService->deductStockForSale($store, $sale, $userId);

                // If payment recorded, create store_payment record(s)
                // Supports split payment: $data['payments'] = [{method, amount, reference}, ...]
                $paymentsInput = $data['payments'] ?? null;
                if (! empty($paymentsInput) && is_array($paymentsInput)) {
                    foreach ($paymentsInput as $payRow) {
                        $payAmt = (float) ($payRow['amount'] ?? 0);
                        if ($payAmt <= 0) {
                            continue;
                        }
                        StorePayment::create([
                            'store_id' => $store->id,
                            'payment_number' => StorePayment::generatePaymentNumber($store->id),
                            'type' => StorePaymentType::SALE_PAYMENT,
                            'payment_date' => $saleDate,
                            'amount' => $payAmt,
                            'payment_method' => $payRow['method'] ?? PaymentMethod::CASH->value,
                            'sale_id' => $sale->id,
                            'customer_id' => $customerId,
                            'reference_number' => $payRow['reference'] ?? null,
                            'notes' => "Split payment for Invoice #{$sale->invoice_number}",
                            'created_by' => $userId,
                        ]);
                    }
                } elseif ($paidAmount > 0) {
                    StorePayment::create([
                        'store_id' => $store->id,
                        'payment_number' => StorePayment::generatePaymentNumber($store->id),
                        'type' => StorePaymentType::SALE_PAYMENT,
                        'payment_date' => $saleDate,
                        'amount' => $paidAmount,
                        'payment_method' => $paymentMethod,
                        'sale_id' => $sale->id,
                        'customer_id' => $customerId,
                        'reference_number' => $data['payment_reference'] ?? null,
                        'notes' => "Payment received at sale checkout for Invoice #{$sale->invoice_number}",
                        'created_by' => $userId,
                    ]);
                }

                AuditLogger::log(
                    AuditAction::CREATED,
                    AuditModule::SALES,
                    "Completed Sale Invoice #{$sale->invoice_number} for customer [{$customerName}] total ₹{$grandTotal}.",
                    $sale,
                    null,
                    $sale->toArray()
                );

                DB::afterCommit(fn () => event(new SaleCompleted($sale)));
            } else {
                AuditLogger::log(
                    AuditAction::CREATED,
                    AuditModule::SALES,
                    "Created Draft Sale #{$sale->invoice_number} for customer [{$customerName}].",
                    $sale,
                    null,
                    $sale->toArray()
                );
            }

            return $sale;
        });
    }

    /**
     * Complete a draft sale.
     *
     * @throws ValidationException
     */
    public function completeDraftSale(Store $store, Sale $sale, array $paymentData = [], ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($store, $sale, $paymentData, $userId) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $sale->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedSale->isDraft()) {
                throw ValidationException::withMessages([
                    'sale' => 'Only draft sales can be completed.',
                ]);
            }

            // Deduct stock
            $this->inventoryService->deductStockForSale($store, $lockedSale, $userId);

            $grandTotal = (float) $lockedSale->grand_total;
            $paidAmount = isset($paymentData['paid_amount']) ? (float) $paymentData['paid_amount'] : (float) $lockedSale->paid_amount;
            if ($paidAmount > $grandTotal) {
                $paidAmount = $grandTotal;
            }

            $paymentMethod = $paymentData['payment_method'] ?? ($lockedSale->payment_method ?: PaymentMethod::CASH->value);

            $paymentStatus = PaymentStatus::UNPAID;
            if ($paidAmount >= $grandTotal && $grandTotal > 0) {
                $paymentStatus = PaymentStatus::PAID;
            } elseif ($paidAmount > 0) {
                $paymentStatus = PaymentStatus::PARTIAL;
            }

            $lockedSale->status = SaleStatus::COMPLETED;
            $lockedSale->paid_amount = $paidAmount;
            $lockedSale->payment_status = $paymentStatus;
            $lockedSale->payment_method = $paymentMethod;
            $lockedSale->completed_at = now();
            $lockedSale->updated_by = $userId;
            $lockedSale->save();

            if ($paidAmount > 0) {
                StorePayment::create([
                    'store_id' => $store->id,
                    'payment_number' => StorePayment::generatePaymentNumber($store->id),
                    'type' => StorePaymentType::SALE_PAYMENT,
                    'payment_date' => now()->toDateString(),
                    'amount' => $paidAmount,
                    'payment_method' => $paymentMethod,
                    'sale_id' => $lockedSale->id,
                    'customer_id' => $lockedSale->customer_id,
                    'reference_number' => $paymentData['payment_reference'] ?? null,
                    'notes' => "Payment recorded on completing draft sale Invoice #{$lockedSale->invoice_number}",
                    'created_by' => $userId,
                ]);
            }

            AuditLogger::log(
                AuditAction::COMPLETED,
                AuditModule::SALES,
                "Completed draft sale Invoice #{$lockedSale->invoice_number}.",
                $lockedSale,
                ['status' => SaleStatus::DRAFT->value],
                ['status' => SaleStatus::COMPLETED->value]
            );

            DB::afterCommit(fn () => event(new SaleCompleted($lockedSale)));

            return $lockedSale;
        });
    }

    /**
     * Cancel a draft sale.
     *
     * @throws ValidationException
     */
    public function cancelDraftSale(Store $store, Sale $sale, ?string $reason = null, ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($store, $sale, $reason, $userId) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $sale->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedSale->isDraft()) {
                throw ValidationException::withMessages([
                    'sale' => 'Only draft sales can be cancelled. Completed sales must be handled via Sales Return.',
                ]);
            }

            $lockedSale->status = SaleStatus::CANCELLED;
            $lockedSale->notes = trim(($lockedSale->notes ? $lockedSale->notes."\n" : '').'Cancellation reason: '.($reason ?: 'Cancelled by user'));
            $lockedSale->updated_by = $userId;
            $lockedSale->save();

            AuditLogger::log(
                AuditAction::CANCELLED,
                AuditModule::SALES,
                "Cancelled draft sale #{$lockedSale->invoice_number}.",
                $lockedSale,
                ['status' => SaleStatus::DRAFT->value],
                ['status' => SaleStatus::CANCELLED->value]
            );

            return $lockedSale;
        });
    }

    /**
     * Hold a bill in POS — saves as a named draft with a hold reference.
     *
     * @throws ValidationException
     */
    public function holdBill(Store $store, array $data, ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($store, $data, $userId) {
            $itemsData = $data['items'] ?? [];

            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Cannot hold an empty bill. Add at least one medicine.',
                ]);
            }

            // Generate hold reference if not provided
            $holdRef = trim($data['hold_reference'] ?? '');
            if (! $holdRef) {
                $count = Sale::forStore($store->id)->held()->count();
                $holdRef = 'HOLD-'.str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
            }

            $invoiceNumber = Sale::generateInvoiceNumber($store->id);
            $saleDate = now()->toDateString();
            $customerId = ! empty($data['customer_id']) ? (int) $data['customer_id'] : null;
            $customerName = trim($data['customer_name'] ?? 'Walk-in Customer');
            $customerPhone = trim($data['customer_phone'] ?? '');

            // Calculate totals without stock deduction
            $subtotal = 0.00;
            $totalTax = 0.00;
            $totalDiscount = (float) ($data['discount'] ?? 0.00);
            $processedItems = [];

            foreach ($itemsData as $itemInput) {
                $medicineId = (int) $itemInput['medicine_id'];
                $batchId = (int) $itemInput['batch_id'];
                $qty = (int) $itemInput['quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $batch = Batch::where('id', $batchId)
                    ->where('medicine_id', $medicineId)
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $unitPrice = (float) ($itemInput['unit_price'] ?? $batch->selling_price);
                $mrp = (float) ($batch->mrp ?: $unitPrice);
                $itemDiscount = (float) ($itemInput['discount'] ?? 0.00);
                $gstRate = (float) ($itemInput['gst_rate'] ?? 0.00);

                $priceAfterDiscount = max(0.00, ($unitPrice * $qty) - $itemDiscount);
                $taxAmount = round($priceAfterDiscount * ($gstRate / 100), 2);
                $lineTotal = round($priceAfterDiscount + $taxAmount, 2);

                $subtotal += ($unitPrice * $qty);
                $totalTax += $taxAmount;

                $processedItems[] = [
                    'medicine_id' => $medicineId,
                    'batch_id' => $batchId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'mrp' => $mrp,
                    'discount' => $itemDiscount,
                    'gst_rate' => $gstRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ];
            }

            $grandTotal = max(0.00, round(($subtotal - $totalDiscount) + $totalTax, 2));

            $sale = Sale::create([
                'store_id' => $store->id,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $saleDate,
                'status' => SaleStatus::DRAFT,
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'grand_total' => $grandTotal,
                'paid_amount' => 0.00,
                'payment_status' => PaymentStatus::UNPAID,
                'hold_reference' => $holdRef,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($processedItems as $itemRow) {
                $itemRow['sale_id'] = $sale->id;
                SaleItem::create($itemRow);
            }

            AuditLogger::log(
                AuditAction::CREATED,
                AuditModule::SALES,
                "Held Bill [{$holdRef}] saved for customer [{$customerName}].",
                $sale,
                null,
                ['hold_reference' => $holdRef]
            );

            return $sale;
        });
    }
}
