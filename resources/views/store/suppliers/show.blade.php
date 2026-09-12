@extends('store.layouts.app')

@section('title', 'Supplier: ' . $supplier->name)

@section('content')
<div x-data="{ paymentModal: false }" class="space-y-6 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('store.suppliers.index') }}"
               class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-black text-[#1e2746]">{{ $supplier->name }}</h1>
                    @if($supplier->supplier_code)
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $supplier->supplier_code }}
                        </span>
                    @endif
                    @if($supplier->company_name)
                        <span class="text-sm text-slate-500 font-normal">· {{ $supplier->company_name }}</span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $supplier->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ ucfirst($supplier->status) }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    {{ $supplier->phone }}
                    @if($supplier->email) · {{ $supplier->email }} @endif
                    @if($supplier->contact_person) · Contact: {{ $supplier->contact_person }} @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button @click="paymentModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Record Payment</span>
            </button>

            <a href="{{ route('store.suppliers.ledger', $supplier) }}" class="px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-xs sm:text-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Ledger Statement</span>
            </a>

            <a href="{{ route('store.suppliers.edit', $supplier) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition flex items-center gap-1">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit</span>
            </a>

            <form method="POST" action="{{ route('store.suppliers.toggle-status', $supplier) }}" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-semibold transition" title="Toggle active/inactive status">
                    {{ $supplier->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    {{-- ── Alerts ─────────────────────────────────────────────────────────── --}}
    @if(session('status'))
    <div class="flex items-center gap-2 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold">
        <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 14l-4.121-4.121a1 1 0 011.414-1.414L8.414 11.172l6.879-6.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        {{ session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-2 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">
        <svg class="w-4 h-4 flex-shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── KPI Stats Row ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Total Purchased</p>
            <p class="stat-value text-indigo-600">₹{{ number_format($totalPurchased, 2) }}</p>
            <p class="stat-sub">{{ $purchasesCount }} completed orders</p>
        </div>

        <div class="stat-card">
            <p class="stat-label">Amount Paid</p>
            <p class="stat-value text-emerald-600">₹{{ number_format($totalPaid, 2) }}</p>
            <p class="stat-sub">Invoices & direct payments</p>
        </div>

        <div class="stat-card">
            <p class="stat-label">Net Balance</p>
            <p class="stat-value {{ $outstanding > 0 ? 'text-rose-600' : ($outstanding < 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                ₹{{ number_format(abs($outstanding), 2) }}
            </p>
            <p class="stat-sub font-semibold {{ $outstanding > 0 ? 'text-rose-500' : ($outstanding < 0 ? 'text-emerald-500' : 'text-slate-400') }}">
                {{ $outstanding > 0 ? 'Payable (You owe)' : ($outstanding < 0 ? 'Advance (Credit)' : 'Settled') }}
            </p>
        </div>

        <div class="stat-card">
            <p class="stat-label">Credit Terms</p>
            @if($supplier->credit_limit)
                <p class="stat-value text-slate-800 text-lg">₹{{ number_format($supplier->credit_limit, 2) }}</p>
                <p class="stat-sub text-slate-500">Avail: ₹{{ number_format($availableCredit ?? 0, 2) }}</p>
            @else
                <p class="stat-value text-slate-400 text-lg">No Limit</p>
                <p class="stat-sub">{{ $supplier->payment_terms ?: 'Standard terms' }}</p>
            @endif
        </div>
    </div>

    {{-- ── Tabs ────────────────────────────────────────────────────────────── --}}
    <div x-data="{ tab: '{{ request()->fragment ?? 'overview' }}' }">

        <div class="border-b border-slate-200 flex gap-1 overflow-x-auto">
            @foreach([['overview','Overview'], ['purchases','Purchases (Invoices)'], ['payments','Payment History'], ['notes','Notes & Follow-ups']] as [$key,$label])
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-[#4b55c8] text-[#4b55c8] font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="px-4 py-3 text-xs sm:text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap">
                {{ $label }}
                @if($key === 'notes' && $pendingFollowUps->count())
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">
                    {{ $pendingFollowUps->count() }}
                </span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ── OVERVIEW TAB ──────────────────────────────────────────────── --}}
        <div x-show="tab === 'overview'" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Details -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2">Business & Contact Details</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <div>
                            <dt class="text-slate-400 font-semibold">Vendor Code</dt>
                            <dd class="font-mono font-bold text-slate-700">{{ $supplier->supplier_code ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Company Name</dt>
                            <dd class="font-medium text-slate-800">{{ $supplier->company_name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Contact Person</dt>
                            <dd class="font-medium text-slate-800">{{ $supplier->contact_person ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Primary Phone</dt>
                            <dd class="font-medium text-slate-800">{{ $supplier->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Alternate Phone</dt>
                            <dd class="font-medium text-slate-800">{{ $supplier->alternate_phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Email Address</dt>
                            <dd class="font-medium text-slate-800">{{ $supplier->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">GSTIN</dt>
                            <dd class="font-mono font-bold text-slate-800">{{ $supplier->gst_number ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">PAN Number</dt>
                            <dd class="font-mono font-bold text-slate-800">{{ $supplier->pan_number ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Drug License No</dt>
                            <dd class="font-mono text-slate-800">{{ $supplier->drug_license_no ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Registered Address</dt>
                            <dd class="text-slate-700">
                                {{ $supplier->address ?: '—' }}
                                @if($supplier->city)
                                    <br>{{ $supplier->city }}{{ $supplier->state ? ', ' . $supplier->state : '' }}{{ $supplier->pincode ? ' - ' . $supplier->pincode : '' }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Financial Ledger Summary -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-extrabold text-[#1e2746]">Financial & Ledger Setup</h3>
                        <a href="{{ route('store.suppliers.ledger', $supplier) }}" class="text-xs font-bold text-[#4b55c8] hover:underline">
                            Full Statement →
                        </a>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <div>
                            <dt class="text-slate-400 font-semibold">Initial Opening Balance</dt>
                            <dd class="font-mono font-bold text-slate-800">
                                ₹{{ number_format($supplier->opening_balance ?? 0, 2) }}
                                <span class="text-xs text-slate-400 font-normal">({{ ucfirst($supplier->opening_balance_type ?? 'payable') }})</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Payment Terms</dt>
                            <dd class="font-medium text-slate-800">{{ $supplier->payment_terms ?: 'None specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Credit Limit</dt>
                            <dd class="font-mono font-bold text-slate-800">
                                {{ $supplier->credit_limit ? '₹' . number_format($supplier->credit_limit, 2) : 'No limit set' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Available Credit</dt>
                            <dd class="font-mono font-bold {{ ($availableCredit ?? 0) > 0 ? 'text-emerald-600' : 'text-slate-600' }}">
                                {{ $availableCredit !== null ? '₹' . number_format($availableCredit, 2) : 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Side Card: Quick Actions & Notes -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 space-y-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Quick Actions</h4>
                    <div class="space-y-2">
                        <button @click="paymentModal = true" class="w-full py-2.5 px-4 rounded-2xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-between transition">
                            <span>Record Payment to Vendor</span>
                            <span class="text-emerald-600">→</span>
                        </button>
                        <a href="{{ route('store.suppliers.ledger', $supplier) }}" class="w-full py-2.5 px-4 rounded-2xl bg-indigo-50 hover:bg-indigo-100 text-indigo-800 font-bold text-xs flex items-center justify-between transition">
                            <span>Open Detailed Ledger</span>
                            <span class="text-indigo-600">→</span>
                        </a>
                        <a href="{{ route('store.suppliers.ledger.print', $supplier) }}" target="_blank" class="w-full py-2.5 px-4 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-between transition">
                            <span>Print Ledger Statement</span>
                            <span class="text-slate-400">🖨</span>
                        </a>
                        <a href="{{ route('store.purchases.create') }}?supplier_id={{ $supplier->id }}" class="w-full py-2.5 px-4 rounded-2xl bg-blue-50 hover:bg-blue-100 text-[#4b55c8] font-bold text-xs flex items-center justify-between transition">
                            <span>Create Purchase Order</span>
                            <span class="text-[#4b55c8]">→</span>
                        </a>
                    </div>
                </div>

                @if($supplier->notes)
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 space-y-2">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Internal Remarks</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $supplier->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── PURCHASES TAB ───────────────────────────────────────────── --}}
        <div x-show="tab === 'purchases'" class="mt-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-[#1e2746]">Purchase Orders & Invoices</h3>
                    <a href="{{ route('store.purchases.create') }}?supplier_id={{ $supplier->id }}" class="px-3 py-1.5 rounded-xl bg-[#4b55c8] text-white text-xs font-bold shadow-sm">
                        + New Purchase
                    </a>
                </div>

                @if($recentPurchases->isEmpty())
                    <p class="text-xs text-slate-400 py-8 text-center">No purchases recorded from this supplier yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4">Invoice #</th>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Items</th>
                                    <th class="py-3 px-4 text-right">Total (₹)</th>
                                    <th class="py-3 px-4 text-right">Paid (₹)</th>
                                    <th class="py-3 px-4 text-right">Due (₹)</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @foreach($recentPurchases as $purchase)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                            <a href="{{ route('store.purchases.show', $purchase) }}" class="hover:text-[#4b55c8]">
                                                {{ $purchase->invoice_number }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4">{{ $purchase->purchase_date->format('d M Y') }}</td>
                                        <td class="py-3 px-4">{{ $purchase->items->count() }}</td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">₹{{ number_format($purchase->grand_total, 2) }}</td>
                                        <td class="py-3 px-4 text-right font-mono font-semibold text-emerald-600">₹{{ number_format($purchase->paid_amount, 2) }}</td>
                                        <td class="py-3 px-4 text-right font-mono font-bold {{ $purchase->outstandingAmount() > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                            ₹{{ number_format($purchase->outstandingAmount(), 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                                {{ $purchase->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                {{ ucfirst($purchase->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('store.purchases.show', $purchase) }}" class="text-[#4b55c8] hover:underline font-bold">
                                                View →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-3">
                        {{ $recentPurchases->fragment('purchases')->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── PAYMENTS TAB ───────────────────────────────────────────── --}}
        <div x-show="tab === 'payments'" class="mt-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-[#1e2746]">Supplier Payment History</h3>
                    <button @click="paymentModal = true" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                        + Record Payment
                    </button>
                </div>

                @if($payments->isEmpty())
                    <p class="text-xs text-slate-400 py-8 text-center">No payment transactions recorded for this supplier yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4">Payment #</th>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Method</th>
                                    <th class="py-3 px-4">Allocated To</th>
                                    <th class="py-3 px-4">Ref / Transaction #</th>
                                    <th class="py-3 px-4 text-right">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @foreach($payments as $pay)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $pay->payment_number }}</td>
                                        <td class="py-3 px-4">{{ $pay->payment_date->format('d M Y') }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                {{ $pay->payment_method->label() ?? $pay->payment_method }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($pay->purchase)
                                                <a href="{{ route('store.purchases.show', $pay->purchase) }}" class="text-[#4b55c8] hover:underline font-mono font-semibold">
                                                    Invoice #{{ $pay->purchase->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-slate-400 italic">On-account / Direct</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-mono text-slate-500">{{ $pay->reference_number ?: '—' }}</td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 text-sm">
                                            ₹{{ number_format($pay->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-3">
                        {{ $payments->fragment('payments')->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── NOTES TAB ───────────────────────────────────────────────── --}}
        <div x-show="tab === 'notes'" class="mt-6 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-[#1e2746] mb-4">Add Contact Note / Follow-up</h3>
                <form method="POST" action="{{ route('store.suppliers.notes.store', $supplier) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Interaction Type</label>
                            <select name="type" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                                <option value="call">Phone Call</option>
                                <option value="meeting">Meeting / Visit</option>
                                <option value="email">Email</option>
                                <option value="note">General Note</option>
                                <option value="followup">Payment Follow-up</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Subject</label>
                            <input type="text" name="subject" placeholder="e.g. Payment due reminder" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Follow-up Date (Optional)</label>
                            <input type="date" name="follow_up_date" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Note Details</label>
                        <textarea name="body" rows="2" required placeholder="Interaction summary, discussed delivery terms, payment commitments..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#4b55c8] text-white text-xs font-bold hover:bg-[#3f49b8] transition">
                            Save Note
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notes List -->
            <div class="space-y-3">
                @forelse($notes as $note)
                    <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-start justify-between gap-4">
                        <div class="space-y-1 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 uppercase text-[10px] tracking-wider bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $note->typeLabel() }}
                                </span>
                                @if($note->subject)
                                    <span class="font-bold text-slate-800">{{ $note->subject }}</span>
                                @endif
                                <span class="text-slate-400 text-[11px]">· {{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-700 leading-relaxed">{{ $note->body }}</p>
                            @if($note->hasFollowUp())
                                <div class="pt-1 flex items-center gap-2 text-[11px]">
                                    <span class="font-semibold {{ $note->isFollowUpOverdue() ? 'text-rose-600' : 'text-amber-600' }}">
                                        📅 Follow-up: {{ $note->follow_up_date->format('d M Y') }}
                                    </span>
                                    @if($note->follow_up_done)
                                        <span class="text-emerald-600 font-bold">✓ Done</span>
                                    @else
                                        <form method="POST" action="{{ route('store.suppliers.notes.done', [$supplier, $note]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-[#4b55c8] hover:underline font-bold">Mark Completed</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('store.suppliers.notes.destroy', [$supplier, $note]) }}" onsubmit="return confirm('Delete this note?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">No notes or follow-ups logged yet.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ── RECORD PAYMENT MODAL ────────────────────────────────────────── --}}
    <div x-show="paymentModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div @click.away="paymentModal = false" class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-lg w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-black text-[#1e2746]">Record Supplier Payment</h3>
                    <p class="text-xs text-slate-500">Pay {{ $supplier->name }} · Net Due: ₹{{ number_format($outstanding, 2) }}</p>
                </div>
                <button @click="paymentModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
            </div>

            <form method="POST" action="{{ route('store.suppliers.record-payment', $supplier) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" required value="{{ old('amount', $outstanding > 0 ? $outstanding : '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-mono font-bold focus:border-emerald-600 outline-none" placeholder="0.00">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Payment Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method <span class="text-rose-500">*</span></label>
                        <select name="payment_method" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card (Credit/Debit)</option>
                            <option value="net_banking">Net Banking</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Allocate to Specific Purchase (Optional)</label>
                    <select name="purchase_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                        <option value="">-- Direct Payment / On Account (Unallocated) --</option>
                        @foreach($pendingPurchases as $p)
                            <option value="{{ $p->id }}">
                                Invoice #{{ $p->invoice_number }} ({{ $p->purchase_date->format('d M Y') }}) — Due: ₹{{ number_format($p->outstandingAmount(), 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reference / Cheque / UTR #</label>
                    <input type="text" name="reference_number" placeholder="e.g. UTR12345678, CHQ-998822" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Optional transaction memo" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="paymentModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition">
                        Confirm & Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('styles')
<style>
.stat-card { @apply bg-white rounded-3xl border border-slate-100 shadow-sm p-5; }
.stat-label { @apply text-xs font-bold text-slate-400 uppercase tracking-wider; }
.stat-value { @apply text-2xl font-black mt-1.5; }
.stat-sub { @apply text-xs text-slate-400 mt-0.5; }
</style>
@endpush
@endsection
