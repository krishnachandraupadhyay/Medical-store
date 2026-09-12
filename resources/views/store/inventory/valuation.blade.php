@extends('store.layouts.app')

@section('title', 'Inventory Valuation & Capital Insights')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Inventory
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                    Financial Analytics
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Inventory Valuation & Insights</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Real-time financial capital locked in warehouse inventory, retail realization, profit potential, and loss exposure.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.inventory.stock-counts.index') }}" class="px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Stock Reconciliation</span>
            </a>
            <a href="{{ route('store.reports.inventory') }}" class="px-4 py-2.5 rounded-2xl bg-[#1e2746] text-white font-bold text-xs sm:text-sm hover:bg-slate-800 transition">
                Full Inventory Report
            </a>
        </div>
    </div>

    <!-- Core Valuation KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Purchase Cost Valuation -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Purchase Valuation</div>
            <div class="text-2xl sm:text-3xl font-black text-[#4b55c8] mt-1 font-mono">
                ₹{{ number_format($valuation['purchase_valuation'], 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Cost value of {{ number_format($valuation['total_units']) }} in-stock units</div>
        </div>

        <!-- Selling Valuation -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Retail Sales Valuation</div>
            <div class="text-2xl sm:text-3xl font-black text-[#1e2746] mt-1 font-mono">
                ₹{{ number_format($valuation['selling_valuation'], 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Estimated gross retail revenue</div>
        </div>

        <!-- Potential Profit -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">Potential Gross Profit</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1 font-mono">
                ₹{{ number_format($valuation['potential_profit'], 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">
                Margin: {{ $valuation['selling_valuation'] > 0 ? round(($valuation['potential_profit'] / $valuation['selling_valuation']) * 100, 1) : 0 }}%
            </div>
        </div>

        <!-- Total Batches -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Batches with Stock</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-700 mt-1">
                {{ number_format($valuation['total_batches']) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Active warehouse lots</div>
        </div>
    </div>

    <!-- Capital Risk & Quarantine Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Expired Stock Capital -->
        <div class="bg-white rounded-3xl border border-rose-100 p-5 shadow-sm bg-rose-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Expired Stock Capital</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">{{ $valuation['expired_units'] }} units</span>
            </div>
            <div class="text-2xl font-black text-rose-600 mt-2 font-mono">
                ₹{{ number_format($valuation['expired_valuation'], 2) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Lost capital locked in past-expiry batches</div>
            <div class="mt-3">
                <a href="{{ route('store.inventory.expired.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Process Disposal →</a>
            </div>
        </div>

        <!-- Expiring Soon Capital (<90d) -->
        <div class="bg-white rounded-3xl border border-amber-100 p-5 shadow-sm bg-amber-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">Expiring in 90 Days</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">{{ $valuation['expiring_soon_units'] }} units</span>
            </div>
            <div class="text-2xl font-black text-amber-600 mt-2 font-mono">
                ₹{{ number_format($valuation['expiring_soon_valuation'], 2) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">At-risk capital requiring promotion/sales</div>
            <div class="mt-3">
                <a href="{{ route('store.inventory.index', ['expiry_status' => 'expiring_soon']) }}" class="text-xs font-bold text-amber-600 hover:underline">View Batches →</a>
            </div>
        </div>

        <!-- Blocked/Quarantined Capital -->
        <div class="bg-white rounded-3xl border border-purple-100 p-5 shadow-sm bg-purple-50/20">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-purple-600">Quarantined / Blocked</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">{{ $valuation['blocked_units'] }} units</span>
            </div>
            <div class="text-2xl font-black text-purple-700 mt-2 font-mono">
                ₹{{ number_format($valuation['blocked_valuation'], 2) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Temporarily blocked from sales</div>
            <div class="mt-3">
                <a href="{{ route('store.inventory.index', ['status' => 'blocked']) }}" class="text-xs font-bold text-purple-600 hover:underline">Manage Status →</a>
            </div>
        </div>
    </div>

    <!-- Category Valuation Breakdown Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-base font-black text-[#1e2746]">Valuation Breakdown by Medicine Category</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Category</th>
                        <th class="py-3.5 px-6 text-right">In-Stock Units</th>
                        <th class="py-3.5 px-6 text-right">Purchase Value</th>
                        <th class="py-3.5 px-6 text-right">Retail Value</th>
                        <th class="py-3.5 px-6 text-right">Estimated Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[#1e2746]">
                    @forelse ($valuation['category_breakdown'] as $cat)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 font-bold">
                                {{ $cat['category'] }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-slate-700">
                                {{ number_format($cat['total_units']) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-[#4b55c8]">
                                ₹{{ number_format($cat['purchase_value'], 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-[#1e2746]">
                                ₹{{ number_format($cat['selling_value'], 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-emerald-600">
                                @php
                                    $profit = $cat['selling_value'] - $cat['purchase_value'];
                                    $pct = $cat['selling_value'] > 0 ? round(($profit / $cat['selling_value']) * 100, 1) : 0;
                                @endphp
                                ₹{{ number_format($profit, 2) }} ({{ $pct }}%)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                No stock inventory recorded in any category.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 30-Day Inventory Shrinkage & Loss Exposure -->
    <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm">
        <h3 class="text-base font-black text-[#1e2746] tracking-tight mb-2">30-Day Inventory Loss & Shrinkage Summary</h3>
        <p class="text-xs text-slate-500 mb-4">Total costs written off in the past 30 days due to damage, shrinkage, and expiry disposal.</p>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase">Damaged Write-Off</div>
                <div class="text-xl font-bold font-mono text-rose-600 mt-1">₹{{ number_format($valuation['loss_summary_30d']['damaged_cost'], 2) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $valuation['loss_summary_30d']['damaged_units'] }} units</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase">Lost / Missing Write-Off</div>
                <div class="text-xl font-bold font-mono text-amber-600 mt-1">₹{{ number_format($valuation['loss_summary_30d']['lost_cost'], 2) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $valuation['loss_summary_30d']['lost_units'] }} units</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase">Expired Disposal</div>
                <div class="text-xl font-bold font-mono text-rose-600 mt-1">₹{{ number_format($valuation['loss_summary_30d']['expired_cost'], 2) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $valuation['loss_summary_30d']['expired_units'] }} units</div>
            </div>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200">
                <div class="text-[11px] font-bold text-rose-600 uppercase">Total 30-Day Loss Cost</div>
                <div class="text-xl font-bold font-mono text-rose-700 mt-1">₹{{ number_format($valuation['loss_summary_30d']['total_loss_cost'], 2) }}</div>
                <div class="text-xs text-rose-600/80 mt-0.5">Written-off warehouse capital</div>
            </div>
        </div>
    </div>

</div>
@endsection
