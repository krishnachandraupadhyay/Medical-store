@extends('super-admin.layouts.app')

@section('title', 'Subscription Plans Directory')
@section('page-title', 'Subscription Plans')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Subscription Plans & Tiers</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Configure pricing tiers, store catalog capacities, and functional module access for tenant medical stores.</p>
        </div>

        <a href="{{ route('super-admin.subscriptions.plans.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] active:scale-[0.99] text-white font-semibold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/20 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Create New Plan</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Filters Group -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Status Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
                    <a href="{{ route('super-admin.subscriptions.plans.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}"
                       class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ empty($currentStatus) ? 'bg-[#eef2fd] text-[#4b55c8] font-bold' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-100' }}">
                        All Status <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-100 font-mono">{{ $counts['all'] }}</span>
                    </a>
                    <a href="{{ route('super-admin.subscriptions.plans.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                       class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $currentStatus === 'active' ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-200' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-100' }}">
                        Active <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-emerald-100/70 font-mono text-emerald-700">{{ $counts['active'] }}</span>
                    </a>
                    <a href="{{ route('super-admin.subscriptions.plans.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                       class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $currentStatus === 'inactive' ? 'bg-slate-200 text-slate-700 font-bold' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-100' }}">
                        Inactive <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-100 font-mono">{{ $counts['inactive'] }}</span>
                    </a>
                </div>

                <span class="text-slate-300 hidden md:inline">|</span>

                <!-- Billing Cycle Filter Pills -->
                <div class="flex items-center gap-1.5 text-xs">
                    <a href="{{ route('super-admin.subscriptions.plans.index', array_merge(request()->except(['cycle', 'page']), ['cycle' => 'monthly'])) }}"
                       class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $currentCycle === 'monthly' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-100' }}">
                        Monthly ({{ $counts['monthly'] }})
                    </a>
                    <a href="{{ route('super-admin.subscriptions.plans.index', array_merge(request()->except(['cycle', 'page']), ['cycle' => 'yearly'])) }}"
                       class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $currentCycle === 'yearly' ? 'bg-purple-50 text-purple-700 font-bold border border-purple-200' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-100' }}">
                        Yearly ({{ $counts['yearly'] }})
                    </a>
                </div>
            </div>

            <!-- Search Form -->
            <form action="{{ route('super-admin.subscriptions.plans.index') }}" method="GET" class="flex items-center gap-2">
                @if (!empty($currentStatus))
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                @if (!empty($currentCycle))
                    <input type="hidden" name="cycle" value="{{ $currentCycle }}">
                @endif
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search plan name, slug..."
                        class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200/80 focus:border-[#4b55c8] focus:ring-2 focus:ring-[#4b55c8]/10 rounded-xl text-[#1e2746] placeholder-slate-400 text-xs focus:outline-none transition"
                    >
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-[#4b55c8] hover:bg-[#3f49b8] text-white rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer">
                    Search
                </button>
                @if (!empty($search) || !empty($currentStatus) || !empty($currentCycle))
                    <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="p-1.5 text-slate-400 hover:text-rose-500" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Plans Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if ($plans->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 border-b border-slate-200/70">
                        <tr>
                            <th class="px-5 py-3.5">Plan Name</th>
                            <th class="px-5 py-3.5">Price & Billing</th>
                            <th class="px-5 py-3.5">Trial Days</th>
                            <th class="px-5 py-3.5">Capacity Limits</th>
                            <th class="px-5 py-3.5">Modules Active</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Created Date</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($plans as $plan)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Plan Name & Badges -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-[#eef2fd] text-[#4b55c8] border border-[#d8e0ec] flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('super-admin.subscriptions.plans.show', $plan) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] transition text-sm">
                                                    {{ $plan->name }}
                                                </a>
                                                @if ($plan->is_popular)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        Popular
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-mono block mt-0.5">{{ $plan->slug }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Price & Billing Cycle -->
                                <td class="px-5 py-4">
                                    <div class="font-extrabold text-[#1e2746] text-sm">{{ $plan->formattedPrice() }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border mt-0.5 {{ $plan->billing_cycle->badgeClasses() }}">
                                        {{ $plan->billing_cycle->label() }}
                                    </span>
                                </td>

                                <!-- Trial Period -->
                                <td class="px-5 py-4">
                                    @if ($plan->trial_days > 0)
                                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md text-xs border border-emerald-200">
                                            <span>{{ $plan->trial_days }} Days Free</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">No Trial</span>
                                    @endif
                                </td>

                                <!-- Limits Summary -->
                                <td class="px-5 py-4 font-mono text-[11px] text-slate-600">
                                    <div class="space-y-0.5">
                                        <div><span class="text-slate-400">Staff:</span> <strong class="text-[#1e2746]">{{ $plan->displayLimit('max_staff') }}</strong></div>
                                        <div><span class="text-slate-400">Meds:</span> <strong class="text-[#1e2746]">{{ $plan->displayLimit('max_medicines') }}</strong></div>
                                        <div><span class="text-slate-400">Invoices:</span> <strong class="text-[#1e2746]">{{ $plan->displayLimit('max_invoices') }}</strong></div>
                                    </div>
                                </td>

                                <!-- Modules / Features Count -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                                        <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                                        <span>{{ count($plan->features ?? []) }} of {{ count($supportedFeatures) }} Modules</span>
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $plan->status->badgeClasses() }}">
                                        {{ $plan->status->label() }}
                                    </span>
                                </td>

                                <!-- Created Date -->
                                <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                    {{ $plan->created_at->format('d M Y') }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View -->
                                        <a href="{{ route('super-admin.subscriptions.plans.show', $plan) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#4b55c8] hover:bg-[#eef2fd] transition" title="View Plan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('super-admin.subscriptions.plans.edit', $plan) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit Plan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <!-- Status Toggle Button -->
                                        <form action="{{ route('super-admin.subscriptions.plans.update-status', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to {{ $plan->isActive() ? 'DEACTIVATE' : 'ACTIVATE' }} this subscription plan?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $plan->isActive() ? 'inactive' : 'active' }}">
                                            <button type="submit" class="p-1.5 rounded-lg {{ $plan->isActive() ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition cursor-pointer" title="{{ $plan->isActive() ? 'Deactivate Plan' : 'Activate Plan' }}">
                                                @if ($plan->isActive())
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 bg-slate-50/50">
                <span>Showing {{ $plans->firstItem() ?? 0 }} to {{ $plans->lastItem() ?? 0 }} of {{ $plans->total() }} subscription plans</span>
                <div>
                    {{ $plans->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="py-16 text-center p-6 flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-[#eef2fd] border border-[#d8e0ec] flex items-center justify-center text-[#4b55c8] mb-4 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1e2746]">No subscription plans found</h3>
                <p class="text-xs text-[#64748b] max-w-sm mt-1 mb-6">
                    @if (!empty($search) || !empty($currentStatus) || !empty($currentCycle))
                        No subscription plan matches your current search or filter criteria. Try clearing your filters.
                    @else
                        No subscription plans have been defined yet. Create your first pricing tier to begin onboarding stores with SaaS subscriptions.
                    @endif
                </p>

                @if (!empty($search) || !empty($currentStatus) || !empty($currentCycle))
                    <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-[#1e2746] text-xs font-semibold transition">
                        Clear All Filters
                    </a>
                @else
                    <a href="{{ route('super-admin.subscriptions.plans.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-semibold text-xs shadow-md shadow-[#4b55c8]/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Create First Plan</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
