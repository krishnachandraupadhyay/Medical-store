<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Facades\SubscriptionAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Customer\StoreCustomerRequest;
use App\Http\Requests\Store\Customer\UpdateCustomerRequest;
use App\Models\ContactNote;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $baseQuery = Customer::forStore($store->id);

        // KPI summary metrics
        $metrics = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->active()->count(),
            'inactive' => (clone $baseQuery)->inactive()->count(),
            'has_due_count' => (clone $baseQuery)->withOutstanding()->count(),
            'total_outstanding' => (float) (Sale::forStore($store->id)
                ->where('status', 'completed')
                ->whereNotNull('customer_id')
                ->selectRaw('SUM(grand_total - paid_amount) as due')
                ->value('due') ?? 0.00),
        ];

        $query = Customer::forStore($store->id);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($tier = $request->input('tier')) {
            $query->where('loyalty_tier', $tier);
        }

        if ($due = $request->input('due')) {
            if ($due === 'has_due') {
                $query->withOutstanding();
            } elseif ($due === 'cleared') {
                $query->cleared();
            }
        }

        if ($gender = $request->input('gender')) {
            $query->gender($gender);
        }

        if ($bloodGroup = $request->input('blood_group')) {
            $query->bloodGroup($bloodGroup);
        }

        if ($city = $request->input('city')) {
            $query->city($city);
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();

        $bloodGroups = Customer::BLOOD_GROUPS;
        $cities = Customer::forStore($store->id)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('store.customers.index', compact('store', 'customers', 'metrics', 'bloodGroups', 'cities'));
    }

    public function create(): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::canCreateResource('max_customers')) {
            return back()->with('error', 'Customer limit reached for your subscription plan. Please upgrade to add more customers.');
        }

        $bloodGroups = Customer::BLOOD_GROUPS;

        return view('store.customers.create', compact('store', 'bloodGroups'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if (! SubscriptionAccess::canCreateResource('max_customers')) {
            return back()->withInput()->with('error', 'Customer limit reached for your subscription plan. Please upgrade to add more customers.');
        }

        $data = $request->validated();
        $data['store_id'] = $store->id;
        $data['customer_code'] = Customer::generateCustomerCode($store->id);
        $data['status'] = 'active';
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $customer = Customer::create($data);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::CUSTOMERS,
            "Created customer [{$customer->name}] with code [{$customer->customer_code}].",
            $customer,
            null,
            $customer->toArray()
        );

        return redirect()->route('store.customers.index')->with('success', "Customer '{$customer->name}' created successfully.");
    }

    public function show(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);

        $salesCount = $customer->salesCount();
        $totalOrders = $salesCount;
        $totalSales = $customer->totalPurchases();
        $totalPaid = $customer->totalPaid();
        $outstanding = $customer->outstandingAmount();
        $lastSaleDate = $customer->lastSaleDate();
        $avgOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0;

        // Recent transactions (last 20)
        $recentSales = $customer->sales()
            ->with('items')
            ->latest('sale_date')
            ->limit(20)
            ->get();

        // Payments
        $recentPayments = $customer->payments()
            ->latest('payment_date')
            ->limit(20)
            ->get();

        // Notes & follow-ups
        $notes = $customer->contactNotes()
            ->with('creator')
            ->latest()
            ->get();

        $pendingFollowUps = $notes->filter(fn ($n) => $n->hasFollowUp() && ! $n->follow_up_done);
        $overdueFollowUps = $notes->filter(fn ($n) => $n->isFollowUpOverdue());

        // Dispensed medicines summary
        $dispensedMedicines = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
            ->where('sales.store_id', $store->id)
            ->where('sales.customer_id', $customer->id)
            ->where('sales.status', 'completed')
            ->whereNull('sales.deleted_at')
            ->select([
                'medicines.id as medicine_id',
                'medicines.name as medicine_name',
                'medicines.generic_name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('MAX(sales.sale_date) as last_dispensed_date'),
                DB::raw('COUNT(DISTINCT sales.id) as orders_count'),
            ])
            ->groupBy('medicines.id', 'medicines.name', 'medicines.generic_name')
            ->orderByDesc('last_dispensed_date')
            ->limit(25)
            ->get();

        return view('store.customers.show', compact(
            'store', 'customer',
            'salesCount', 'totalOrders', 'totalSales', 'totalPaid', 'outstanding', 'lastSaleDate', 'avgOrderValue',
            'recentSales', 'recentPayments',
            'notes', 'pendingFollowUps', 'overdueFollowUps',
            'dispensedMedicines'
        ));
    }

    public function edit(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);
        $bloodGroups = Customer::BLOOD_GROUPS;

        return view('store.customers.edit', compact('store', 'customer', 'bloodGroups'));
    }

    public function update(UpdateCustomerRequest $request, int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);
        $oldData = $customer->toArray();

        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $customer->update($data);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::CUSTOMERS,
            "Updated customer [{$customer->name}].",
            $customer,
            $oldData,
            $customer->toArray()
        );

        return redirect()->route('store.customers.show', $customer)->with('success', "Customer '{$customer->name}' updated successfully.");
    }

    // ─── Note Actions ─────────────────────────────────────────────────────────

    public function storeNote(Request $request, int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);

        $data = $request->validate([
            'type' => ['required', 'in:note,call,meeting,email,followup'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $note = $customer->contactNotes()->create([
            'store_id' => $store->id,
            'type' => $data['type'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update last_contacted_at
        $customer->updateQuietly(['last_contacted_at' => now()]);

        AuditLogger::log(
            AuditAction::NOTE_ADDED,
            AuditModule::CONTACT_NOTES,
            "Added {$note->typeLabel()} note on customer [{$customer->name}].",
            $customer
        );

        return redirect()->route('store.customers.show', $customer)
            ->with('success', 'Note added successfully.')
            ->withFragment('notes');
    }

    public function markNoteFollowUpDone(int $id, int $noteId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);

        $note = ContactNote::forStore($store->id)
            ->where('notable_type', Customer::class)
            ->where('notable_id', $customer->id)
            ->findOrFail($noteId);

        $note->update([
            'follow_up_done' => true,
            'follow_up_done_at' => now(),
        ]);

        AuditLogger::log(
            AuditAction::FOLLOWUP_COMPLETED,
            AuditModule::CONTACT_NOTES,
            "Marked follow-up done on customer [{$customer->name}] note.",
            $customer
        );

        return redirect()->route('store.customers.show', $customer)
            ->with('success', 'Follow-up marked as done.')
            ->withFragment('notes');
    }

    public function destroyNote(int $id, int $noteId): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);

        $note = ContactNote::forStore($store->id)
            ->where('notable_type', Customer::class)
            ->where('notable_id', $customer->id)
            ->findOrFail($noteId);

        $note->delete();

        AuditLogger::log(
            AuditAction::NOTE_DELETED,
            AuditModule::CONTACT_NOTES,
            "Deleted note on customer [{$customer->name}].",
            $customer
        );

        return redirect()->route('store.customers.show', $customer)
            ->with('success', 'Note deleted.')
            ->withFragment('notes');
    }

    /**
     * Toggle customer active / inactive status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);
        $oldStatus = $customer->status;
        $customer->toggleStatus();

        AuditLogger::log(
            $customer->isActive() ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED,
            AuditModule::CUSTOMERS,
            "Changed status of customer [{$customer->name}] from '{$oldStatus}' to '{$customer->status}'.",
            $customer,
            ['status' => $oldStatus],
            ['status' => $customer->status]
        );

        return back()->with('success', "Customer '{$customer->name}' status changed to ".ucfirst($customer->status).'.');
    }

    /**
     * Safe customer deletion with sales history protection.
     */
    public function destroy(int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $customer = Customer::forStore($store->id)->findOrFail($id);

        if ($customer->sales()->exists()) {
            return back()->with('error', "Cannot delete customer '{$customer->name}' because sales invoices exist in records. You may deactivate the customer instead.");
        }

        $name = $customer->name;
        $oldData = $customer->toArray();
        $customer->delete();

        AuditLogger::log(
            AuditAction::DELETED,
            AuditModule::CUSTOMERS,
            "Deleted customer [{$name}].",
            $customer,
            $oldData,
            null
        );

        return redirect()->route('store.customers.index')->with('success', "Customer '{$name}' deleted successfully.");
    }

    /**
     * Stream CSV export of customers list.
     */
    public function export(Request $request): StreamedResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Customer::forStore($store->id);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($tier = $request->input('tier')) {
            $query->where('loyalty_tier', $tier);
        }

        if ($due = $request->input('due')) {
            if ($due === 'has_due') {
                $query->withOutstanding();
            } elseif ($due === 'cleared') {
                $query->cleared();
            }
        }

        if ($gender = $request->input('gender')) {
            $query->gender($gender);
        }

        if ($bloodGroup = $request->input('blood_group')) {
            $query->bloodGroup($bloodGroup);
        }

        if ($city = $request->input('city')) {
            $query->city($city);
        }

        $customers = $query->latest('id')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_export_'.date('Y_m_d_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Customer Code',
                'Name',
                'Phone',
                'Alternate Phone',
                'Email',
                'Gender',
                'Blood Group',
                'City',
                'State',
                'Emergency Contact Name',
                'Emergency Contact Phone',
                'Prescribing Doctor',
                'GSTIN / Tax ID',
                'Loyalty Tier',
                'Status',
                'Total Purchases (INR)',
                'Total Paid (INR)',
                'Outstanding Balance (INR)',
                'Completed Orders',
                'Last Purchase Date',
            ]);

            foreach ($customers as $c) {
                fputcsv($file, [
                    $c->customer_code,
                    $c->name,
                    $c->phone,
                    $c->alternate_phone,
                    $c->email,
                    ucfirst($c->gender ?? ''),
                    $c->blood_group ?? 'N/A',
                    $c->city,
                    $c->state,
                    $c->emergency_contact_name ?? 'N/A',
                    $c->emergency_contact_phone ?? 'N/A',
                    $c->doctor_name,
                    $c->tax_number,
                    ucfirst($c->loyalty_tier ?? 'regular'),
                    ucfirst($c->status),
                    number_format($c->totalPurchases(), 2, '.', ''),
                    number_format($c->totalPaid(), 2, '.', ''),
                    number_format($c->outstandingAmount(), 2, '.', ''),
                    $c->salesCount(),
                    $c->lastSaleDate() ?? 'None',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
