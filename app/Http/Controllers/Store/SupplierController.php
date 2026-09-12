<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Supplier\StoreSupplierRequest;
use App\Http\Requests\Store\Supplier\UpdateSupplierRequest;
use App\Models\ContactNote;
use App\Models\Supplier;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers for the active store.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Supplier::forStore($store->id)->withCount('purchases');

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('store.suppliers.index', compact('store', 'suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::checkLimit($store, 'max_suppliers', 1)) {
            return redirect()->route('store.suppliers.index')
                ->with('error', 'You have reached the maximum supplier limit allowed by your plan. Please upgrade your plan.');
        }

        return view('store.suppliers.create', compact('store'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::checkLimit($store, 'max_suppliers', 1)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You have reached the maximum supplier limit allowed by your plan. Please upgrade your plan.');
        }

        $data = $request->validated();
        $data['store_id'] = $store->id;
        $data['created_by'] = auth()->id();

        $supplier = Supplier::create($data);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::SUPPLIERS,
            "Created supplier [{$supplier->name}] for store [{$store->name}].",
            $supplier,
            null,
            $supplier->toArray()
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', "Supplier [{$supplier->name}] created successfully.");
    }

    /**
     * Display the specified supplier — 360° profile.
     */
    public function show(Supplier $supplier): View
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $totalPurchased = $supplier->totalPurchased();
        $totalPaid = $supplier->totalPaid();
        $outstanding = $supplier->outstandingAmount();
        $purchasesCount = $supplier->purchasesCount();
        $lastPurchase = $supplier->lastPurchaseDate();

        // Recent purchases (last 20)
        $recentPurchases = $supplier->purchases()
            ->with('items')
            ->latest('purchase_date')
            ->limit(20)
            ->get();

        // Notes & follow-ups
        $notes = $supplier->contactNotes()
            ->with('creator')
            ->latest()
            ->get();

        $pendingFollowUps = $notes->filter(fn ($n) => $n->hasFollowUp() && ! $n->follow_up_done);
        $overdueFollowUps = $notes->filter(fn ($n) => $n->isFollowUpOverdue());

        return view('store.suppliers.show', compact(
            'store', 'supplier',
            'totalPurchased', 'totalPaid', 'outstanding', 'purchasesCount', 'lastPurchase',
            'recentPurchases',
            'notes', 'pendingFollowUps', 'overdueFollowUps'
        ));
    }

    /**
     * Show the form for editing the supplier.
     */
    public function edit(Supplier $supplier): View
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        return view('store.suppliers.edit', compact('store', 'supplier'));
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $oldValues = $supplier->toArray();
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $supplier->update($data);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::SUPPLIERS,
            "Updated supplier [{$supplier->name}].",
            $supplier,
            $oldValues,
            $supplier->fresh()->toArray()
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', "Supplier [{$supplier->name}] updated successfully.");
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        if ($supplier->purchases()->exists()) {
            return redirect()->route('store.suppliers.show', $supplier)
                ->with('error', 'Cannot delete this supplier because there are existing purchase records linked to it. You can set the supplier status to inactive instead.');
        }

        $supplierName = $supplier->name;
        $supplierData = $supplier->toArray();
        $supplier->delete();

        AuditLogger::log(
            AuditAction::DELETED,
            AuditModule::SUPPLIERS,
            "Deleted supplier [{$supplierName}].",
            $supplier,
            $supplierData,
            null
        );

        return redirect()->route('store.suppliers.index')
            ->with('status', "Supplier [{$supplierName}] deleted successfully.");
    }

    // ─── Note Actions ─────────────────────────────────────────────────────────

    public function storeNote(Request $request, Supplier $supplier): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $data = $request->validate([
            'type' => ['required', 'in:note,call,meeting,email,followup'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $note = $supplier->contactNotes()->create([
            'store_id' => $store->id,
            'type' => $data['type'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update last_contacted_at
        $supplier->updateQuietly(['last_contacted_at' => now()]);

        AuditLogger::log(
            AuditAction::NOTE_ADDED,
            AuditModule::CONTACT_NOTES,
            "Added {$note->typeLabel()} note on supplier [{$supplier->name}].",
            $supplier
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', 'Note added successfully.')
            ->withFragment('notes');
    }

    public function markNoteFollowUpDone(Supplier $supplier, int $noteId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $note = ContactNote::forStore($store->id)
            ->where('notable_type', Supplier::class)
            ->where('notable_id', $supplier->id)
            ->findOrFail($noteId);

        $note->update([
            'follow_up_done' => true,
            'follow_up_done_at' => now(),
        ]);

        AuditLogger::log(
            AuditAction::FOLLOWUP_COMPLETED,
            AuditModule::CONTACT_NOTES,
            "Marked follow-up done on supplier [{$supplier->name}] note.",
            $supplier
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', 'Follow-up marked as done.')
            ->withFragment('notes');
    }

    public function destroyNote(Supplier $supplier, int $noteId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $supplier->store_id === $store->id, 404);

        $note = ContactNote::forStore($store->id)
            ->where('notable_type', Supplier::class)
            ->where('notable_id', $supplier->id)
            ->findOrFail($noteId);

        $note->delete();

        AuditLogger::log(
            AuditAction::NOTE_DELETED,
            AuditModule::CONTACT_NOTES,
            "Deleted note on supplier [{$supplier->name}].",
            $supplier
        );

        return redirect()->route('store.suppliers.show', $supplier)
            ->with('status', 'Note deleted.')
            ->withFragment('notes');
    }
}
