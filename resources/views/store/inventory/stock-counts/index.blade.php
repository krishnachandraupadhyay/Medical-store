@extends('store.layouts.app')

@section('title', 'Stock Count & Reconciliation')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Inventory
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                    Phase 24 Control
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Stock Count & Reconciliation</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Conduct physical inventory audits, record actual shelf quantities, detect variances, and post atomic adjustments.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.inventory.valuation') }}" class="px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Stock Valuation</span>
            </a>
            <a href="{{ route('store.inventory.stock-counts.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                <span>New Stock Count</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold ml-4">✕</button>
        </div>
    @endif

    <!-- Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Counts</div>
            <div class="text-2xl font-black text-[#1e2746] mt-1">{{ number_format($metrics['total']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Audit sessions recorded</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-amber-500">Draft / In Progress</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($metrics['draft']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Pending physical entries</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">Reconciled / Completed</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($metrics['completed']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Stock movements posted</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Cancelled</div>
            <div class="text-2xl font-black text-slate-500 mt-1">{{ number_format($metrics['cancelled']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Discarded count drafts</div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" action="{{ route('store.inventory.stock-counts.index') }}" class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-3 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[280px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Count # (e.g. SC-)..."
                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] w-48 sm:w-64 bg-white">
                
                <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-white text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-white text-slate-700" title="From Date">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-white text-slate-700" title="To Date">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1e2746] text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.inventory.stock-counts.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Count #</th>
                        <th class="py-3.5 px-6">Count Date</th>
                        <th class="py-3.5 px-6">Scope</th>
                        <th class="py-3.5 px-6 text-center">Items Counted</th>
                        <th class="py-3.5 px-6 text-right">Net Variance</th>
                        <th class="py-3.5 px-6 text-right">Variance Cost</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[#1e2746]">
                    @forelse ($stockCounts as $count)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 font-mono font-bold text-[#4b55c8]">
                                <a href="{{ route('store.inventory.stock-counts.show', $count) }}" class="hover:underline">
                                    {{ $count->count_number }}
                                </a>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $count->count_date->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="capitalize font-semibold text-slate-700">{{ $count->scope }}</span>
                            </td>
                            <td class="py-4 px-6 text-center font-bold">
                                {{ $count->total_items }}
                            </td>
                            <td class="py-4 px-6 text-right font-bold font-mono">
                                @if ($count->total_variance_units > 0)
                                    <span class="text-emerald-600">+{{ $count->total_variance_units }}</span>
                                @elseif ($count->total_variance_units < 0)
                                    <span class="text-rose-600">{{ $count->total_variance_units }}</span>
                                @else
                                    <span class="text-slate-500">0</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-bold font-mono">
                                @if ($count->total_variance_cost > 0)
                                    <span class="text-emerald-600">+₹{{ number_format($count->total_variance_cost, 2) }}</span>
                                @elseif ($count->total_variance_cost < 0)
                                    <span class="text-rose-600">-₹{{ number_format(abs($count->total_variance_cost), 2) }}</span>
                                @else
                                    <span class="text-slate-500">₹0.00</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wide border {{ $count->status->badgeClasses() }}">
                                    {{ $count->status->label() }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('store.inventory.stock-counts.show', $count) }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-[#4b55c8] hover:bg-blue-50 transition">
                                    {{ $count->isDraft() ? 'Enter Counts' : 'View Audit' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div class="font-bold text-[#1e2746]">No stock counts found</div>
                                <div class="text-xs text-slate-500 mt-1">Start a physical inventory count session to audit and reconcile warehouse stock.</div>
                                <div class="mt-4">
                                    <a href="{{ route('store.inventory.stock-counts.create') }}" class="px-4 py-2 rounded-xl bg-[#4b55c8] text-white text-xs font-bold inline-flex items-center gap-1">
                                        + Start New Stock Count
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stockCounts->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $stockCounts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
