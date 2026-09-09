@extends('super-admin.layouts.app')

@section('title', 'Expiring Subscriptions Report')
@section('page-title', 'Expiring Subscriptions')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.reports.overview') }}" class="hover:text-[#4b55c8] transition">Reports</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Expiring Subscriptions</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Expiring Subscriptions Audit</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Tenant stores approaching renewal deadline in the upcoming timeframe.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.subscriptions.stores.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs shadow-md shadow-[#4b55c8]/25 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Assign / Renew Plan</span>
            </a>
            <a href="{{ route('super-admin.reports.overview') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Overview
            </a>
        </div>
    </div>

    <!-- Filter Bar: Days Selector & Search -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('super-admin.reports.expiring-subscriptions') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            
            <!-- Range Selection Tabs -->
            <div class="flex items-center gap-2 text-xs font-semibold">
                <span class="text-slate-400 mr-1 text-[11px] font-bold uppercase tracking-wider">Expiry Range:</span>
                
                <a href="{{ route('super-admin.reports.expiring-subscriptions', ['days' => 7, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl transition {{ $days === 7 ? 'bg-amber-50 text-amber-800 border border-amber-300 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                    Expiring in 7 Days <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 font-mono">{{ $counts['7_days'] }}</span>
                </a>

                <a href="{{ route('super-admin.reports.expiring-subscriptions', ['days' => 15, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl transition {{ $days === 15 ? 'bg-amber-50 text-amber-800 border border-amber-300 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                    Expiring in 15 Days <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 font-mono">{{ $counts['15_days'] }}</span>
                </a>

                <a href="{{ route('super-admin.reports.expiring-subscriptions', ['days' => 30, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl transition {{ $days === 30 ? 'bg-blue-50 text-[#4b55c8] border border-blue-200 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100' }}">
                    Expiring in 30 Days <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 font-mono text-[#4b55c8]">{{ $counts['30_days'] }}</span>
                </a>
            </div>

            <!-- Search input -->
            <div class="flex items-center gap-2">
                <input type="hidden" name="days" value="{{ $days }}">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search store name or code..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                    Search
                </button>
            </div>

        </form>
    </div>

    <!-- Expiring Subscriptions Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                    <tr>
                        <th class="px-4 py-3.5">Store Details</th>
                        <th class="px-4 py-3.5">Owner Contact</th>
                        <th class="px-4 py-3.5">Current Plan</th>
                        <th class="px-4 py-3.5">Subscription Expiry</th>
                        <th class="px-4 py-3.5">Remaining Days</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($expiringSubscriptions as $subscription)
                        @php
                            $owner = $subscription->store?->owners->first();
                            $daysLeft = $subscription->daysRemaining();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Store Details -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($subscription->store)
                                    <a href="{{ route('super-admin.stores.show', $subscription->store) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] block">
                                        {{ $subscription->store->name }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $subscription->store->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">Store Deleted</span>
                                @endif
                            </td>

                            <!-- Owner Contact -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($owner)
                                    <div class="text-slate-800 font-semibold">{{ $owner->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $owner->email }} • {{ $owner->mobile }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Current Plan -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-[#4b55c8] border border-blue-200">
                                    {{ $subscription->plan?->name ?? 'Standard' }}
                                </span>
                            </td>

                            <!-- Subscription Expiry -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-700 font-semibold">
                                {{ $subscription->end_date->format('F d, Y') }}
                            </td>

                            <!-- Remaining Days -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $daysLeft <= 7 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                    {{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $subscription->effectiveBadgeClasses() }}">
                                    {{ $subscription->effectiveStatusLabel() }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('super-admin.subscriptions.stores.create', ['store_id' => $subscription->store_id]) }}" class="px-3 py-1 rounded-lg bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                                        Renew
                                    </a>
                                    <a href="{{ route('super-admin.subscriptions.stores.show', $subscription) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="text-2xl">🎉</div>
                                    <div class="text-sm font-bold text-[#1e2746]">No subscriptions expiring in {{ $days }} days</div>
                                    <p class="text-xs text-slate-400">All tenant stores currently have active or long-term subscriptions.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($expiringSubscriptions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $expiringSubscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
