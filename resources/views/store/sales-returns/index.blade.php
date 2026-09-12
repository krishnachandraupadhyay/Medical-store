@extends('store.layouts.app')

@section('title', 'Sales Returns')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sales Returns & Refunds</h1>
            <p class="text-sm text-slate-500 mt-1">Customer medicine returns, batch stock replenishment, dues adjustment, and credit notes.</p>
        </div>
        <a href="{{ route('store.sales.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Initiate Return from Sale
        </a>
    </div>

    <!-- KPI Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Returns</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalReturnsCount) }}</p>
            <span class="text-[11px] text-slate-400">Completed returns</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Return Value</p>
            <p class="text-2xl font-black text-slate-900 mt-1">₹{{ number_format($totalReturnValue, 2) }}</p>
            <span class="text-[11px] text-slate-400">Gross medicine value</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Refunds Paid Out</p>
            <p class="text-2xl font-black text-amber-600 mt-1">₹{{ number_format($totalRefundedValue, 2) }}</p>
            <span class="text-[11px] text-amber-600/80 font-medium">Cash/UPI/Card refunds</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dues Adjusted</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($totalAdjustedValue, 2) }}</p>
            <span class="text-[11px] text-emerald-600/80 font-medium">Reduced customer dues</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.sales-returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Return #, Invoice #, Customer..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Customer</label>
                <select name="customer_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->customer_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-xs transition">
                    Apply Filter
                </button>
                @if(request()->hasAny(['search', 'customer_id', 'from_date', 'to_date']))
                    <a href="{{ route('store.sales-returns.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition" title="Reset Filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Return #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Original Sale</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Returned Items</th>
                        <th class="py-3.5 px-4">Return Value</th>
                        <th class="py-3.5 px-4">Settlement</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($returns as $ret)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.sales-returns.show', $ret->id) }}" class="hover:underline">
                                {{ $ret->return_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $ret->return_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            <a href="{{ route('store.sales.show', $ret->sale_id) }}" class="hover:underline text-slate-700">
                                {{ $ret->sale?->invoice_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-900">
                            {{ $ret->customer?->name ?: ($ret->sale?->customer_name ?: 'Walk-in Customer') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-800">{{ $ret->items->sum('quantity') }}</span> units
                            <span class="text-[11px] text-slate-400 block">({{ $ret->items->count() }} medicines)</span>
                        </td>
                        <td class="py-3 px-4 font-black text-slate-900">₹{{ number_format($ret->grand_total, 2) }}</td>
                        <td class="py-3 px-4 text-xs">
                            @if($ret->refund_amount > 0)
                                <div class="text-amber-700 font-bold">Refund: ₹{{ number_format($ret->refund_amount, 2) }}</div>
                            @endif
                            @if($ret->adjustment_amount > 0)
                                <div class="text-emerald-700 font-bold">Adjusted: ₹{{ number_format($ret->adjustment_amount, 2) }}</div>
                            @endif
                            @if($ret->refund_amount <= 0 && $ret->adjustment_amount <= 0)
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ ucfirst($ret->status->value) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('store.sales-returns.receipt', $ret->id) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 rounded-lg transition" title="Print Credit Note / Receipt">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <a href="{{ route('store.sales-returns.show', $ret->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            No sales returns recorded matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
