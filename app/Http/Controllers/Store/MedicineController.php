<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreMedicineRequest;
use App\Http\Requests\Store\UpdateMedicineRequest;
use App\Models\Category;
use App\Models\DosageForm;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Unit;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
{
    /**
     * Display a listing of medicines for the authenticated store.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Medicine::forStore($store->id)
            ->with(['category', 'manufacturer', 'dosageForm', 'unit']);

        // 1. Search term
        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        // 2. Filter by Category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // 3. Filter by Manufacturer
        if ($manufacturerId = $request->input('manufacturer_id')) {
            $query->where('manufacturer_id', $manufacturerId);
        }

        // 4. Filter by Dosage Form
        if ($dosageFormId = $request->input('dosage_form_id')) {
            $query->where('dosage_form_id', $dosageFormId);
        }

        // 5. Filter by Status
        $status = $request->input('status');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // 6. Filter by Prescription Required
        $rx = $request->input('prescription_required');
        if ($rx !== null && $rx !== '') {
            $query->where('prescription_required', (bool) $rx);
        }

        // 7. Sorting
        match ($request->input('sort')) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'oldest' => $query->oldest('id'),
            default => $query->latest('id'),
        };

        $medicines = $query->paginate(15)->withQueryString();

        // Metric counts
        $counts = [
            'all' => Medicine::forStore($store->id)->count(),
            'active' => Medicine::forStore($store->id)->where('status', 'active')->count(),
            'inactive' => Medicine::forStore($store->id)->where('status', 'inactive')->count(),
            'prescription' => Medicine::forStore($store->id)->where('prescription_required', true)->count(),
        ];

        // Quota usage for store
        $quota = [
            'limit' => SubscriptionAccess::getLimit($store, 'max_medicines'),
            'usage' => SubscriptionAccess::getUsage($store, 'max_medicines'),
            'remaining' => SubscriptionAccess::remaining($store, 'max_medicines'),
            'is_unlimited' => SubscriptionAccess::isUnlimited($store, 'max_medicines'),
            'can_add' => SubscriptionAccess::checkLimit($store, 'max_medicines', 1),
        ];

        // Accessible dropdown options
        $categories = Category::forStore($store->id)->active()->orderBy('name')->get();
        $manufacturers = Manufacturer::forStore($store->id)->active()->orderBy('name')->get();
        $dosageForms = DosageForm::forStore($store->id)->active()->orderBy('name')->get();

        return view('store.medicines.index', compact(
            'medicines',
            'counts',
            'quota',
            'categories',
            'manufacturers',
            'dosageForms',
            'store'
        ));
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create(): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        // Quota Limit Enforcement (Part 19)
        if (! SubscriptionAccess::checkLimit($store, 'max_medicines', 1)) {
            return redirect()->route('store.medicines.index')
                ->with('error', 'You have reached the medicine limit available in your current plan. Please upgrade your plan to add more medicines.');
        }

        $categories = Category::forStore($store->id)->active()->orderBy('sort_order')->orderBy('name')->get();
        $manufacturers = Manufacturer::forStore($store->id)->active()->orderBy('name')->get();
        $dosageForms = DosageForm::forStore($store->id)->active()->orderBy('sort_order')->orderBy('name')->get();
        $units = Unit::forStore($store->id)->active()->orderBy('sort_order')->orderBy('name')->get();

        return view('store.medicines.create', compact(
            'store',
            'categories',
            'manufacturers',
            'dosageForms',
            'units'
        ));
    }

    /**
     * Store a newly created medicine in storage.
     */
    public function store(StoreMedicineRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        // Quota Limit Enforcement (Part 19)
        if (! SubscriptionAccess::checkLimit($store, 'max_medicines', 1)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You have reached the medicine limit available in your current plan. Please upgrade your plan to add more medicines.');
        }

        $data = $request->validated();
        $data['store_id'] = $store->id;
        $data['prescription_required'] = (bool) ($data['prescription_required'] ?? false);
        $data['created_by'] = auth()->id();

        $medicine = Medicine::create($data);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::STORES,
            "Added new medicine master entry [{$medicine->name}] for store [{$store->name}].",
            $medicine,
            null,
            $medicine->toArray()
        );

        return redirect()->route('store.medicines.show', $medicine)
            ->with('status', "Medicine [{$medicine->name}] added successfully to store catalog.");
    }

    /**
     * Display the specified medicine details.
     */
    public function show(Medicine $medicine): View
    {
        $this->authorizeMedicineAccess($medicine);

        $medicine->load(['category', 'manufacturer', 'dosageForm', 'unit', 'creator', 'updater']);

        return view('store.medicines.show', [
            'medicine' => $medicine,
            'store' => current_store(),
        ]);
    }

    /**
     * Show the form for editing the specified medicine.
     */
    public function edit(Medicine $medicine): View
    {
        $this->authorizeMedicineAccess($medicine);

        $store = current_store();
        $categories = Category::forStore($store->id)->active()->orderBy('sort_order')->orderBy('name')->get();
        $manufacturers = Manufacturer::forStore($store->id)->active()->orderBy('name')->get();
        $dosageForms = DosageForm::forStore($store->id)->active()->orderBy('sort_order')->orderBy('name')->get();
        $units = Unit::forStore($store->id)->active()->orderBy('sort_order')->orderBy('name')->get();

        return view('store.medicines.edit', compact(
            'medicine',
            'store',
            'categories',
            'manufacturers',
            'dosageForms',
            'units'
        ));
    }

    /**
     * Update the specified medicine in storage.
     */
    public function update(UpdateMedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $this->authorizeMedicineAccess($medicine);

        $oldValues = $medicine->toArray();
        $data = $request->validated();
        $data['prescription_required'] = (bool) ($data['prescription_required'] ?? false);
        $data['updated_by'] = auth()->id();

        $medicine->update($data);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORES,
            "Updated medicine master entry [{$medicine->name}].",
            $medicine,
            $oldValues,
            $medicine->toArray()
        );

        return redirect()->route('store.medicines.show', $medicine)
            ->with('status', "Medicine [{$medicine->name}] updated successfully.");
    }

    /**
     * Toggle active/inactive status for a medicine.
     */
    public function toggleStatus(Medicine $medicine): RedirectResponse
    {
        $this->authorizeMedicineAccess($medicine);
        $store = current_store();

        // If activating an inactive medicine, check quota limit
        if ($medicine->status === 'inactive' && ! SubscriptionAccess::checkLimit($store, 'max_medicines', 1)) {
            return redirect()->back()->with(
                'error',
                'You have reached the medicine limit available in your current plan. Please upgrade your plan to activate more medicines.'
            );
        }

        $newStatus = $medicine->status === 'active' ? 'inactive' : 'active';
        $oldStatus = $medicine->status;

        $medicine->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            $newStatus === 'active' ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED,
            AuditModule::STORES,
            "Changed medicine [{$medicine->name}] status from {$oldStatus} to {$newStatus}.",
            $medicine,
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('status', "Medicine [{$medicine->name}] has been {$label}.");
    }

    /**
     * Remove the specified medicine from storage (soft delete).
     */
    public function destroy(Medicine $medicine): RedirectResponse
    {
        $this->authorizeMedicineAccess($medicine);

        $medicineName = $medicine->name;
        $medicine->delete();

        AuditLogger::log(
            AuditAction::DELETED,
            AuditModule::STORES,
            "Soft deleted medicine [{$medicineName}].",
            $medicine
        );

        return redirect()->route('store.medicines.index')
            ->with('status', "Medicine [{$medicineName}] deleted successfully.");
    }

    /**
     * Strict Tenant Authorization check.
     */
    protected function authorizeMedicineAccess(Medicine $medicine): void
    {
        $store = current_store();

        if (auth()->user()?->isSuperAdmin()) {
            return;
        }

        if (! $store || $medicine->store_id !== $store->id) {
            abort(404, 'Medicine not found.');
        }
    }
}
