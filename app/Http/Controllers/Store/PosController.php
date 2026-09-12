<?php

namespace App\Http\Controllers\Store;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Sale\CheckoutSaleRequest;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {}

    /**
     * Display POS checkout interface.
     */
    public function index(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customers = Customer::forStore($store->id)->active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::cases();

        return view('store.pos.index', compact('store', 'customers', 'paymentMethods'));
    }

    /**
     * Search medicines with active in-stock batches for POS terminal.
     */
    public function searchMedicines(Request $request): JsonResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $term = trim((string) $request->input('q', ''));
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $medicines = Medicine::forStore($store->id)
            ->active()
            ->search($term)
            ->with(['batches' => function ($bq) {
                $bq->where('status', 'active')
                    ->where('quantity', '>', 0)
                    ->where('expiry_date', '>=', now()->toDateString())
                    ->orderBy('expiry_date');
            }, 'unit', 'dosageForm'])
            ->limit(20)
            ->get()
            ->map(function (Medicine $med) {
                $batches = $med->batches->map(function (Batch $b) {
                    return [
                        'id' => $b->id,
                        'batch_number' => $b->batch_number,
                        'expiry_date' => $b->expiry_date->format('Y-m-d'),
                        'expiry_label' => $b->expiry_date->format('d M Y'),
                        'is_expired' => $b->isExpired(),
                        'is_expiring_soon' => $b->isExpiringSoon(90),
                        'quantity' => $b->quantity,
                        'selling_price' => (float) $b->selling_price,
                        'mrp' => (float) $b->mrp,
                    ];
                });

                return [
                    'id' => $med->id,
                    'name' => $med->displayName(),
                    'generic_name' => $med->generic_name,
                    'brand_name' => $med->brand_name,
                    'prescription_required' => (bool) $med->prescription_required,
                    'unit' => $med->unit?->name,
                    'dosage_form' => $med->dosageForm?->name,
                    'hsn_code' => $med->hsn_code,
                    'gst_rate' => (float) ($med->gst_rate ?? 0),
                    'total_stock' => $batches->sum('quantity'),
                    'batches' => $batches,
                ];
            });

        return response()->json($medicines);
    }

    /**
     * Quick create customer from POS interface.
     */
    public function quickCustomer(Request $request): JsonResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $validated['store_id'] = $store->id;
        $validated['customer_code'] = Customer::generateCustomerCode($store->id);
        $validated['status'] = 'active';
        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'code' => $customer->customer_code,
            ],
        ]);
    }

    /**
     * Process POS sale checkout.
     */
    public function checkout(CheckoutSaleRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::canCreateResource('max_invoices')) {
            return back()->withInput()->with('error', 'Invoice limit reached for your subscription plan. Please upgrade to continue billing.');
        }

        $data = $request->validated();
        $data['status'] = $data['status'] ?? SaleStatus::COMPLETED->value;

        try {
            $sale = $this->salesService->createSale($store, $data, auth()->id());

            return redirect()->route('store.sales.show', $sale->id)
                ->with('success', "Sale Invoice #{$sale->invoice_number} created successfully.");
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Fast barcode lookup for POS terminal.
     * Looks up by batch barcode or secondary_barcode.
     */
    public function barcodeSearch(Request $request): JsonResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $code = trim((string) $request->input('code', $request->input('q', '')));
        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Barcode query is required.'], 422);
        }

        $batch = Batch::where('store_id', $store->id)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                    ->orWhere('secondary_barcode', $code);
            })
            ->with(['medicine.unit', 'medicine.dosageForm'])
            ->first();

        if (! $batch) {
            return response()->json([
                'success' => false,
                'message' => "No batch found with barcode '{$code}'.",
            ], 404);
        }

        if ($batch->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => "Batch {$batch->batch_number} has expired on {$batch->expiry_date->format('d M Y')} and cannot be billed.",
            ], 422);
        }

        if ($batch->quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Batch {$batch->batch_number} is out of stock (Quantity: 0).",
            ], 422);
        }

        $med = $batch->medicine;

        return response()->json([
            'success' => true,
            'medicine' => [
                'id' => $med->id,
                'name' => $med->displayName(),
                'generic_name' => $med->generic_name,
                'brand_name' => $med->brand_name,
                'prescription_required' => (bool) $med->prescription_required,
                'unit' => $med->unit?->name,
                'dosage_form' => $med->dosageForm?->name,
                'hsn_code' => $med->hsn_code,
                'gst_rate' => (float) ($med->gst_rate ?? 0),
            ],
            'batch' => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'barcode' => $batch->barcode,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'expiry_label' => $batch->expiry_date->format('d M Y'),
                'is_expiring_soon' => $batch->isExpiringSoon(90),
                'quantity' => $batch->quantity,
                'selling_price' => (float) $batch->selling_price,
                'mrp' => (float) $batch->mrp,
            ],
        ]);
    }

    /**
     * Hold active POS bill (save as named draft).
     */
    public function holdBill(CheckoutSaleRequest $request)
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $data = $request->validated();

        try {
            $sale = $this->salesService->holdBill($store, $data, auth()->id());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Bill #{$sale->hold_reference} held successfully.",
                    'sale' => [
                        'id' => $sale->id,
                        'invoice_number' => $sale->invoice_number,
                        'hold_reference' => $sale->hold_reference,
                        'customer_name' => $sale->customer_name,
                        'grand_total' => $sale->grand_total,
                        'items_count' => $sale->items()->count(),
                        'created_at' => $sale->created_at->format('H:i:s'),
                    ],
                ]);
            }

            return redirect()->route('store.pos.index')
                ->with('success', "Bill #{$sale->hold_reference} held successfully.");
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }

            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * List all currently held bills for the store.
     */
    public function heldBills(): JsonResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $heldBills = Sale::forStore($store->id)
            ->held()
            ->with(['items.medicine.unit', 'items.batch', 'customer'])
            ->latest()
            ->get()
            ->map(function (Sale $sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'hold_reference' => $sale->hold_reference,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone,
                    'subtotal' => (float) $sale->subtotal,
                    'discount' => (float) $sale->discount,
                    'tax' => (float) $sale->tax,
                    'grand_total' => (float) $sale->grand_total,
                    'notes' => $sale->notes,
                    'items_count' => $sale->items->count(),
                    'held_at' => $sale->created_at->format('d M, H:i'),
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'medicine_id' => $item->medicine_id,
                            'medicine_name' => $item->medicine?->displayName() ?? 'Unknown',
                            'batch_id' => $item->batch_id,
                            'batch_number' => $item->batch?->batch_number ?? '',
                            'current_stock' => $item->batch?->quantity ?? 0,
                            'quantity' => $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'mrp' => (float) $item->mrp,
                            'discount' => (float) $item->discount,
                            'gst_rate' => (float) $item->gst_rate,
                            'tax_amount' => (float) $item->tax_amount,
                            'line_total' => (float) $item->line_total,
                        ];
                    }),
                ];
            });

        return response()->json($heldBills);
    }

    /**
     * Resume a held bill.
     */
    public function resumeBill(Sale $sale): JsonResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_unless($sale->store_id === $store->id, 404);

        if (! $sale->isDraft()) {
            return response()->json(['success' => false, 'message' => 'Only held draft bills can be resumed.'], 422);
        }

        $sale->load(['items.medicine.unit', 'items.batch', 'customer']);

        // Check stock availability
        $warnings = [];
        foreach ($sale->items as $item) {
            $available = $item->batch?->quantity ?? 0;
            if ($available < $item->quantity) {
                $warnings[] = "Batch {$item->batch?->batch_number} of {$item->medicine?->name} only has {$available} in stock (requested {$item->quantity}).";
            }
        }

        return response()->json([
            'success' => true,
            'sale' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'hold_reference' => $sale->hold_reference,
                'customer_id' => $sale->customer_id,
                'customer_name' => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'subtotal' => (float) $sale->subtotal,
                'discount' => (float) $sale->discount,
                'tax' => (float) $sale->tax,
                'grand_total' => (float) $sale->grand_total,
                'notes' => $sale->notes,
                'items' => $sale->items->map(function ($item) {
                    return [
                        'medicine_id' => $item->medicine_id,
                        'medicine_name' => $item->medicine?->displayName() ?? 'Unknown',
                        'batch_id' => $item->batch_id,
                        'batch_number' => $item->batch?->batch_number ?? '',
                        'expiry_date' => $item->batch?->expiry_date?->format('Y-m-d') ?? '',
                        'expiry_label' => $item->batch?->expiry_date?->format('d M Y') ?? '',
                        'current_stock' => $item->batch?->quantity ?? 0,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'mrp' => (float) $item->mrp,
                        'discount' => (float) $item->discount,
                        'gst_rate' => (float) $item->gst_rate,
                        'tax_amount' => (float) $item->tax_amount,
                        'line_total' => (float) $item->line_total,
                    ];
                }),
            ],
            'warnings' => $warnings,
        ]);
    }

    /**
     * Discard a held bill.
     */
    public function discardHeld(Sale $sale): JsonResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        abort_unless($sale->store_id === $store->id, 404);

        try {
            $this->salesService->cancelDraftSale($store, $sale, 'Discarded from POS terminal', auth()->id());

            return response()->json([
                'success' => true,
                'message' => "Held bill #{$sale->hold_reference} discarded successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
