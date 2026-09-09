@extends('super-admin.layouts.app')

@section('title', 'Subscription Reports')
@section('page-title', 'Subscription Reports & Lifecycle')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.reports.overview') }}" class="hover:text-[#4b55c8] transition">Reports</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Subscription Reports</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Subscription Performance & Distribution</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Tenant subscription lifecycle, tier conversions, and status audits.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.reports.subscriptions.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs shadow-sm transition">
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

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Assigned</span>
            <span class="text-xl font-bold text-[#1e2746] mt-0.5 block">{{ number_format($counts['total']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-600 block">Active</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ number_format($counts['active']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-[#4b55c8] block">In Free Trial</span>
            <span class="text-xl font-bold text-[#4b55c8] mt-0.5 block">{{ number_format($counts['trial']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Expired</span>
            <span class="text-xl font-bold text-slate-700 mt-0.5 block">{{ number_format($counts['expired']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-rose-600 block">Cancelled</span>
            <span class="text-xl font-bold text-rose-700 mt-0.5 block">{{ number_format($counts['cancelled']) }}</span>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('super-admin.reports.subscriptions') }}" method="GET" class="space-y-3">
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
                           placeholder="Search store, owner, plan..."
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-3">
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Subscription Statuses</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ $status === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plan Filter -->
                <div class="lg:col-span-3">
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
                    @if ($search || $status || $planId || $dateFrom || $dateTo)
                        <a href="{{ route('super-admin.reports.subscriptions') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition" title="Clear Filters">
                            ✕
                        </a>
                    @endif
                </div>

            </div>

            <!-- Date Range Filter Row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Start Date Range:</span>
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

    <!-- Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                    <tr>
                        <th class="px-4 py-3.5">Store Details</th>
                        <th class="px-4 py-3.5">Owner</th>
                        <th class="px-4 py-3.5">Plan Details</th>
                        <th class="px-4 py-3.5">Tier Price</th>
                        <th class="px-4 py-3.5">Start Date</th>
                        <th class="px-4 py-3.5">End Date</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Assigned By</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($subscriptions as $subscription)
                        @php
                            $owner = $subscription->store?->owners->first();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Store Details -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($subscription->store)
                                    <a href="{{ route('super-admin.subscriptions.stores.show', $subscription) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] block">
                                        {{ $subscription->store->name }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $subscription->store->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">Store Deleted</span>
                                @endif
                            </td>

                            <!-- Owner -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                {{ $owner?->name ?? '—' }}
                            </td>

                            <!-- Plan Details -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-[#4b55c8] border border-blue-200">
                                    {{ $subscription->plan?->name ?? 'Standard' }}
                                </span>
                            </td>

                            <!-- Tier Price -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-800">
                                ₹{{ number_format((float) ($subscription->plan?->price ?? 0), 2) }}
                                <span class="text-[10px] text-slate-400 font-normal">/ {{ $subscription->plan?->billing_cycle?->label() ?? 'Monthly' }}</span>
                            </td>

                            <!-- Start Date -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                {{ $subscription->start_date->format('M d, Y') }}
                            </td>

                            <!-- End Date -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                {{ $subscription->end_date->format('M d, Y') }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $subscription->effectiveBadgeClasses() }}">
                                    {{ $subscription->effectiveStatusLabel() }}
                                </span>
                            </td>

                            <!-- Assigned By -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                                {{ $subscription->creator?->name ?? 'Super Admin' }}
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <a href="{{ route('super-admin.subscriptions.stores.show', $subscription) }}" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#4b55c8] font-bold text-xs transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="text-2xl">⭐</div>
                                    <div class="text-sm font-bold text-[#1e2746]">No subscription records found</div>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subscriptions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
