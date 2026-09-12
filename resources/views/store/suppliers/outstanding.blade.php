@extends('store.layouts.app')

@section('title', 'Supplier Outstanding & Payables Report')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.suppliers.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Suppliers
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-mono font-semibold text-slate-400">Financial Reports</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Supplier Outstanding Dues</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Complete analysis of payables, supplier credit limits, outstanding balances, and advances.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Payable (Dues)</span>
            <div class="text-2xl font-black text-rose-600 mt-2 font-mono">
                ₹{{ number_format($totalPayable, 2) }}
            </div>
            <div class="text-xs text-rose-400 mt-1 font-semibold">{{ $suppliersWithDueCount }} Suppliers with dues</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Advances Given</span>
            <div class="text-2xl font-black text-emerald-600 mt-2 font-mono">
                ₹{{ number_format($totalAdvance, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Prepaid store credits</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Net Store Payables</span>
            <div class="text-2xl font-black {{ $netPayable > 0 ? 'text-[#1e2746]' : 'text-emerald-600' }} mt-2 font-mono">
                ₹{{ number_format($netPayable, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Payables minus advances</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Exceeding Credit Limit</span>
            <div class="text-2xl font-black {{ $suppliersOverLimitCount > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-2">
                {{ $suppliersOverLimitCount }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Vendors past safe limit</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5 space-y-3">
        <form method="GET" action="{{ route('store.suppliers.outstanding') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by vendor name, code, company, phone..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
            </div>

            <div class="w-full md:w-44">
                <select name="status" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <input type="hidden" name="filter" value="{{ $filter }}">

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('store.suppliers.outstanding', ['filter' => $filter]) }}" class="px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 overflow-x-auto">
            <a href="{{ route('store.suppliers.outstanding', array_merge(request()->except('filter'), ['filter' => 'all'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'all' ? 'bg-[#4b55c8] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Vendors
            </a>
            <a href="{{ route('store.suppliers.outstanding', array_merge(request()->except('filter'), ['filter' => 'has_due'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'has_due' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                With Dues Only ({{ $suppliersWithDueCount }})
            </a>
            <a href="{{ route('store.suppliers.outstanding', array_merge(request()->except('filter'), ['filter' => 'over_limit'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'over_limit' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                Over Credit Limit ({{ $suppliersOverLimitCount }})
            </a>
            <a href="{{ route('store.suppliers.outstanding', array_merge(request()->except('filter'), ['filter' => 'advance'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'advance' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                With Advance Balance
            </a>
        </div>
    </div>

    <!-- Outstanding Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        @if(empty($rows))
            <div class="p-12 text-center text-slate-400 text-xs">
                No supplier records match the selected filter criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider bg-slate-50/60">
                            <th class="py-3.5 px-5">Supplier & Code</th>
                            <th class="py-3.5 px-5">Contact</th>
                            <th class="py-3.5 px-5 text-right">Opening Bal (₹)</th>
                            <th class="py-3.5 px-5 text-right">Purchased (₹)</th>
                            <th class="py-3.5 px-5 text-right">Paid (₹)</th>
                            <th class="py-3.5 px-5 text-right">Net Outstanding</th>
                            <th class="py-3.5 px-5 text-center">Credit Limit</th>
                            <th class="py-3.5 px-5 text-center">Last Purchase</th>
                            <th class="py-3.5 px-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($rows as $row)
                            @php
                                $sup = $row['supplier'];
                                $out = $row['outstanding'];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition {{ $row['is_over_limit'] ? 'bg-amber-50/30' : '' }}">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('store.suppliers.show', $sup) }}" class="font-bold text-slate-900 hover:text-[#4b55c8] transition">
                                            {{ $sup->name }}
                                        </a>
                                        @if($sup->supplier_code)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600">
                                                {{ $sup->supplier_code }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($sup->company_name)
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $sup->company_name }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-semibold">{{ $sup->phone }}</div>
                                    @if($sup->gst_number)
                                        <div class="text-[10px] text-slate-400 font-mono">GST: {{ $sup->gst_number }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-right font-mono">
                                    ₹{{ number_format($row['opening_balance'], 2) }}
                                    <span class="block text-[10px] text-slate-400 font-sans font-normal">({{ ucfirst($row['opening_type']) }})</span>
                                </td>
                                <td class="py-4 px-5 text-right font-mono font-semibold text-slate-900">
                                    ₹{{ number_format($row['total_purchased'], 2) }}
                                </td>
                                <td class="py-4 px-5 text-right font-mono font-semibold text-emerald-700">
                                    ₹{{ number_format($row['total_paid'], 2) }}
                                </td>
                                <td class="py-4 px-5 text-right">
                                    @if($out > 0)
                                        <span class="font-mono font-bold text-rose-600 text-sm">
                                            ₹{{ number_format($out, 2) }}
                                        </span>
                                        <span class="block text-[10px] font-bold text-rose-500 font-sans">Payable</span>
                                    @elseif($out < 0)
                                        <span class="font-mono font-bold text-emerald-600 text-sm">
                                            ₹{{ number_format(abs($out), 2) }}
                                        </span>
                                        <span class="block text-[10px] font-bold text-emerald-500 font-sans">Advance</span>
                                    @else
                                        <span class="font-mono text-slate-400">₹0.00</span>
                                        <span class="block text-[10px] text-slate-400 font-sans">Settled</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-center">
                                    @if($row['credit_limit'])
                                        <div class="font-mono text-slate-800 font-semibold">₹{{ number_format($row['credit_limit'], 0) }}</div>
                                        @if($row['is_over_limit'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Over Limit!
                                            </span>
                                        @else
                                            <span class="text-[10px] text-emerald-600">Within Limit</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">No Limit</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-center text-slate-500">
                                    {{ $row['last_purchase_date'] ?? '—' }}
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('store.suppliers.ledger', $sup) }}" class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 font-bold text-xs" title="Supplier Ledger">
                                            Ledger →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
