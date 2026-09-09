@extends('super-admin.layouts.app')

@section('title', 'Store Reports')
@section('page-title', 'Store Directory Reports')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.reports.overview') }}" class="hover:text-[#4b55c8] transition">Reports</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Store Reports</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Store Directory & Subscription Status</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Tenant store provisioning, owner assignments, and active plan overview.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.reports.stores.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs shadow-sm transition">
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

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('super-admin.reports.stores') }}" method="GET" class="space-y-3">
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
                           placeholder="Search name, code, owner, city..."
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                </div>

                <!-- Store Status -->
                <div class="lg:col-span-2">
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Store Statuses</option>
                        @foreach ($storeStatuses as $st)
                            <option value="{{ $st->value }}" {{ $status === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plan -->
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

                <!-- Subscription Status -->
                <div class="lg:col-span-2">
                    <select name="subscription_status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Subscriptions</option>
                        @foreach ($subStatuses as $subSt)
                            <option value="{{ $subSt->value }}" {{ $subStatus === $subSt->value ? 'selected' : '' }}>
                                {{ $subSt->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-3 flex items-center gap-2">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                        Filter Report
                    </button>
                    @if ($search || $status || $planId || $subStatus || $dateFrom || $dateTo)
                        <a href="{{ route('super-admin.reports.stores') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition" title="Clear Filters">
                            ✕
                        </a>
                    @endif
                </div>

            </div>

            <!-- Date Range Filter Row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Created Date:</span>
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

    <!-- Stores Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                    <tr>
                        <th class="px-4 py-3.5">Store Details</th>
                        <th class="px-4 py-3.5">Store Owner</th>
                        <th class="px-4 py-3.5">Location</th>
                        <th class="px-4 py-3.5">Store Status</th>
                        <th class="px-4 py-3.5">Current Plan</th>
                        <th class="px-4 py-3.5">Sub. Status</th>
                        <th class="px-4 py-3.5">Sub. Expiry</th>
                        <th class="px-4 py-3.5">Joined Date</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($stores as $store)
                        @php
                            $sub = $store->activeSubscription ?? $store->latestSubscription;
                            $owner = $store->owners->first();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Store Details -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <a href="{{ route('super-admin.stores.show', $store) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] block">
                                    {{ $store->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $store->code }}</span>
                            </td>

                            <!-- Store Owner -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($owner)
                                    <div class="text-slate-800 font-semibold">{{ $owner->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $owner->email }}</div>
                                @else
                                    <span class="text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Location -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">
                                {{ $store->city }}, {{ $store->state }}
                            </td>

                            <!-- Store Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $store->status->badgeClasses() }}">
                                    {{ $store->status->label() }}
                                </span>
                            </td>

                            <!-- Current Plan -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($sub?->plan)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-[#4b55c8] border border-blue-200">
                                        {{ $sub->plan->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            <!-- Subscription Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($sub)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sub->effectiveBadgeClasses() }}">
                                        {{ $sub->effectiveStatusLabel() }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">No Sub</span>
                                @endif
                            </td>

                            <!-- Subscription Expiry -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                {{ $sub ? $sub->end_date->format('M d, Y') : '—' }}
                            </td>

                            <!-- Joined Date -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                {{ $store->created_at->format('M d, Y') }}
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <a href="{{ route('super-admin.stores.show', $store) }}" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#4b55c8] font-bold text-xs transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="text-2xl">🏪</div>
                                    <div class="text-sm font-bold text-[#1e2746]">No store records found</div>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stores->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $stores->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
