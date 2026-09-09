@extends('super-admin.layouts.app')

@section('title', 'Payment & Revenue Reports')
@section('page-title', 'Financial Payment Reports')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.reports.overview') }}" class="hover:text-[#4b55c8] transition">Reports</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Payment & Revenue</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Payment Ledger & Revenue Breakdown</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Audit subscription billing transactions, gateway collections, and payment methods.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.reports.payments.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('super-admin.reports.overview') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Overview
            </a>
        </div>
    </div>

    <!-- Metrics Summary Bar for Filtered Results -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-[#4b55c8] block">Total Paid Revenue</span>
            <span class="text-lg sm:text-xl font-black text-[#1e2746] mt-0.5 block">₹{{ number_format($stats['total_paid_amount'], 2) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-600 block">Paid Transactions</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ number_format($stats['paid_count']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-amber-600 block">Pending Confirmations</span>
            <span class="text-xl font-bold text-amber-700 mt-0.5 block">{{ number_format($stats['pending_count']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-rose-600 block">Failed Attempts</span>
            <span class="text-xl font-bold text-rose-700 mt-0.5 block">{{ number_format($stats['failed_count']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-purple-600 block">Refunded Volume</span>
            <span class="text-lg sm:text-xl font-bold text-purple-700 mt-0.5 block">₹{{ number_format($stats['total_refunded_amount'], 2) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Records</span>
            <span class="text-xl font-bold text-slate-800 mt-0.5 block">{{ number_format($stats['total_transactions']) }}</span>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('super-admin.reports.payments') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- Search -->
                <div class="lg:col-span-3 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search TXN ID or store..."
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Statuses</option>
                        @foreach ($paymentStatuses as $st)
                            <option value="{{ $st->value }}" {{ $status === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="lg:col-span-2">
                    <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Methods</option>
                        @foreach ($paymentMethods as $m)
                            <option value="{{ $m->value }}" {{ $method === $m->value ? 'selected' : '' }}>
                                {{ $m->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plan Filter -->
                <div class="lg:col-span-2">
                    <select name="plan_id" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Plans</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" {{ (string)$planId === (string)$plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-3 flex items-center gap-2">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                        Filter
                    </button>
                    @if ($search || $status || $method || $planId || $storeId || $dateFrom || $dateTo)
                        <a href="{{ route('super-admin.reports.payments') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition" title="Clear Filters">
                            ✕
                        </a>
                    @endif
                </div>

            </div>

            <!-- Date Range Filter Row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Payment Date:</span>
                <div class="flex items-center gap-2">
                    <label for="date_from" class="text-[11px]">From:</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                </div>
                <div class="flex items-center gap-2">
                    <label for="date_to" class="text-[11px]">To:</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                </div>
            </div>
        </form>
    </div>

    <!-- Payments Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                    <tr>
                        <th class="px-4 py-3.5">Transaction ID</th>
                        <th class="px-4 py-3.5">Store</th>
                        <th class="px-4 py-3.5">Plan</th>
                        <th class="px-4 py-3.5">Amount</th>
                        <th class="px-4 py-3.5">Method</th>
                        <th class="px-4 py-3.5">Payment Date</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Transaction ID -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <a href="{{ route('super-admin.payments.show', $payment) }}" class="font-mono font-bold text-[#4b55c8] hover:underline">
                                    {{ $payment->transaction_id }}
                                </a>
                            </td>

                            <!-- Store -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($payment->store)
                                    <div class="font-bold text-[#1e2746]">{{ $payment->store->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $payment->store->code }}</div>
                                @else
                                    <span class="text-slate-400 italic">Deleted Store</span>
                                @endif
                            </td>

                            <!-- Plan -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $pName = $payment->subscriptionPlan?->name ?? $payment->subscription?->plan?->name;
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-[#4b55c8]">
                                    {{ $pName ?? 'Standard' }}
                                </span>
                            </td>

                            <!-- Amount -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 text-xs">₹{{ number_format($payment->amount, 2) }}</span>
                                <span class="text-[10px] text-slate-400 font-mono uppercase">{{ $payment->currency }}</span>
                            </td>

                            <!-- Method -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $payment->payment_method->label() }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                {{ $payment->payment_date->format('M d, Y h:i A') }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $payment->status->badgeClasses() }}">
                                    {{ $payment->status->label() }}
                                </span>
                            </td>

                            <!-- Receipt -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <a href="{{ route('super-admin.payments.show', $payment) }}" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#4b55c8] font-bold text-xs transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="text-2xl">💳</div>
                                    <div class="text-sm font-bold text-[#1e2746]">No payment records found</div>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
