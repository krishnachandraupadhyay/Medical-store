<?php

namespace App\Services;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessAlertService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected OutstandingService $outstandingService
    ) {}

    /**
     * Check and trigger low stock alerts for a store.
     */
    public function checkLowStockAlerts(Store $store): int
    {
        $lowStockMedicines = DB::table('medicines')
            ->leftJoin('batches', function ($join) {
                $join->on('medicines.id', '=', 'batches.medicine_id')
                    ->where('batches.status', '=', 'active')
                    ->whereNull('batches.deleted_at');
            })
            ->where('medicines.store_id', $store->id)
            ->where('medicines.status', 'active')
            ->whereNull('medicines.deleted_at')
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength', 'medicines.reorder_level')
            ->havingRaw('COALESCE(SUM(batches.quantity), 0) <= medicines.reorder_level AND COALESCE(SUM(batches.quantity), 0) > 0 AND medicines.reorder_level > 0')
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.strength',
                'medicines.reorder_level',
                DB::raw('COALESCE(SUM(batches.quantity), 0) as current_stock')
            )
            ->get();

        $count = 0;
        foreach ($lowStockMedicines as $item) {
            $notification = $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::LOW_STOCK,
                'title' => 'Low Stock Warning',
                'message' => "Medicine {$item->name} is running low ({$item->current_stock} remaining, reorder level: {$item->reorder_level}).",
                'priority' => NotificationPriority::HIGH,
                'reference_type' => Medicine::class,
                'reference_id' => $item->id,
                'alert_key' => "low_stock:med:{$item->id}",
                'action_url' => route('store.inventory.index', ['filter' => 'low_stock']),
                'metadata' => [
                    'medicine_id' => $item->id,
                    'current_stock' => (int) $item->current_stock,
                    'reorder_level' => (int) $item->reorder_level,
                ],
            ]);

            if ($notification && $notification->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check and trigger out-of-stock alerts for a store.
     */
    public function checkOutOfStockAlerts(Store $store): int
    {
        $outOfStockMedicines = DB::table('medicines')
            ->leftJoin('batches', function ($join) {
                $join->on('medicines.id', '=', 'batches.medicine_id')
                    ->where('batches.status', '=', 'active')
                    ->whereNull('batches.deleted_at');
            })
            ->where('medicines.store_id', $store->id)
            ->where('medicines.status', 'active')
            ->whereNull('medicines.deleted_at')
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength')
            ->havingRaw('COALESCE(SUM(batches.quantity), 0) = 0')
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.strength'
            )
            ->get();

        $count = 0;
        foreach ($outOfStockMedicines as $item) {
            $notification = $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::OUT_OF_STOCK,
                'title' => 'Out of Stock Alert',
                'message' => "Medicine {$item->name} is completely out of stock.",
                'priority' => NotificationPriority::CRITICAL,
                'reference_type' => Medicine::class,
                'reference_id' => $item->id,
                'alert_key' => "out_of_stock:med:{$item->id}",
                'action_url' => route('store.inventory.index', ['filter' => 'out_of_stock']),
                'metadata' => [
                    'medicine_id' => $item->id,
                ],
            ]);

            if ($notification && $notification->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check and trigger expiring soon batch alerts (default 30 days).
     */
    public function checkExpiryAlerts(Store $store, int $windowDays = 30): int
    {
        $today = Carbon::today()->toDateString();
        $threshold = Carbon::today()->addDays($windowDays)->toDateString();

        $expiringBatches = Batch::with('medicine')
            ->where('store_id', $store->id)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [$today, $threshold])
            ->where('quantity', '>', 0)
            ->get();

        $count = 0;
        foreach ($expiringBatches as $batch) {
            $medicineName = $batch->medicine?->name ?? 'Unknown Medicine';
            $daysLeft = Carbon::today()->diffInDays(Carbon::parse($batch->expiry_date), false);

            $notification = $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::EXPIRING_BATCH,
                'title' => 'Batch Expiring Soon',
                'message' => "Batch {$batch->batch_number} of {$medicineName} expires in {$daysLeft} days ({$batch->quantity} units remaining).",
                'priority' => NotificationPriority::HIGH,
                'reference_type' => Batch::class,
                'reference_id' => $batch->id,
                'alert_key' => "expiring:batch:{$batch->id}:{$windowDays}d",
                'action_url' => route('store.inventory.index', ['filter' => 'expiring_soon']),
                'metadata' => [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date ? Carbon::parse($batch->expiry_date)->toDateString() : null,
                    'quantity' => $batch->quantity,
                ],
            ]);

            if ($notification && $notification->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check and trigger expired batch alerts.
     */
    public function checkExpiredBatchAlerts(Store $store): int
    {
        $today = Carbon::today()->toDateString();

        $expiredBatches = Batch::with('medicine')
            ->where('store_id', $store->id)
            ->where('status', 'active')
            ->where('expiry_date', '<', $today)
            ->where('quantity', '>', 0)
            ->get();

        $count = 0;
        foreach ($expiredBatches as $batch) {
            $medicineName = $batch->medicine?->name ?? 'Unknown Medicine';

            $notification = $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::EXPIRED_BATCH,
                'title' => 'Critical: Expired Batch in Stock',
                'message' => "Batch {$batch->batch_number} of {$medicineName} has EXPIRED with {$batch->quantity} units still in stock!",
                'priority' => NotificationPriority::CRITICAL,
                'reference_type' => Batch::class,
                'reference_id' => $batch->id,
                'alert_key' => "expired:batch:{$batch->id}",
                'action_url' => route('store.inventory.index', ['filter' => 'expired']),
                'metadata' => [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date ? Carbon::parse($batch->expiry_date)->toDateString() : null,
                    'quantity' => $batch->quantity,
                ],
            ]);

            if ($notification && $notification->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check and trigger customer outstanding receivables alerts with 7-day cooldown.
     */
    public function checkCustomerOutstandingAlerts(Store $store): int
    {
        $topCustomers = $this->outstandingService->getTopCustomerOutstanding($store, 20);
        $weekKey = now()->format('Y-W');
        $count = 0;

        foreach ($topCustomers as $customer) {
            if ($customer->outstanding > 0) {
                $formattedAmount = number_format($customer->outstanding, 2);
                $notification = $this->notificationService->notify([
                    'store_id' => $store->id,
                    'type' => NotificationType::CUSTOMER_OUTSTANDING,
                    'title' => 'Customer Outstanding Receivable',
                    'message' => "Customer {$customer->name} has an outstanding balance of ₹{$formattedAmount}.",
                    'priority' => NotificationPriority::NORMAL,
                    'reference_type' => 'App\Models\Customer',
                    'reference_id' => $customer->id,
                    'alert_key' => "cust_due:{$customer->id}:{$weekKey}",
                    'action_url' => route('store.customers.show', $customer->id),
                    'metadata' => [
                        'customer_id' => $customer->id,
                        'outstanding' => (float) $customer->outstanding,
                    ],
                ]);

                if ($notification && $notification->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Check and trigger supplier outstanding payables alerts with 7-day cooldown.
     */
    public function checkSupplierOutstandingAlerts(Store $store): int
    {
        $topSuppliers = $this->outstandingService->getTopSupplierOutstanding($store, 20);
        $weekKey = now()->format('Y-W');
        $count = 0;

        foreach ($topSuppliers as $supplier) {
            if ($supplier->outstanding > 0) {
                $formattedAmount = number_format($supplier->outstanding, 2);
                $notification = $this->notificationService->notify([
                    'store_id' => $store->id,
                    'type' => NotificationType::SUPPLIER_OUTSTANDING,
                    'title' => 'Supplier Payment Due',
                    'message' => "Supplier {$supplier->name} has an outstanding payable of ₹{$formattedAmount}.",
                    'priority' => NotificationPriority::NORMAL,
                    'reference_type' => 'App\Models\Supplier',
                    'reference_id' => $supplier->id,
                    'alert_key' => "supp_due:{$supplier->id}:{$weekKey}",
                    'action_url' => route('store.suppliers.show', $supplier->id),
                    'metadata' => [
                        'supplier_id' => $supplier->id,
                        'outstanding' => (float) $supplier->outstanding,
                    ],
                ]);

                if ($notification && $notification->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Run all business checks for a given store.
     *
     * @return array<string, int>
     */
    public function checkAllStoreAlerts(Store $store): array
    {
        return [
            'low_stock' => $this->checkLowStockAlerts($store),
            'out_of_stock' => $this->checkOutOfStockAlerts($store),
            'expiring_batch' => $this->checkExpiryAlerts($store),
            'expired_batch' => $this->checkExpiredBatchAlerts($store),
            'customer_outstanding' => $this->checkCustomerOutstandingAlerts($store),
            'supplier_outstanding' => $this->checkSupplierOutstandingAlerts($store),
        ];
    }
}
