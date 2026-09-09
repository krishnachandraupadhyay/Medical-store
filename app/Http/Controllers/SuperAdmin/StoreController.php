<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Store\StoreStoreRequest;
use App\Http\Requests\SuperAdmin\Store\UpdateStoreRequest;
use App\Models\Store;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    /**
     * Display a listing of all medical stores with search and filtering.
     */
    public function index(Request $request): View
    {
        $query = Store::query()->with('owners');

        // 1. Search Query Filter
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('owners', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if ($status = $request->input('status')) {
            if (in_array(strtoupper($status), ['ACTIVE', 'INACTIVE', 'SUSPENDED'], true)) {
                $query->where('status', strtoupper($status));
            }
        }

        // 3. Status Summary Counts for Filter Tabs
        $counts = [
            'all' => Store::count(),
            'active' => Store::where('status', StoreStatus::ACTIVE)->count(),
            'inactive' => Store::where('status', StoreStatus::INACTIVE)->count(),
            'suspended' => Store::where('status', StoreStatus::SUSPENDED)->count(),
        ];

        $stores = $query->latest()->paginate(10)->withQueryString();

        return view('super-admin.stores.index', [
            'stores' => $stores,
            'counts' => $counts,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new store.
     */
    public function create(): View
    {
        $previewCode = Store::generateUniqueCode();

        return view('super-admin.stores.create', [
            'previewCode' => $previewCode,
        ]);
    }

    /**
     * Store a newly created store in storage.
     */
    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // 1. Generate unique Store Code on the server
        $data['code'] = Store::generateUniqueCode();

        // 2. Secure Logo Upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        $store = Store::create($data);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::STORES,
            "Created store: {$store->name} ({$store->code})",
            $store,
            null,
            $store->toArray()
        );

        return redirect()->route('super-admin.stores.show', $store)
            ->with('status', "Store [{$store->name}] ({$store->code}) created successfully!");
    }

    /**
     * Display the specified store details.
     */
    public function show(Store $store): View
    {
        $store->load('owners', 'users');

        return view('super-admin.stores.show', [
            'store' => $store,
        ]);
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(Store $store): View
    {
        return view('super-admin.stores.edit', [
            'store' => $store,
        ]);
    }

    /**
     * Update the specified store in storage.
     */
    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $data = $request->validated();
        $oldValues = $store->toArray();

        // Ensure Store Code is NOT modified
        unset($data['code']);

        // Handle logo replacement if new file is uploaded
        if ($request->hasFile('logo')) {
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        $store->update($data);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORES,
            "Updated store details: {$store->name} ({$store->code})",
            $store,
            $oldValues,
            $store->toArray()
        );

        return redirect()->route('super-admin.stores.show', $store)
            ->with('status', "Store details for [{$store->name}] updated successfully!");
    }

    /**
     * Update the operational status of a store (Activate, Deactivate, Suspend).
     */
    public function updateStatus(Request $request, Store $store): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::enum(StoreStatus::class)],
        ]);

        $oldStatus = $store->status->value;
        $newStatus = StoreStatus::from($request->input('status'));
        $store->update(['status' => $newStatus]);

        $action = match ($newStatus) {
            StoreStatus::ACTIVE => AuditAction::ACTIVATED,
            StoreStatus::SUSPENDED => AuditAction::SUSPENDED,
            default => AuditAction::STATUS_CHANGED,
        };

        AuditLogger::log(
            $action,
            AuditModule::STORES,
            "Updated store [{$store->code}] status from {$oldStatus} to {$newStatus->value}.",
            $store,
            ['status' => $oldStatus],
            ['status' => $newStatus->value]
        );

        $statusLabel = $newStatus->label();

        return redirect()->back()
            ->with('status', "Store [{$store->name}] status has been updated to {$statusLabel}.");
    }
}
