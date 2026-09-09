@extends('super-admin.layouts.app')

@section('title', 'Reports & System Analytics')
@section('page-title', 'Reports Overview')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Sub-nav -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">System Reports & Analytics</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Platform-wide statistics, tenant growth, subscription lifecycle, and revenue analytics.</p>
        </div>

        <!-- Quick Links to Specialized Reports -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('super-admin.reports.stores') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#4b55c8] text-[#1e2746] font-semibold text-xs transition shadow-sm">
                Store Reports
            </a>
            <a href="{{ route('super-admin.reports.subscriptions') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#4b55c8] text-[#1e2746] font-semibold text-xs transition shadow-sm">
                Subscription Reports
            </a>
            <a href="{{ route('super-admin.reports.payments') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#4b55c8] text-[#1e2746] font-semibold text-xs transition shadow-sm">
                Payment Reports
            </a>
            <a href="{{ route('super-admin.reports.expiring-subscriptions') }}" class="px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 text-amber-800 font-bold text-xs transition shadow-sm">
                Expiring ({{ $expiringSubscriptions->count() }})
            </a>
        </div>
    </div>

    <!-- Date Range Filter Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-3">
        <form action="{{ route('super-admin.reports.overview') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Quick Presets -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none text-xs font-semibold">
                <span class="text-slate-400 mr-1 text-[11px] font-bold uppercase tracking-wider">Period:</span>
                @php
                    $presets = [
                        'today' => 'Today',
                        'last_7_days' => 'Last 7 Days',
                        'last_30_days' => 'Last 30 Days',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                        'all_time' => 'All Time',
                    ];
                @endphp
                @foreach ($presets as $key => $label)
                    <a href="{{ route('super-admin.reports.overview', ['date_range' => $key]) }}"
                       class="px-3 py-1.5 rounded-xl transition whitespace-nowrap {{ $preset === $key ? 'bg-blue-50 text-[#4b55c8] border border-blue-200 shadow-sm' : 'text-slate-600 hover:text-[#1e2746] hover:bg-slate-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Custom Date Inputs -->
            <div class="flex items-center gap-2 text-xs">
                <input type="hidden" name="date_range" value="custom">
                <input type="date" name="date_from" value="{{ $from?->format('Y-m-d') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                <span class="text-slate-400">to</span>
                <input type="date" name="date_to" value="{{ $to?->format('Y-m-d') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                    Apply
                </button>
                @if ($preset !== 'this_month')
                    <a href="{{ route('super-admin.reports.overview') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition" title="Reset to Current Month">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- 4 Main Platform KPI Pillar Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- 1. Stores Pillar -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Medical Stores</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold">
                        🏪
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-2">
                    {{ number_format($totalStores) }}
                </div>
                <span class="text-[11px] font-semibold text-[#4b55c8] block mt-0.5">
                    +{{ number_format($newStoresCount) }} registered in period
                </span>
            </div>

            <div class="grid grid-cols-3 gap-1.5 pt-3 border-t border-slate-100 text-center">
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Active</span>
                    <span class="text-xs font-bold text-emerald-600">{{ number_format($activeStores) }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Inactive</span>
                    <span class="text-xs font-bold text-slate-600">{{ number_format($inactiveStores) }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Suspended</span>
                    <span class="text-xs font-bold text-amber-600">{{ number_format($suspendedStores) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Store Owners Pillar -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Store Owners</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold">
                        👤
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-2">
                    {{ number_format($totalOwners) }}
                </div>
                <span class="text-[11px] font-semibold text-slate-500 block mt-0.5">
                    Platform Administrator Users
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-center">
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Active Accounts</span>
                    <span class="text-xs font-bold text-emerald-600">{{ number_format($activeOwners) }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Inactive Accounts</span>
                    <span class="text-xs font-bold text-rose-600">{{ number_format($inactiveOwners) }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Subscriptions Pillar -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Active Subscriptions</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold">
                        ⭐
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-2">
                    {{ number_format($activeSubs) }}
                </div>
                <span class="text-[11px] font-semibold text-[#4b55c8] block mt-0.5">
                    {{ number_format($trialSubs) }} in Free Trial
                </span>
            </div>

            <div class="grid grid-cols-3 gap-1.5 pt-3 border-t border-slate-100 text-center">
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Trial</span>
                    <span class="text-xs font-bold text-[#4b55c8]">{{ number_format($trialSubs) }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Expired</span>
                    <span class="text-xs font-bold text-slate-600">{{ number_format($expiredSubs) }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Cancelled</span>
                    <span class="text-xs font-bold text-rose-600">{{ number_format($cancelledSubs) }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Revenue Pillar -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Paid Revenue (Period)</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        ₹
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-2">
                    ₹{{ number_format($totalRevenue, 2) }}
                </div>
                <span class="text-[11px] font-semibold text-emerald-600 block mt-0.5">
                    Successful transactions volume
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-center">
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Pending Volume</span>
                    <span class="text-xs font-bold text-amber-600">₹{{ number_format($pendingAmount, 2) }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <span class="text-[10px] text-slate-400 block font-medium">Refunded Volume</span>
                    <span class="text-xs font-bold text-purple-600">₹{{ number_format($refundedAmount, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Middle Row: Charts & Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Store Growth Line Chart (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Store Growth & Tenant Acquisition</h3>
                        <p class="text-xs text-slate-400">Cumulative active stores and new registrations over time.</p>
                    </div>

                    <!-- Period Toggles -->
                    <div class="inline-flex rounded-xl bg-slate-100 p-0.5 text-[11px] font-semibold">
                        <a href="{{ route('super-admin.reports.overview', array_merge(request()->query(), ['chart_period' => '7_days'])) }}"
                           class="px-2.5 py-1 rounded-lg {{ $chartPeriod === '7_days' ? 'bg-white text-[#4b55c8] shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">7D</a>
                        <a href="{{ route('super-admin.reports.overview', array_merge(request()->query(), ['chart_period' => '30_days'])) }}"
                           class="px-2.5 py-1 rounded-lg {{ $chartPeriod === '30_days' || empty($chartPeriod) ? 'bg-white text-[#4b55c8] shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">30D</a>
                        <a href="{{ route('super-admin.reports.overview', array_merge(request()->query(), ['chart_period' => '6_months'])) }}"
                           class="px-2.5 py-1 rounded-lg {{ $chartPeriod === '6_months' ? 'bg-white text-[#4b55c8] shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">6M</a>
                        <a href="{{ route('super-admin.reports.overview', array_merge(request()->query(), ['chart_period' => '1_year'])) }}"
                           class="px-2.5 py-1 rounded-lg {{ $chartPeriod === '1_year' ? 'bg-white text-[#4b55c8] shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">1Y</a>
                    </div>
                </div>

                <div class="relative h-60 w-full mt-2">
                    <canvas id="storeGrowthChart"></canvas>
                </div>
            </div>

            <div class="flex items-center justify-center gap-6 pt-3 border-t border-slate-100 text-xs text-[#64748b] font-medium">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#4b55c8]"></span>
                    <span>Cumulative Active Stores</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#93c5fd]"></span>
                    <span>New Registrations</span>
                </div>
            </div>
        </div>

        <!-- Right: Subscription Plan Distribution Donut Chart (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Subscription Distribution</h3>
                    <a href="{{ route('super-admin.reports.subscriptions') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">Full Details &rarr;</a>
                </div>

                <div class="relative h-48 w-full flex items-center justify-center my-2">
                    <canvas id="planDonutChart"></canvas>
                </div>
            </div>

            <!-- Distribution Legend Breakdown -->
            <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                @forelse ($plansDistribution as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#4b55c8]"></span>
                            <span class="text-slate-700 font-semibold">{{ $item['name'] }}</span>
                        </div>
                        <span class="font-bold text-[#1e2746]">{{ $item['count'] }} stores</span>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 text-center py-2">No active plans assigned yet.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Bottom Row: Revenue Performance Ledger & Expiring Subscriptions Widget -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Revenue Historical Breakdown (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Revenue Performance</h3>
                    <p class="text-xs text-slate-400">Monthly gross billing breakdown (completed payments only).</p>
                </div>
                <a href="{{ route('super-admin.reports.payments') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">View Payment Ledger &rarr;</a>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">This Month</span>
                    <span class="text-lg font-bold text-[#1e2746] mt-0.5 block">₹{{ number_format($thisMonthRevenue, 2) }}</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">Last Month</span>
                    <span class="text-lg font-bold text-[#1e2746] mt-0.5 block">₹{{ number_format($lastMonthRevenue, 2) }}</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">This Year</span>
                    <span class="text-lg font-bold text-[#1e2746] mt-0.5 block">₹{{ number_format($thisYearRevenue, 2) }}</span>
                </div>
            </div>

            <div class="relative h-48 w-full mt-2">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <!-- Right: Urgent Expiring Subscriptions (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Approaching Expiration</h3>
                        <p class="text-xs text-slate-400">Subscriptions expiring within the next 14 days.</p>
                    </div>
                    <a href="{{ route('super-admin.reports.expiring-subscriptions') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">View All &rarr;</a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($expiringSubscriptions as $sub)
                        <div class="p-3 rounded-2xl bg-amber-50/50 border border-amber-200/70 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <a href="{{ route('super-admin.subscriptions.stores.show', $sub) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] block">
                                    {{ $sub->store->name }}
                                </a>
                                <span class="text-[10px] text-slate-500">{{ $sub->plan?->name ?? 'Plan' }} • Expires {{ $sub->end_date->format('M d, Y') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    {{ $sub->daysRemaining() }} days left
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <div class="text-2xl mb-1">🎉</div>
                            <span>No active subscriptions expiring in the next 14 days.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('super-admin.reports.expiring-subscriptions') }}" class="text-xs font-bold text-[#4b55c8] hover:underline">
                    Manage Expiring Subscriptions List &rarr;
                </a>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Store Growth Chart
        const growthCtx = document.getElementById('storeGrowthChart')?.getContext('2d');
        if (growthCtx) {
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: @json($growthLabels),
                    datasets: [
                        {
                            label: 'Active Stores',
                            data: @json($growthActiveStores),
                            borderColor: '#4b55c8',
                            backgroundColor: 'rgba(75, 85, 200, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'New Registrations',
                            data: @json($growthNewStores),
                            borderColor: '#93c5fd',
                            backgroundColor: 'rgba(147, 197, 253, 0.2)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 1,
                            pointHoverRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 }, precision: 0 }, min: 0 }
                    }
                }
            });
        }

        // 2. Plan Donut Chart
        const donutCtx = document.getElementById('planDonutChart')?.getContext('2d');
        if (donutCtx) {
            const planLabels = @json($plansDistribution->pluck('name'));
            const planCounts = @json($plansDistribution->pluck('count'));
            
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: planLabels.length ? planLabels : ['No Active Subscriptions'],
                    datasets: [{
                        data: planCounts.length && planCounts.some(c => c > 0) ? planCounts : [1],
                        backgroundColor: ['#1e40af', '#2563eb', '#60a5fa', '#93c5fd', '#c7d2fe', '#e0e7ff'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 3. Monthly Revenue Bar/Line Chart
        const revCtx = document.getElementById('monthlyRevenueChart')?.getContext('2d');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'bar',
                data: {
                    labels: @json($revenueLabels),
                    datasets: [{
                        label: 'Paid Revenue (₹)',
                        data: @json($revenueValues),
                        backgroundColor: '#4b55c8',
                        borderRadius: 6,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 } }, min: 0 }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
