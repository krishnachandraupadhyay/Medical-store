@extends('super-admin.layouts.app')

@section('title', 'Store Subscriptions Directory')
@section('page-title', 'Store Subscriptions')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Store Subscriptions</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Assign, track, and manage tier subscriptions for tenant medical stores.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                Manage Plans
            </a>
            <a href="{{ route('super-admin.subscriptions.stores.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition active:scale-[0.99] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Assign Plan to Store</span>
            </a>
        </div>
    </div>

    <!-- Metrics Summary Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Subscriptions</span>
            <span class="text-xl font-bold text-[#1e2746] mt-0.5 block">{{ number_format($counts['all']) }}</span>
        </div>

        <!-- Active -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-600 block">Active Subscriptions</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ number_format($counts['active']) }}</span>
        </div>

        <!-- Trial -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-[#4b55c8] block">In Free Trial</span>
            <span class="text-xl font-bold text-[#4b55c8] mt-0.5 block">{{ number_format($counts['trial']) }}</span>
        </div>

        <!-- Expired -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Expired</span>
            <span class="text-xl font-bold text-slate-700 mt-0.5 block">{{ number_format($counts['expired']) }}</span>
        </div>

        <!-- Cancelled -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-rose-600 block">Cancelled</span>
            <span class="text-xl font-bold text-rose-700 mt-0.5 block">{{ number_format($counts['cancelled']) }}</span>
        </div>

        <!-- Suspended -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-amber-600 block">Suspended</span>
            <span class="text-xl font-bold text-amber-700 mt-0.5 block">{{ number_format($counts['suspended']) }}</span>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none text-xs">
                <a href="{{ route('super-admin.subscriptions.stores.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ empty($status) ? 'bg-blue-50 text-[#4b55c8] border border-blue-200 shadow-sm' : 'text-slate-600 hover:text-[#1e2746] hover:bg-slate-100' }}">
                    All <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-100 font-mono">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ route('super-admin.subscriptions.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm' : 'text-slate-600 hover:text-[#1e2746] hover:bg-slate-100' }}">
                    Active <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-emerald-100/60 font-mono text-emerald-700">{{ $counts['active'] }}</span>
                </a>
                <a href="{{ route('super-admin.subscriptions.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'trial'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $status === 'trial' ? 'bg-blue-50 text-[#4b55c8] border border-blue-200 shadow-sm' : 'text-slate-600 hover:text-[#1e2746] hover:bg-slate-100' }}">
                    Trial <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-100/60 font-mono text-[#4b55c8]">{{ $counts['trial'] }}</span>
                </a>
                <a href="{{ route('super-admin.subscriptions.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'expired'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $status === 'expired' ? 'bg-slate-100 text-slate-800 border border-slate-300 shadow-sm' : 'text-slate-600 hover:text-[#1e2746] hover:bg-slate-100' }}">
                    Expired <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-200 font-mono">{{ $counts['expired'] }}</span>
                </a>
                <a href="{{ route('super-admin.subscriptions.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'cancelled'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $status === 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200 shadow-sm' : 'text-slate-600 hover:text-[#1e2746] hover:bg-slate-100' }}">
                    Cancelled <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-rose-100/60 font-mono text-rose-700">{{ $counts['cancelled'] }}</span>
                </a>
            </div>

            <!-- Filters Form -->
            <form action="{{ route('super-admin.subscriptions.stores.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                @if (!empty($status))
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <!-- Plan Filter -->
                <select
                    name="plan_id"
                    onchange="this.form.submit()"
                    class="px-3.5 py-2 bg-[#f8faff] border border-slate-200 focus:border-[#4b55c8] focus:ring-2 focus:ring-[#4b55c8]/20 focus:bg-white rounded-xl text-slate-700 text-xs focus:outline-none transition"
                >
                    <option value="">All Subscription Plans</option>
                    @foreach ($plans as $p)
                        <option value="{{ $p->id }}" {{ $planId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->formattedPrice() }}/{{ $p->billing_cycle->value }})
                        </option>
                    @endforeach
                </select>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search store, code, owner, plan..."
                        class="w-full pl-9 pr-3 py-2 bg-[#f8faff] border border-slate-200 focus:border-[#4b55c8] focus:ring-2 focus:ring-[#4b55c8]/20 focus:bg-white rounded-xl text-[#1e2746] placeholder-slate-400 text-xs focus:outline-none transition"
                    >
                </div>

                <button type="submit" class="px-4 py-2 bg-[#4b55c8] hover:bg-[#3f49b8] text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Search
                </button>

                @if (!empty($search) || !empty($planId) || !empty($status))
                    <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="p-2 text-slate-400 hover:text-rose-500 self-center transition" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>

        </div>
    </div>

    <!-- Store Subscriptions Data Table -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        @if ($subscriptions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200/70">
                        <tr>
                            <th class="px-5 py-3.5">Store / Code</th>
                            <th class="px-5 py-3.5">Assigned Owner</th>
                            <th class="px-5 py-3.5">Subscription Plan</th>
                            <th class="px-5 py-3.5">Start Date</th>
                            <th class="px-5 py-3.5">End Date</th>
                            <th class="px-5 py-3.5">Trial End</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Created Date</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($subscriptions as $sub)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Store -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-[#4b55c8] font-bold flex items-center justify-center text-xs">
                                            {{ substr($sub->store->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('super-admin.stores.show', $sub->store) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] transition block">
                                                {{ $sub->store->name }}
                                            </a>
                                            <span class="text-[10px] font-mono text-[#4b55c8] font-bold">{{ $sub->store->code }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Owner -->
                                <td class="px-5 py-4 text-slate-700">
                                    @if ($sub->store->owners->isNotEmpty())
                                        <a href="{{ route('super-admin.store-owners.show', $sub->store->owners->first()) }}" class="font-semibold text-[#1e2746] hover:text-[#4b55c8] hover:underline">
                                            {{ $sub->store->owners->first()->name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Unassigned</span>
                                    @endif
                                </td>

                                <!-- Plan -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('super-admin.subscriptions.plans.show', $sub->plan) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8]">
                                            {{ $sub->plan->name }}
                                        </a>
                                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-blue-50 text-[#4b55c8] font-semibold">
                                            {{ $sub->plan->formattedPrice() }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 capitalize block mt-0.5">{{ $sub->plan->billing_cycle->value }} cycle</span>
                                </td>

                                <!-- Start Date -->
                                <td class="px-5 py-4 font-mono text-slate-600">
                                    {{ $sub->start_date->format('d M Y') }}
                                </td>

                                <!-- End Date -->
                                <td class="px-5 py-4 font-mono">
                                    <span class="{{ $sub->isExpired() ? 'text-rose-600 font-bold' : 'text-slate-700 font-semibold' }}">
                                        {{ $sub->end_date->format('d M Y') }}
                                    </span>
                                    @if (!$sub->isExpired() && $sub->isCurrentlyActive())
                                        <span class="block text-[10px] text-emerald-600 font-sans font-semibold">{{ $sub->daysRemaining() }} days left</span>
                                    @endif
                                </td>

                                <!-- Trial End -->
                                <td class="px-5 py-4 font-mono text-slate-600">
                                    @if ($sub->trial_ends_at)
                                        <span class="text-blue-700 font-semibold">{{ $sub->trial_ends_at->format('d M Y') }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Status Badge (Effective Status dynamically calculated) -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sub->effectiveBadgeClasses() }}">
                                        {{ $sub->effectiveStatusLabel() }}
                                    </span>
                                </td>

                                <!-- Created -->
                                <td class="px-5 py-4 text-slate-400 font-mono text-[11px]">
                                    {{ $sub->created_at->format('d M Y') }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View -->
                                        <a href="{{ route('super-admin.subscriptions.stores.show', $sub) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#4b55c8] hover:bg-blue-50 transition" title="View Subscription Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('super-admin.subscriptions.stores.edit', $sub) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#4b55c8] hover:bg-blue-50 transition" title="Edit Subscription">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Cancel Action -->
                                        @if ($sub->status === \App\Enums\SubscriptionStatus::ACTIVE || $sub->status === \App\Enums\SubscriptionStatus::TRIAL)
                                            <form action="{{ route('super-admin.subscriptions.stores.update-status', $sub) }}" method="POST" onsubmit="return confirm('Are you sure you want to CANCEL this active subscription?');" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Cancel Subscription">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 bg-slate-50/50">
                <span>Showing {{ $subscriptions->firstItem() ?? 0 }} to {{ $subscriptions->lastItem() ?? 0 }} of {{ $subscriptions->total() }} store subscriptions</span>
                <div>
                    {{ $subscriptions->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="py-16 text-center p-6 flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#4b55c8] mb-4 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1e2746]">No Store Subscriptions found</h3>
                <p class="text-xs text-slate-500 max-w-sm mt-1 mb-6">
                    @if (!empty($search) || !empty($status) || !empty($planId))
                        No subscriptions match your current search or filter criteria. Try clearing your filters.
                    @else
                        No medical store subscriptions have been provisioned yet. Assign a subscription plan to a store to get started.
                    @endif
                </p>

                @if (!empty($search) || !empty($status) || !empty($planId))
                    <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Clear All Filters
                    </a>
                @else
                    <a href="{{ route('super-admin.subscriptions.stores.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs shadow-md shadow-[#4b55c8]/25 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Assign First Subscription</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
