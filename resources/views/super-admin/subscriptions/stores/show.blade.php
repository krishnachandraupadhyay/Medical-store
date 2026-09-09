@extends('super-admin.layouts.app')

@section('title', 'Subscription Details - ' . $subscription->store->name)
@section('page-title', 'Subscription Details')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4b55c8] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Store Subscriptions</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.subscriptions.stores.edit', $subscription) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#4b55c8] border border-blue-200 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Subscription</span>
            </a>
        </div>
    </div>

    <!-- Hero Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-7 shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] text-white font-extrabold flex items-center justify-center text-xl shadow-md shadow-[#4b55c8]/25">
                    {{ substr($subscription->store->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">{{ $subscription->store->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $subscription->effectiveBadgeClasses() }}">
                            {{ $subscription->effectiveStatusLabel() }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 font-mono">
                        <span class="text-[#4b55c8] font-bold">{{ $subscription->store->code }}</span>
                        <span>•</span>
                        <span class="font-bold text-[#1e2746]">{{ $subscription->plan->name }} ({{ $subscription->plan->formattedPrice() }}/{{ $subscription->plan->billing_cycle->value }})</span>
                        <span>•</span>
                        <span>Valid: {{ $subscription->start_date->format('d M Y') }} – {{ $subscription->end_date->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Transition Buttons -->
            <div class="flex items-center gap-2 pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                @if ($subscription->status !== \App\Enums\SubscriptionStatus::ACTIVE)
                    <form action="{{ route('super-admin.subscriptions.stores.update-status', $subscription) }}" method="POST" onsubmit="return confirm('Activate this store subscription?');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Activate</span>
                        </button>
                    </form>
                @endif

                @if ($subscription->status !== \App\Enums\SubscriptionStatus::SUSPENDED)
                    <form action="{{ route('super-admin.subscriptions.stores.update-status', $subscription) }}" method="POST" onsubmit="return confirm('Suspend this subscription?');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="suspended">
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Suspend</span>
                        </button>
                    </form>
                @endif

                @if ($subscription->status !== \App\Enums\SubscriptionStatus::CANCELLED)
                    <form action="{{ route('super-admin.subscriptions.stores.update-status', $subscription) }}" method="POST" onsubmit="return confirm('Cancel this subscription?');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Cancel</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: Store & Owner Overview -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Medical Store Details
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Store Name</span>
                    <a href="{{ route('super-admin.stores.show', $subscription->store) }}" class="text-[#4b55c8] font-bold hover:underline">
                        {{ $subscription->store->name }}
                    </a>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Store Code</span>
                    <span class="font-mono font-bold text-[#4b55c8]">{{ $subscription->store->code }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Store Owner</span>
                    @if ($subscription->store->owners->isNotEmpty())
                        <a href="{{ route('super-admin.store-owners.show', $subscription->store->owners->first()) }}" class="font-semibold text-[#1e2746] hover:text-[#4b55c8] hover:underline">
                            {{ $subscription->store->owners->first()->name }}
                        </a>
                    @else
                        <span class="text-slate-400 italic">Unassigned</span>
                    @endif
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Contact Number</span>
                    <span class="font-mono text-[#1e2746] font-semibold">{{ $subscription->store->mobile }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Store Classification</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">{{ $subscription->store->store_type }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Validity & Duration -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Subscription Duration & Status
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Plan Tier</span>
                    <a href="{{ route('super-admin.subscriptions.plans.show', $subscription->plan) }}" class="text-[#4b55c8] font-bold hover:underline">
                        {{ $subscription->plan->name }} ({{ $subscription->plan->formattedPrice() }})
                    </a>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Start Date</span>
                    <span class="font-mono font-semibold text-[#1e2746]">{{ $subscription->start_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">End / Expiry Date</span>
                    <div class="text-right">
                        <span class="font-mono font-bold {{ $subscription->isExpired() ? 'text-rose-600' : 'text-emerald-700' }}">
                            {{ $subscription->end_date->format('d M Y') }}
                        </span>
                        @if (!$subscription->isExpired() && $subscription->isCurrentlyActive())
                            <span class="block text-[10px] text-emerald-600 font-semibold">{{ $subscription->daysRemaining() }} days remaining</span>
                        @elseif ($subscription->isExpired())
                            <span class="block text-[10px] text-rose-500 font-semibold">Expired</span>
                        @endif
                    </div>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Trial Period End</span>
                    <span class="font-mono text-slate-700">
                        {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('d M Y') : 'None (Regular Activation)' }}
                    </span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Assigned By</span>
                    <span class="text-slate-700 font-semibold">{{ $subscription->creator->name ?? 'Super Admin' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Inherited Plan Limits & Capabilities -->
        <div class="sm:col-span-2 bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Inherited Plan Capabilities & Resource Limits
            </h3>

            <!-- Resource Limits Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                @foreach (\App\Models\SubscriptionPlan::supportedLimits() as $key => $def)
                    <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-semibold">{{ $def['label'] }}</span>
                        <span class="text-lg font-bold text-[#1e2746] mt-0.5 block font-mono">
                            {{ $subscription->plan->displayLimit($key) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Features Modules Checklist -->
            <div class="pt-3">
                <span class="text-xs font-semibold text-slate-700 block mb-2.5">Enabled Functional Modules:</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    @foreach (\App\Models\SubscriptionPlan::supportedFeatures() as $key => $feature)
                        @php $isEnabled = $subscription->plan->hasFeature($key); @endphp
                        <div class="p-2.5 rounded-xl border flex items-center gap-2 {{ $isEnabled ? 'bg-blue-50/60 border-blue-200 text-[#1e2746]' : 'bg-slate-50 border-slate-200/60 text-slate-400 opacity-60' }}">
                            @if ($isEnabled)
                                <svg class="w-4 h-4 text-[#4b55c8] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                            <span class="font-medium text-[11px] truncate">{{ $feature['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($subscription->notes)
                <div class="pt-3 border-t border-slate-100">
                    <span class="text-xs font-semibold text-slate-500 block mb-1">Administrative Notes:</span>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">{{ $subscription->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Card 4: Historical Subscriptions for this Store -->
        <div class="sm:col-span-2 bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Store Subscription History ({{ $subscription->store->name }})
                </h3>
                <span class="text-[11px] text-slate-400">{{ count($history) + 1 }} total records</span>
            </div>

            @if ($history->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200/70">
                            <tr>
                                <th class="px-4 py-2.5">Plan Tier</th>
                                <th class="px-4 py-2.5">Start Date</th>
                                <th class="px-4 py-2.5">End Date</th>
                                <th class="px-4 py-2.5">Status</th>
                                <th class="px-4 py-2.5">Assigned On</th>
                                <th class="px-4 py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($history as $h)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 font-semibold text-[#1e2746]">
                                        {{ $h->plan->name }} ({{ $h->plan->formattedPrice() }})
                                    </td>
                                    <td class="px-4 py-3 font-mono">{{ $h->start_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $h->end_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $h->effectiveBadgeClasses() }}">
                                            {{ $h->effectiveStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400 font-mono text-[11px]">{{ $h->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('super-admin.subscriptions.stores.show', $h) }}" class="text-[#4b55c8] hover:underline font-bold text-xs">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-6 text-center text-xs text-slate-400">
                    This is the only subscription record for this medical store.
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
