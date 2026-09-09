@extends('super-admin.layouts.app')

@section('title', 'Payment Management')
@section('page-title', 'Payments')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Payment Management</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Track, record, and monitor subscription billing transactions across all tenant stores.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                Subscriptions Directory
            </a>
            <a href="{{ route('super-admin.payments.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition active:scale-[0.99] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Record Payment</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Metrics Summary Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Revenue -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-[#4b55c8] block">Total Revenue (Paid)</span>
            <span class="text-lg sm:text-xl font-black text-[#1e2746] mt-0.5 block">₹{{ number_format($stats['total_revenue'], 2) }}</span>
        </div>

        <!-- Paid Transactions -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-600 block">Paid Payments</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ number_format($stats['paid_count']) }}</span>
        </div>

        <!-- Pending Transactions -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-amber-600 block">Pending Confirmation</span>
            <span class="text-xl font-bold text-amber-700 mt-0.5 block">{{ number_format($stats['pending_count']) }}</span>
        </div>

        <!-- Failed Transactions -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-rose-600 block">Failed Payments</span>
            <span class="text-xl font-bold text-rose-700 mt-0.5 block">{{ number_format($stats['failed_count']) }}</span>
        </div>

        <!-- Refunded Amount -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-purple-600 block">Refunded Volume</span>
            <span class="text-lg sm:text-xl font-bold text-purple-700 mt-0.5 block">₹{{ number_format($stats['refunded_amount'], 2) }}</span>
        </div>

        <!-- Total Records -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">All Transactions</span>
            <span class="text-xl font-bold text-slate-800 mt-0.5 block">{{ number_format($stats['total_transactions']) }}</span>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('super-admin.payments.index') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- Search -->
                <div class="lg:col-span-4 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search TXN ID, store, owner, email..."
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent transition" />
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Statuses</option>
                        @foreach ($paymentStatuses as $statusCase)
                            <option value="{{ $statusCase->value }}" {{ $status === $statusCase->value ? 'selected' : '' }}>
                                {{ $statusCase->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="lg:col-span-2">
                    <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Methods</option>
                        @foreach ($paymentMethods as $methodCase)
                            <option value="{{ $methodCase->value }}" {{ $method === $methodCase->value ? 'selected' : '' }}>
                                {{ $methodCase->label() }}
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
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                        Filter
                    </button>
                    @if ($search || $status || $method || $planId || $dateFrom || $dateTo)
                        <a href="{{ route('super-admin.payments.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition" title="Clear Filters">
                            ✕
                        </a>
                    @endif
                </div>

            </div>

            <!-- Date Range Filter Row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Payment Date Range:</span>
                <div class="flex items-center gap-2">
                    <label for="date_from" class="text-[11px]">From:</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                </div>
                <div class="flex items-center gap-2">
                    <label for="date_to" class="text-[11px]">To:</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                </div>
                @if ($dateFrom || $dateTo)
                    <span class="text-[11px] text-[#4b55c8] font-medium">Filtering by custom dates</span>
                @endif
            </div>
        </form>
    </div>

    <!-- Payments Data Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                    <tr>
                        <th class="px-4 py-3.5">Transaction ID</th>
                        <th class="px-4 py-3.5">Store Details</th>
                        <th class="px-4 py-3.5">Store Owner</th>
                        <th class="px-4 py-3.5">Plan</th>
                        <th class="px-4 py-3.5">Amount</th>
                        <th class="px-4 py-3.5">Method</th>
                        <th class="px-4 py-3.5">Payment Date</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Transaction ID -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <a href="{{ route('super-admin.payments.show', $payment) }}" class="font-mono font-bold text-[#4b55c8] hover:underline flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>{{ $payment->transaction_id }}</span>
                                </a>
                            </td>

                            <!-- Store Details -->
                            <td class="px-4 py-3.5">
                                @if ($payment->store)
                                    <a href="{{ route('super-admin.stores.show', $payment->store) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] block">
                                        {{ $payment->store->name }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $payment->store->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">Store Deleted</span>
                                @endif
                            </td>

                            <!-- Store Owner -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                @if ($payment->store && $payment->store->owners->isNotEmpty())
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                            {{ substr($payment->store->owners->first()->name, 0, 1) }}
                                        </div>
                                        <span class="text-xs">{{ $payment->store->owners->first()->name }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Plan -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $planName = $payment->subscriptionPlan?->name ?? $payment->subscription?->plan?->name;
                                @endphp
                                @if ($planName)
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-[#4b55c8] border border-blue-200">
                                        {{ $planName }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">Standard</span>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-[#1e2746] text-sm">
                                    ₹{{ number_format($payment->amount, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 block font-mono uppercase">{{ $payment->currency }}</span>
                            </td>

                            <!-- Payment Method -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $payment->payment_method->label() }}
                                </span>
                            </td>

                            <!-- Payment Date -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                <div>{{ $payment->payment_date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $payment->payment_date->format('h:i A') }}</div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $payment->status->badgeClasses() }}">
                                    {{ $payment->status->label() }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('super-admin.payments.show', $payment) }}" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#4b55c8] transition" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('super-admin.payments.edit', $payment) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Edit Payment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#4b55c8] flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-bold text-[#1e2746]">No payment transactions found</div>
                                    <p class="text-xs text-slate-400 max-w-sm">No subscription payments match your selected search or filter criteria.</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        @if ($search || $status || $method || $planId || $dateFrom || $dateTo)
                                            <a href="{{ route('super-admin.payments.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                                Clear All Filters
                                            </a>
                                        @endif
                                        <a href="{{ route('super-admin.payments.create') }}" class="px-4 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white text-xs font-bold transition">
                                            Record Payment Now
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
