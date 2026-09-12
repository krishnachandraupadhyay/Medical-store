@extends('store.layouts.app')

@section('title', 'Customer: ' . $customer->name)

@section('content')
<div class="space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('store.customers.index') }}"
               class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                    {{-- Loyalty tier badge --}}
                    <span class="loyalty-badge loyalty-{{ $customer->loyalty_tier }}">
                        {{ $customer->loyaltyTierLabel() }}
                    </span>
                    {{-- Blood group badge --}}
                    @if($customer->blood_group)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        🩸 {{ $customer->blood_group }}
                    </span>
                    @endif
                    {{-- Status badge --}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $customer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $customer->customer_code }} · {{ $customer->phone }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <form method="POST" action="{{ route('store.customers.toggle-status', $customer->id) }}" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-secondary text-xs font-bold">
                    {{ $customer->isActive() ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            <a href="{{ route('store.customers.edit', $customer) }}"
               class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>

            @if($salesCount === 0)
            <form method="POST" action="{{ route('store.customers.destroy', $customer->id) }}" onsubmit="return confirm('Are you sure you want to delete this customer?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 transition-colors">
                    Delete
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert-success flex items-center gap-2 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 14l-4.121-4.121a1 1 0 011.414-1.414L8.414 11.172l6.879-6.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── KPI Stats Row ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Total Purchases</p>
            <p class="stat-value text-indigo-600">₹{{ number_format($totalSales, 2) }}</p>
            <p class="stat-sub">{{ $salesCount }} completed orders</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Amount Paid</p>
            <p class="stat-value text-emerald-600">₹{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Outstanding</p>
            <p class="stat-value {{ $outstanding > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                ₹{{ number_format($outstanding, 2) }}
            </p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Last Purchase</p>
            <p class="stat-value text-gray-700 text-lg">{{ $lastSaleDate ?? '—' }}</p>
            @if($overdueFollowUps->count())
            <p class="stat-sub text-rose-600">{{ $overdueFollowUps->count() }} overdue follow-up(s)</p>
            @endif
        </div>
    </div>

    {{-- ── Tabs ────────────────────────────────────────────────────────────── --}}
    <div x-data="{ tab: '{{ request()->fragment ?? 'overview' }}' }">

        <div class="border-b border-gray-200 flex gap-0 overflow-x-auto">
            @foreach([['overview','Overview'], ['dispensed','Dispensed Medicines'], ['prescriptions','Prescriptions'], ['notes','Notes & Follow-ups'], ['transactions','Transactions'], ['payments','Payments']] as [$key,$label])
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'tab-active' : 'tab-inactive'"
                    class="tab-btn whitespace-nowrap">
                {{ $label }}
                @if($key === 'notes' && $pendingFollowUps->count())
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                    {{ $pendingFollowUps->count() }}
                </span>
                @endif
                @if($key === 'dispensed' && count($dispensedMedicines ?? []))
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                    {{ count($dispensedMedicines) }}
                </span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ── OVERVIEW TAB ──────────────────────────────────────────────── --}}
        <div x-show="tab === 'overview'" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Contact Info --}}
            <div class="card col-span-2">
                <h2 class="card-title mb-4">Patient Profile & Demographics</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach([
                        ['Primary Phone', $customer->phone],
                        ['Alt. Phone', $customer->alternate_phone],
                        ['Email', $customer->email],
                        ['Gender', $customer->gender ? ucfirst($customer->gender) : null],
                        ['Blood Group', $customer->blood_group],
                        ['Date of Birth', $customer->date_of_birth?->format('d M Y')],
                        ['City', $customer->city],
                        ['State', $customer->state],
                        ['Pincode', $customer->pincode],
                        ['Emergency Contact Person', $customer->emergency_contact_name],
                        ['Emergency Contact Phone', $customer->emergency_contact_phone],
                        ['Prescribing Doctor', $customer->doctor_name],
                        ['GSTIN / Tax ID', $customer->tax_number],
                    ] as [$lbl,$val])
                    @if($val)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $lbl }}</dt>
                        <dd class="mt-0.5 text-sm text-gray-800 font-medium">{{ $val }}</dd>
                    </div>
                    @endif
                    @endforeach

                    @if($customer->address)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Address</dt>
                        <dd class="mt-0.5 text-sm text-gray-800">{{ $customer->address }}</dd>
                    </div>
                    @endif
                </dl>

                @if($customer->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Internal Notes</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $customer->notes }}</dd>
                </div>
                @endif
            </div>

            {{-- Relationship Summary --}}
            <div class="space-y-4">
                <div class="card">
                    <h2 class="card-title mb-3">Relationship</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Customer Code</span>
                            <span class="font-mono text-gray-700">{{ $customer->customer_code ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status</span>
                            <span class="font-medium {{ $customer->isActive() ? 'text-emerald-700' : 'text-slate-500' }}">{{ ucfirst($customer->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Customer Since</span>
                            <span class="text-gray-700">{{ $customer->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Invoices</span>
                            <span class="font-semibold text-gray-800">{{ $totalOrders }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Avg. Order Value</span>
                            <span class="font-semibold text-gray-800">₹{{ number_format($avgOrderValue, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Outstanding Balance</span>
                            <span class="font-semibold {{ $customer->outstanding_balance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                ₹{{ number_format($customer->outstanding_balance, 2) }}
                            </span>
                        </div>
                    </dl>
                </div>

                {{-- Tags --}}
                @if($customer->tags && count($customer->tags))
                <div class="card">
                    <h2 class="card-title mb-3">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($customer->tags as $tag)
                        <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium border border-indigo-100">
                            {{ $tag }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Overdue follow-ups alert --}}
                @if($overdueFollowUps->count())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-sm font-semibold text-rose-800 mb-1">⚠️ Overdue Follow-ups</p>
                    <ul class="space-y-1">
                        @foreach($overdueFollowUps as $note)
                        <li class="text-xs text-rose-700">
                            {{ $note->follow_up_date->format('d M Y') }} — {{ Str::limit($note->body, 60) }}
                        </li>
                        @endforeach
                    </ul>
                    <button @click="tab = 'notes'" class="mt-2 text-xs font-semibold text-rose-700 underline">View notes →</button>
                </div>
                @endif
            </div>
        </div>

        {{-- ── DISPENSED MEDICINES TAB ──────────────────────────────────── --}}
        <div x-show="tab === 'dispensed'" class="mt-6">
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="card-title">Dispensed Medicines & Prescription History</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Top medicines purchased and dispensed to this customer from completed POS sales.</p>
                    </div>
                </div>

                @if(!isset($dispensedMedicines) || $dispensedMedicines->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 font-medium">No dispensed medicines recorded yet.</p>
                    <p class="mt-1 text-xs text-gray-400">Completed POS sales for this customer will automatically populate their medication profile here.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="table-th">Medicine Name</th>
                                <th class="table-th">Generic / Strength</th>
                                <th class="table-th text-center">Total Quantity Dispensed</th>
                                <th class="table-th text-center">Invoices / Orders</th>
                                <th class="table-th text-right">Last Dispensed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($dispensedMedicines as $med)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="table-td">
                                    <div class="font-semibold text-gray-900">{{ $med->medicine_name }}</div>
                                </td>
                                <td class="table-td text-gray-600">
                                    <span>{{ $med->generic_name ?? '—' }}</span>
                                    @if(!empty($med->strength))
                                    <span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono">{{ $med->strength }}</span>
                                    @endif
                                </td>
                                <td class="table-td text-center font-semibold text-indigo-700">
                                    {{ $med->total_qty }}
                                </td>
                                <td class="table-td text-center text-gray-700">
                                    {{ $med->orders_count }} order(s)
                                </td>
                                <td class="table-td text-right text-gray-600">
                                    {{ \Carbon\Carbon::parse($med->last_dispensed)->format('d M Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- ── PRESCRIPTIONS TAB (Foundation) ────────────────────────── --}}
        <div x-show="tab === 'prescriptions'" class="mt-6">
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="card-title">Prescription Archive & Doctor References</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Patient prescription records, physician references, and digital Rx copies.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Prescription Foundation
                    </span>
                </div>

                @if($customer->doctor_name)
                <div class="mb-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-sm">
                            Rx
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase">Primary Referring Physician</p>
                            <p class="text-sm font-bold text-slate-800">Dr. {{ $customer->doctor_name }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-slate-500 font-medium">Default for POS Auto-Tagging</span>
                </div>
                @endif

                <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm font-bold text-gray-700">No Digital Prescriptions Uploaded</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        Prescription archive module foundation ready. Digital Rx files, doctor prescriptions, and dosage instructions for {{ $customer->name }} will be accessible here in future prescription phases.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── NOTES TAB ────────────────────────────────────────────────── --}}
        <div x-show="tab === 'notes'" class="mt-6" id="notes">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Add Note Form --}}
                <div class="card">
                    <h2 class="card-title mb-4">Add Interaction</h2>
                    <form action="{{ route('store.customers.notes.store', $customer) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-input" required>
                                <option value="note">📝 Note</option>
                                <option value="call">📞 Phone Call</option>
                                <option value="meeting">🤝 Meeting</option>
                                <option value="email">📧 Email</option>
                                <option value="followup">📅 Follow-up</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Subject <span class="text-gray-400">(optional)</span></label>
                            <input type="text" name="subject" class="form-input" placeholder="Brief subject…" maxlength="255">
                        </div>
                        <div>
                            <label class="form-label">Note <span class="text-rose-500">*</span></label>
                            <textarea name="body" class="form-input" rows="4" placeholder="Write your note…" required maxlength="2000"></textarea>
                        </div>
                        <div>
                            <label class="form-label">Follow-up Date <span class="text-gray-400">(optional)</span></label>
                            <input type="date" name="follow_up_date" class="form-input"
                                   min="{{ now()->toDateString() }}">
                        </div>
                        <button type="submit" class="btn-primary w-full">Save Note</button>
                    </form>
                </div>

                {{-- Notes Timeline --}}
                <div class="lg:col-span-2">
                    @if($notes->isEmpty())
                    <div class="card text-center py-12">
                        <div class="text-4xl mb-3">📝</div>
                        <p class="text-gray-500">No notes yet. Add your first interaction above.</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($notes as $note)
                        <div class="card group relative border-l-4
                            {{ $note->isFollowUpOverdue() ? 'border-rose-400' : ($note->isFollowUpDueToday() ? 'border-amber-400' : 'border-indigo-200') }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-lg">{{ $note->typeIcon() }}</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $note->typeLabel() }}</span>
                                    @if($note->subject)
                                    <span class="text-sm text-gray-500">· {{ $note->subject }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                                    <form action="{{ route('store.customers.notes.destroy', [$customer, $note]) }}" method="POST"
                                          onsubmit="return confirm('Delete this note?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-rose-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <p class="mt-2 text-sm text-gray-700 leading-relaxed">{{ $note->body }}</p>

                            @if($note->created_by && $note->creator)
                            <p class="mt-2 text-xs text-gray-400">By {{ $note->creator->name }}</p>
                            @endif

                            {{-- Follow-up badge --}}
                            @if($note->hasFollowUp())
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="followup-badge followup-{{ $note->followUpStatusBadge() }}">
                                        📅 Follow-up: {{ $note->follow_up_date->format('d M Y') }}
                                    </span>
                                    @if($note->follow_up_done)
                                    <span class="text-xs text-emerald-600 font-medium">✓ Done</span>
                                    @endif
                                </div>
                                @if(! $note->follow_up_done)
                                <form action="{{ route('store.customers.notes.done', [$customer, $note]) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 underline">
                                        Mark done
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── TRANSACTIONS TAB ─────────────────────────────────────────── --}}
        <div x-show="tab === 'transactions'" class="mt-6">
            <div class="card overflow-hidden">
                <h2 class="card-title mb-4">Recent Transactions</h2>
                @if($recentSales->isEmpty())
                <p class="text-sm text-gray-400 py-6 text-center">No sales found for this customer.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="table-th">Invoice</th>
                                <th class="table-th">Date</th>
                                <th class="table-th">Items</th>
                                <th class="table-th text-right">Amount</th>
                                <th class="table-th text-right">Paid</th>
                                <th class="table-th">Status</th>
                                <th class="table-th"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentSales as $sale)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="table-td font-mono font-semibold text-indigo-700">{{ $sale->invoice_number }}</td>
                                <td class="table-td text-gray-600">{{ $sale->sale_date->format('d M Y') }}</td>
                                <td class="table-td text-gray-600">{{ $sale->items->count() }}</td>
                                <td class="table-td text-right font-semibold text-gray-900">₹{{ number_format($sale->grand_total, 2) }}</td>
                                <td class="table-td text-right text-emerald-700">₹{{ number_format($sale->paid_amount, 2) }}</td>
                                <td class="table-td">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($sale->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td class="table-td">
                                    <a href="{{ route('store.sales.show', $sale) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View →</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- ── PAYMENTS TAB ─────────────────────────────────────────────── --}}
        <div x-show="tab === 'payments'" class="mt-6">
            <div class="card overflow-hidden">
                <h2 class="card-title mb-4">Payment History</h2>
                @if($recentPayments->isEmpty())
                <p class="text-sm text-gray-400 py-6 text-center">No payments recorded for this customer.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="table-th">Date</th>
                                <th class="table-th">Method</th>
                                <th class="table-th">Reference</th>
                                <th class="table-th text-right">Amount</th>
                                <th class="table-th">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentPayments as $payment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="table-td text-gray-600">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="table-td capitalize text-gray-700">{{ str_replace('_',' ',$payment->payment_method) }}</td>
                                <td class="table-td font-mono text-xs text-gray-500">{{ $payment->reference_number ?? '—' }}</td>
                                <td class="table-td text-right font-semibold text-emerald-700">₹{{ number_format($payment->amount, 2) }}</td>
                                <td class="table-td text-gray-500 text-xs">{{ Str::limit($payment->notes ?? '', 40) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- end x-data tabs --}}
</div>

@push('styles')
<style>
.stat-card { @apply bg-white rounded-xl border border-gray-100 shadow-sm p-4; }
.stat-label { @apply text-xs font-medium text-gray-400 uppercase tracking-wide; }
.stat-value { @apply text-2xl font-bold mt-1; }
.stat-sub { @apply text-xs text-gray-400 mt-0.5; }

.card { @apply bg-white rounded-xl border border-gray-100 shadow-sm p-5; }
.card-title { @apply text-base font-semibold text-gray-800; }

.tab-btn { @apply px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors; }
.tab-active { @apply border-indigo-600 text-indigo-700; }
.tab-inactive { @apply border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300; }

.table-th { @apply px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide; }
.table-td { @apply px-4 py-3; }

/* Loyalty Tiers */
.loyalty-badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold; }
.loyalty-regular { @apply bg-gray-100 text-gray-600; }
.loyalty-silver { @apply bg-slate-200 text-slate-700; }
.loyalty-gold { @apply bg-amber-100 text-amber-800; }
.loyalty-platinum { @apply bg-purple-100 text-purple-800; }

/* Follow-up badges */
.followup-badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }
.followup-done { @apply bg-emerald-100 text-emerald-700; }
.followup-overdue { @apply bg-rose-100 text-rose-700; }
.followup-today { @apply bg-amber-100 text-amber-700; }
.followup-upcoming { @apply bg-blue-100 text-blue-700; }

.form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
.form-input { @apply block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none; }
.btn-primary { @apply inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors; }
.btn-secondary { @apply inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors; }
</style>
@endpush
@endsection
