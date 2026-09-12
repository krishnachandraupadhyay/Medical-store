@extends('store.layouts.app')

@section('title', 'Store Dashboard')
@section('page-title', 'Business Dashboard')

@section('content')
<div class="space-y-6 max-w-full mx-auto pb-12">

    <!-- Top Welcome Banner & Date Filter -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#1e2746] via-[#2a3663] to-[#3a4785] text-white p-6 sm:p-7 shadow-lg shadow-slate-900/10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="z-10 max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur text-white text-xs font-bold mb-2.5 border border-white/15">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Store Operational Console</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black tracking-tight">
                {{ $store->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1 font-medium leading-relaxed">
                Store Code: <span class="font-mono text-white font-bold">{{ $store->code }}</span> • {{ $store->city }}, {{ $store->state }} • Drug Lic: <span class="font-mono text-slate-200">{{ $store->drug_license_no ?? 'N/A' }}</span>
            </p>
            <div class="mt-2 flex items-center gap-2 text-xs">
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-white/15 text-white border border-white/20">
                    @if ($activeSubscription && $activeSubscription->plan)
                        {{ $activeSubscription->plan->name }}
                    @else
                        No Active Plan
                    @endif
                </span>
                <span class="text-slate-300 text-[11px]">
                    @if ($activeSubscription)
                        Valid until {{ $activeSubscription->end_date->format('d M Y') }}
                    @else
                        No subscription assigned yet
                    @endif
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3 z-10">
            @hasFeature('pos')
            @if (auth()->user()->hasPermission('sales.create'))
            <a href="{{ route('store.pos.index') }}" class="px-5 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-500/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>Open POS Terminal</span>
            </a>
            @endif
            @endhasFeature
            @hasFeature('reports')
            @if (auth()->user()->hasPermission('reports.view'))
            <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition">
                Reports
            </a>
            @endif
            @endhasFeature
        </div>
    </div>

    @if ($activeSubscription && $activeSubscription->plan)
    <!-- Subscription Quota Strip -->
    <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-[#4b55c8] text-white flex items-center justify-center font-black">
                ★
            </div>
            <div>
                <span class="font-black text-slate-900">{{ $activeSubscription->plan->name }}</span>
                <span class="text-slate-500">• ₹{{ number_format($activeSubscription->plan->price) }} / {{ $activeSubscription->plan->billing_cycle->value ?? 'cycle' }}</span>
                <span class="text-indigo-700 font-medium block text-[11px]">Valid until {{ $activeSubscription->end_date->format('d M Y') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-[11px] text-slate-600">
            <div>Medicines: <strong class="text-slate-900">{{ method_exists($activeSubscription->plan, 'displayLimit') ? $activeSubscription->plan->displayLimit('max_medicines') : 'Unlimited' }}</strong></div>
            <div>Staff: <strong class="text-slate-900">{{ method_exists($activeSubscription->plan, 'displayLimit') ? $activeSubscription->plan->displayLimit('max_staff') : 'Unlimited' }}</strong></div>
        </div>
    </div>
    @endif

    <!-- PART B: Global Date Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Preset Filter Buttons -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0">
            @php
                $presets = [
                    'today' => 'Today',
                    'yesterday' => 'Yesterday',
                    'this_week' => 'This Week',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'this_year' => 'This Year',
                ];
            @endphp
            @foreach($presets as $key => $title)
            <a href="{{ route('store.dashboard', ['period' => $key]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $range['preset'] === $key ? 'bg-[#4b55c8] text-white shadow-sm shadow-[#4b55c8]/25' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                {{ $title }}
            </a>
            @endforeach
        </div>

        <!-- Custom Date Range Form -->
        <form method="GET" action="{{ route('store.dashboard') }}" class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <input type="hidden" name="period" value="custom">
            <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1 text-xs">
                <span class="text-slate-400 font-semibold">From:</span>
                <input type="date" name="from_date" value="{{ $range['from_date'] }}" class="bg-transparent text-slate-800 font-bold outline-none">
            </div>
            <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1 text-xs">
                <span class="text-slate-400 font-semibold">To:</span>
                <input type="date" name="to_date" value="{{ $range['to_date'] }}" class="bg-transparent text-slate-800 font-bold outline-none">
            </div>
            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                Apply
            </button>
            <a href="{{ route('store.dashboard') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                Reset
            </a>
        </form>
    </div>

    <!-- Active Period Label -->
    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold px-1">
        <span>Business Performance for: <strong class="text-slate-900">{{ $range['label'] }}</strong> ({{ $range['from_date'] }} to {{ $range['to_date'] }})</span>
        <span class="text-[11px] text-slate-400 font-mono">Store Isolated Data</span>
    </div>

    <!-- PART C & D: Top Dashboard Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- 1. Total Sales Card -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm space-y-3 hover:border-[#4b55c8]/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Sales</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-black text-xs">
                    ₹
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">₹{{ number_format($summaryCards['total_sales'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $summaryCards['completed_sales_count'] }} completed invoices</p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Completed sales only</span>
                <a href="{{ route('store.sales.index') }}" class="font-bold text-[#4b55c8] hover:underline">View Sales →</a>
            </div>
        </div>

        <!-- 2. Total Purchases Card -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm space-y-3 hover:border-purple-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Purchases</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">
                    📦
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">₹{{ number_format($summaryCards['total_purchases'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $summaryCards['completed_purchases_count'] }} completed vendor orders</p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Stock procurement</span>
                <a href="{{ route('store.purchases.index') }}" class="font-bold text-purple-600 hover:underline">View Purchases →</a>
            </div>
        </div>

        <!-- 3. Returns (Sales & Purchase Returns) -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm space-y-3 hover:border-amber-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Returns Summary</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                    ↺
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Sales Returns:</span>
                    <span class="font-bold text-slate-900">₹{{ number_format($summaryCards['total_sales_returns'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Purchase Returns:</span>
                    <span class="font-bold text-slate-900">₹{{ number_format($summaryCards['total_purchase_returns'], 2) }}</span>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Completed returns</span>
                <a href="{{ route('store.sales-returns.index') }}" class="font-bold text-amber-600 hover:underline">Returns Hub →</a>
            </div>
        </div>

        <!-- 4. Operating Expenses Card -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm space-y-3 hover:border-rose-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Store Expenses</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs">
                    ⚡
                </div>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">₹{{ number_format($summaryCards['total_expenses'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Paid operating expenditures</p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Overhead costs</span>
                <a href="{{ route('store.expenses.index') }}" class="font-bold text-rose-600 hover:underline">View Expenses →</a>
            </div>
        </div>
    </div>

    <!-- Outstanding & Neutral Financial Summary Section (PART D & E) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
        
        <!-- Receivables Card (PART C #8) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Customer Receivables</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Due to Store</span>
            </div>
            <p class="text-2xl font-black text-slate-900">₹{{ number_format($summaryCards['customer_outstanding'], 2) }}</p>
            <div class="pt-1 flex items-center justify-between text-xs">
                <span class="text-slate-400">Outstanding sales invoices</span>
                <a href="{{ route('store.outstanding.customers') }}" class="font-bold text-[#4b55c8] hover:underline">View Receivables →</a>
            </div>
        </div>

        <!-- Payables Card (PART C #9) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Supplier Payables</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Payable Dues</span>
            </div>
            <p class="text-2xl font-black text-slate-900">₹{{ number_format($summaryCards['supplier_outstanding'], 2) }}</p>
            <div class="pt-1 flex items-center justify-between text-xs">
                <span class="text-slate-400">Vendor purchase bills</span>
                <a href="{{ route('store.outstanding.suppliers') }}" class="font-bold text-purple-600 hover:underline">View Payables →</a>
            </div>
        </div>

        <!-- Neutral Business Activity Difference (PART E / AJ - STRICTLY NOT NET PROFIT) -->
        <div class="bg-gradient-to-tr from-slate-900 to-slate-800 rounded-3xl p-5 text-white shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Business Activity Difference</span>
                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-white/10 text-slate-300">Reporting Metric</span>
            </div>
            <p class="text-2xl font-black {{ $summaryCards['activity_difference'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                ₹{{ number_format($summaryCards['activity_difference'], 2) }}
            </p>
            <p class="text-[10px] text-slate-400 leading-tight">
                Sales minus Purchases and Expenses. Note: This indicates operational cash difference, not accounting profit or COGS.
            </p>
        </div>
    </div>

    <!-- PART F, G, H, I: Trends & Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Trend Chart Visual (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Sales vs Purchases Trend</h2>
                    <p class="text-xs text-slate-400">Daily business comparison across the selected date range</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#4b55c8]"></span> Sales</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-purple-500"></span> Purchases</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span> Expenses</span>
                </div>
            </div>

            <!-- Canvas / HTML5 Chart or SVG Bar Renderer -->
            <div class="relative w-full h-64 overflow-x-auto">
                <div class="min-w-[600px] h-full flex items-end justify-between gap-2 pt-6 px-2 border-b border-slate-200">
                    @php
                        $maxVal = max(
                            !empty($trends['sales']) ? max($trends['sales']) : 1,
                            !empty($trends['purchases']) ? max($trends['purchases']) : 1,
                            !empty($trends['expenses']) ? max($trends['expenses']) : 1,
                            1
                        );
                    @endphp
                    @forelse($trends['labels'] as $i => $lbl)
                    @php
                        $sH = ($trends['sales'][$i] / $maxVal) * 100;
                        $pH = ($trends['purchases'][$i] / $maxVal) * 100;
                        $eH = ($trends['expenses'][$i] / $maxVal) * 100;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group h-full justify-end">
                        <div class="w-full flex items-end justify-center gap-0.5 h-48">
                            <!-- Sale Bar -->
                            <div style="height: {{ max(4, $sH) }}%;" class="w-2.5 rounded-t bg-[#4b55c8] transition-all group-hover:opacity-80 relative" title="Sales: ₹{{ $trends['sales'][$i] }}"></div>
                            <!-- Purchase Bar -->
                            <div style="height: {{ max(4, $pH) }}%;" class="w-2.5 rounded-t bg-purple-500 transition-all group-hover:opacity-80 relative" title="Purchases: ₹{{ $trends['purchases'][$i] }}"></div>
                            <!-- Expense Bar -->
                            <div style="height: {{ max(4, $eH) }}%;" class="w-2 rounded-t bg-rose-400 transition-all group-hover:opacity-80 relative" title="Expenses: ₹{{ $trends['expenses'][$i] }}"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-semibold truncate block mt-1">{{ $lbl }}</span>
                    </div>
                    @empty
                    <div class="h-full w-full flex items-center justify-center text-slate-400 text-xs">
                        No activity recorded in this period.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- PART O: Operational Payment Methods (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Payment Mode Breakdown</h2>
                <p class="text-xs text-slate-400">Operational receipts & settlements</p>
            </div>

            <div class="space-y-3">
                @forelse($paymentMethodsSummary as $pm)
                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#4b55c8]"></span>
                        <span class="text-xs font-bold uppercase text-slate-800">{{ $pm->payment_method ?: 'Cash' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-slate-900 block">₹{{ number_format($pm->total_amount, 2) }}</span>
                        <span class="text-[10px] text-slate-400">{{ $pm->count }} transaction(s)</span>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    No payment records in selected period.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- PART J: Top Selling Medicines & PART K, L, M: Stock Health -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Top Selling Medicines (6 cols) -->
        <div class="lg:col-span-6 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Top Selling Medicines</h2>
                    <p class="text-xs text-slate-400">Completed customer sales</p>
                </div>
                <a href="{{ route('store.reports.medicine-sales') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">Full Report →</a>
            </div>
            
            <div class="divide-y divide-slate-100 flex-1">
                @forelse($topMedicines as $idx => $med)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-lg bg-blue-50 text-[#4b55c8] font-black text-xs flex items-center justify-center">
                            {{ $idx + 1 }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ $med->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $med->strength ?: 'Standard' }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-black text-slate-900 text-xs sm:text-sm block">{{ $med->total_quantity }} units sold</span>
                        <span class="text-[11px] font-semibold text-emerald-600">₹{{ number_format($med->total_value, 2) }}</span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    No completed medicine sales in selected date range.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Inventory Alerts: Low Stock & Expiry (6 cols) -->
        <div class="lg:col-span-6 space-y-4">
            
            <!-- Low Stock Card (PART K & L) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>Low Stock & Reorder Alerts</span>
                        </h2>
                        <p class="text-xs text-slate-400">Medicines at or below configured reorder level</p>
                    </div>
                    <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">View Inventory →</a>
                </div>

                <div class="space-y-2">
                    @forelse($lowStock as $ls)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/60 border border-amber-200/60 text-xs">
                        <div>
                            <span class="font-bold text-slate-900 block">{{ $ls->name }}</span>
                            <span class="text-[10px] text-slate-500">Reorder Level: <b>{{ $ls->reorder_level }}</b></span>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-black">
                            Stock: {{ $ls->current_stock }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-2 text-center">All medicines currently have adequate stock levels.</p>
                    @endforelse
                </div>
            </div>

            <!-- Expiry Summary Card (PART M) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Expiry Health Monitor</span>
                        </h2>
                        <p class="text-xs text-slate-400">Near-expiry batches (next 90 days)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                            {{ $expirySummary['expired_count'] }} Expired
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            {{ $expirySummary['expiring_soon_count'] }} Soon
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    @forelse($expirySummary['expiring_batches'] as $eb)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                        <div>
                            <span class="font-bold text-slate-800 block">{{ $eb->medicine?->name }}</span>
                            <span class="text-[10px] font-mono text-slate-400">Batch: {{ $eb->batch_number }}</span>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $eb->isExpired() ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $eb->expiry_date->format('d M Y') }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Qty: {{ $eb->quantity }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-2 text-center">No batches approaching expiry.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- PART N: Top Outstanding Dues & PART P: Combined Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Top Outstanding Parties (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Top Outstanding Dues</h2>
                    <p class="text-xs text-slate-400">Customer receivables & supplier payables</p>
                </div>
            </div>

            <!-- Customer Outstanding Mini-list -->
            <div class="space-y-2">
                <span class="text-[11px] font-bold uppercase text-slate-400 block">Top Customers (Receivables)</span>
                @forelse($topCustomersOutstanding as $co)
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 text-xs border border-slate-100">
                    <span class="font-bold text-slate-800">{{ $co->name }}</span>
                    <span class="font-black text-rose-600">₹{{ number_format($co->outstanding, 2) }}</span>
                </div>
                @empty
                <p class="text-[11px] text-slate-400 py-1">No outstanding customer receivables.</p>
                @endforelse
            </div>

            <!-- Supplier Outstanding Mini-list -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <span class="text-[11px] font-bold uppercase text-slate-400 block">Top Suppliers (Payables)</span>
                @forelse($topSuppliersOutstanding as $so)
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 text-xs border border-slate-100">
                    <span class="font-bold text-slate-800">{{ $so->name }}</span>
                    <span class="font-black text-purple-600">₹{{ number_format($so->outstanding, 2) }}</span>
                </div>
                @empty
                <p class="text-[11px] text-slate-400 py-1">No outstanding supplier payables.</p>
                @endforelse
            </div>
        </div>

        <!-- Combined Recent Transactions (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Recent Operational Transactions</h2>
                    <p class="text-xs text-slate-400">Latest sales, purchases, and operating disbursements</p>
                </div>
                <a href="{{ route('store.payments.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">All Transactions →</a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Reference</th>
                            <th class="py-3 px-4">Party / Detail</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $tx['type_badge'] }}">
                                    {{ $tx['type'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-800">
                                <a href="{{ $tx['url'] }}" class="hover:underline text-[#4b55c8]">
                                    {{ $tx['reference'] }}
                                </a>
                            </td>
                            <td class="py-2.5 px-4 truncate max-w-[140px]">{{ $tx['entity'] }}</td>
                            <td class="py-2.5 px-4 font-black text-slate-900">₹{{ number_format($tx['amount'], 2) }}</td>
                            <td class="py-2.5 px-4 text-right text-slate-500">{{ $tx['date'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No recent transactions recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Platform Announcements -->
    @if(isset($notifications) && $notifications->count() > 0)
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                <span>System Announcements & Notifications</span>
            </h2>
            <span class="text-[10px] font-bold text-[#4b55c8] bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                {{ $notifications->count() }} Notice(s)
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($notifications as $notif)
            <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-200/60 text-xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-900 truncate">{{ $notif->title }}</span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">{{ $notif->message }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Subscription Billing & Platform Account History -->
    @if(isset($recentPayments) && $recentPayments->count() > 0)
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Subscription Invoices & Payments</h2>
                <p class="text-xs text-slate-400">Platform billing statements and renewal receipts</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">Platform Account</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Transaction ID</th>
                        <th class="py-3 px-4">Plan</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($recentPayments as $payment)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-4 font-mono font-bold text-[#4b55c8]">
                            {{ $payment->transaction_id ?? 'TXN-'.$payment->id }}
                        </td>
                        <td class="py-2.5 px-4 font-bold text-slate-800">
                            {{ $payment->subscriptionPlan->name ?? ($payment->plan->name ?? 'Subscription') }}
                        </td>
                        <td class="py-2.5 px-4 font-black text-slate-900">
                            ₹{{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="py-2.5 px-4 uppercase font-semibold text-slate-500">
                            {{ $payment->payment_method->value ?? 'UPI' }}
                        </td>
                        <td class="py-2.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                {{ $payment->status->value ?? 'PAID' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-4 text-right text-slate-500">
                            {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
