@extends('store.layouts.app')

@section('title', 'Supplier Management & Ledger')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Vendor Management
                </span>
                <span class="text-xs font-mono font-semibold text-slate-400">Phase 33</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Suppliers & Ledger</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Manage vendor profiles, track invoices, payments, statements, and store-wide outstanding dues.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('store.suppliers.outstanding') }}" class="px-4 py-2.5 rounded-2xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Outstanding Report</span>
            </a>

            <a href="{{ route('store.suppliers.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add New Supplier</span>
            </a>
        </div>
    </div>

    <!-- Alert Banners -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ session('status') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Suppliers</span>
                <span class="p-2 rounded-xl bg-blue-50 text-[#4b55c8]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-[#1e2746] mt-2">{{ number_format($totalSuppliersCount) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $activeSuppliersCount }} Active vendors</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Outstanding Due</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-600 mt-2">₹{{ number_format($totalOutstandingDue, 2) }}</div>
            <div class="text-xs text-rose-400 mt-1">{{ $suppliersWithDueCount }} Suppliers with dues</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Advance Given</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600 mt-2">₹{{ number_format($totalAdvanceGiven, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Prepaid / Advance balance</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Net Payable</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black {{ ($totalOutstandingDue - $totalAdvanceGiven) > 0 ? 'text-[#1e2746]' : 'text-emerald-600' }} mt-2">
                ₹{{ number_format($totalOutstandingDue - $totalAdvanceGiven, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Outstanding minus advance</div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.suppliers.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code (SUP-000001), company, phone, GST, PAN..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] focus:ring-1 focus:ring-[#4b55c8] outline-none">
            </div>

            <div class="w-full md:w-40">
                <select name="status" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="w-full md:w-44">
                <select name="balance" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Balances</option>
                    <option value="has_due" {{ request('balance') === 'has_due' ? 'selected' : '' }}>With Dues Only</option>
                    <option value="advance" {{ request('balance') === 'advance' ? 'selected' : '' }}>With Advance Only</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'status', 'balance']))
                    <a href="{{ route('store.suppliers.index') }}" class="px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        @if ($suppliers->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1e2746]">No Suppliers Found</h3>
                <p class="text-xs text-[#64748b] mt-1 max-w-sm mx-auto">
                    You have not registered any suppliers matching your criteria. Add your first supplier to begin recording purchase invoices.
                </p>
                <div class="mt-4">
                    <a href="{{ route('store.suppliers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-[#4b55c8] text-white text-xs font-bold shadow-md shadow-[#4b55c8]/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add First Supplier</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">Supplier Info</th>
                            <th class="py-3.5 px-6">Contact Details</th>
                            <th class="py-3.5 px-6">Tax Identifiers</th>
                            <th class="py-3.5 px-6 text-right">Total Purchased</th>
                            <th class="py-3.5 px-6 text-right">Total Paid</th>
                            <th class="py-3.5 px-6 text-right">Net Outstanding</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                        @foreach ($suppliers as $supplier)
                            @php
                                $purchased = $supplier->totalPurchased();
                                $paid = $supplier->totalPaid();
                                $outstanding = $supplier->outstandingAmount();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('store.suppliers.show', $supplier) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] transition">
                                            {{ $supplier->name }}
                                        </a>
                                        @if ($supplier->supplier_code)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600">
                                                {{ $supplier->supplier_code }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($supplier->company_name)
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $supplier->company_name }}</div>
                                    @endif
                                    @if ($supplier->city)
                                        <div class="text-[10px] text-slate-400">{{ $supplier->city }}{{ $supplier->state ? ', ' . $supplier->state : '' }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs">
                                    <div class="font-semibold">{{ $supplier->phone }}</div>
                                    @if ($supplier->email)
                                        <div class="text-[11px] text-slate-400">{{ $supplier->email }}</div>
                                    @endif
                                    @if ($supplier->contact_person)
                                        <div class="text-[10px] text-slate-500 mt-0.5">Attn: {{ $supplier->contact_person }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs font-mono">
                                    @if ($supplier->gst_number)
                                        <div><span class="text-[10px] text-slate-400 font-sans">GST:</span> {{ $supplier->gst_number }}</div>
                                    @endif
                                    @if ($supplier->pan_number)
                                        <div class="text-[11px] text-slate-500"><span class="text-[10px] text-slate-400 font-sans">PAN:</span> {{ $supplier->pan_number }}</div>
                                    @endif
                                    @if (! $supplier->gst_number && ! $supplier->pan_number)
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-semibold text-slate-700">
                                    ₹{{ number_format($purchased, 2) }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-semibold text-emerald-700">
                                    ₹{{ number_format($paid, 2) }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    @if ($outstanding > 0)
                                        <span class="font-mono font-bold text-rose-600">
                                            ₹{{ number_format($outstanding, 2) }}
                                        </span>
                                        <span class="block text-[10px] text-rose-400 font-sans font-semibold">Payable</span>
                                    @elseif ($outstanding < 0)
                                        <span class="font-mono font-bold text-emerald-600">
                                            ₹{{ number_format(abs($outstanding), 2) }}
                                        </span>
                                        <span class="block text-[10px] text-emerald-400 font-sans font-semibold">Advance</span>
                                    @else
                                        <span class="font-mono text-slate-400 font-semibold">₹0.00</span>
                                        <span class="block text-[10px] text-slate-400 font-sans">Settled</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <form method="POST" action="{{ route('store.suppliers.toggle-status', $supplier) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border transition {{ $supplier->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}" title="Click to toggle status">
                                            {{ ucfirst($supplier->status) }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('store.suppliers.ledger', $supplier) }}" class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 transition" title="View Supplier Ledger">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('store.suppliers.show', $supplier) }}" class="p-2 rounded-xl text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 transition" title="View 360° Profile">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('store.suppliers.edit', $supplier) }}" class="p-2 rounded-xl text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 transition" title="Edit supplier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
