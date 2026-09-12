@extends('store.layouts.app')

@section('title', 'Purchase Returns & Debit Notes')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">Purchase Returns</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">Supplier debit notes, damaged medicine returns, and payable adjustments.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('store.purchases.index') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Return from Purchase</span>
            </a>
        </div>
    </div>

    <!-- Alert Banners -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('status') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Returns</span>
            <div class="text-2xl font-black text-[#1e2746] mt-2 font-mono">
                {{ number_format($totalReturnsCount) }}
            </div>
            <div class="text-xs text-slate-400 mt-0.5">Debit notes recorded</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Returned Value</span>
            <div class="text-2xl font-black text-rose-600 mt-2 font-mono">
                ₹{{ number_format($totalReturnedAmount, 2) }}
            </div>
            <div class="text-xs text-rose-400 mt-0.5">Gross returned stock value</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">On-Account Adjusted</span>
            <div class="text-2xl font-black text-[#4b55c8] mt-2 font-mono">
                ₹{{ number_format($totalAdjustedAmount, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-0.5">Payables reduced / credit notes</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Cash / Bank Refunded</span>
            <div class="text-2xl font-black text-emerald-600 mt-2 font-mono">
                ₹{{ number_format($totalRefundedAmount, 2) }}
            </div>
            <div class="text-xs text-emerald-500 mt-0.5">Direct supplier payouts received</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.purchase-returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search return #, invoice #, supplier..." class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none focus:border-[#4b55c8] transition">
            </div>

            <div>
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none focus:border-[#4b55c8] transition">
            </div>

            <div>
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none focus:border-[#4b55c8] transition">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    Filter
                </button>
                <a href="{{ route('store.purchase-returns.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3.5 px-5">Debit Note / Return #</th>
                        <th class="py-3.5 px-5">Date</th>
                        <th class="py-3.5 px-5">Purchase Invoice</th>
                        <th class="py-3.5 px-5">Supplier</th>
                        <th class="py-3.5 px-5 text-center">Qty</th>
                        <th class="py-3.5 px-5 text-right">Gross Total</th>
                        <th class="py-3.5 px-5 text-right">Adjustment</th>
                        <th class="py-3.5 px-5 text-right">Refund</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-[#1e2746]">
                    @forelse($returns as $ret)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-5 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.purchase-returns.show', $ret->id) }}" class="hover:underline">
                                {{ $ret->return_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600">{{ $ret->return_date->format('d M, Y') }}</td>
                        <td class="py-3.5 px-5 font-mono font-bold">
                            @if($ret->purchase)
                                <a href="{{ route('store.purchases.show', $ret->purchase_id) }}" class="text-slate-700 hover:text-[#4b55c8] underline">
                                    {{ $ret->purchase->invoice_number }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 font-bold">
                            @if($ret->supplier)
                                <a href="{{ route('store.suppliers.show', $ret->supplier_id) }}" class="hover:text-[#4b55c8]">
                                    {{ $ret->supplier->name }}
                                </a>
                            @else
                                <span class="text-slate-400">Supplier</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-center font-mono">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-xs">
                                {{ $ret->items->sum('quantity') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right font-mono font-black text-rose-600">
                            ₹{{ number_format($ret->grand_total, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-right font-mono text-slate-700">
                            ₹{{ number_format($ret->adjustment_amount, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-right font-mono font-bold text-emerald-600">
                            ₹{{ number_format($ret->refund_amount, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ ucfirst($ret->status->value) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('store.purchase-returns.show', $ret->id) }}" class="px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                    View
                                </a>
                                <a href="{{ route('store.purchase-returns.print', $ret->id) }}" target="_blank" class="px-2.5 py-1 text-xs font-bold text-[#4b55c8] bg-blue-50 hover:bg-blue-100 rounded-lg transition" title="Print Debit Note">
                                    Print
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-medium">No purchase returns found matching your criteria.</span>
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
