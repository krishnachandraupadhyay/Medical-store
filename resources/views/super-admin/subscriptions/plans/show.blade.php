@extends('super-admin.layouts.app')

@section('title', $plan->name . ' - Subscription Plan')
@section('page-title', 'Plan Overview')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#64748b] hover:text-[#1e2746] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Plans Directory</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.subscriptions.plans.edit', $plan) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-[#1e2746] border border-slate-200 text-xs font-semibold shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Plan</span>
            </a>
        </div>
    </div>

    <!-- Plan Hero Pricing Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <div class="space-y-2">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">{{ $plan->name }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $plan->status->badgeClasses() }}">
                        {{ $plan->status->label() }}
                    </span>
                    @if ($plan->is_popular)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Most Popular Tier
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-xs text-[#64748b]">
                    <span class="font-mono text-[#4b55c8] font-bold">{{ $plan->slug }}</span>
                    <span>•</span>
                    <span>{{ $plan->billing_cycle->label() }} Recurring Billing</span>
                    <span>•</span>
                    <span>{{ $plan->trial_days > 0 ? $plan->trial_days . ' Days Trial' : 'No Free Trial' }}</span>
                </div>

                @if ($plan->description)
                    <p class="text-xs sm:text-sm text-slate-600 max-w-xl pt-1">
                        {{ $plan->description }}
                    </p>
                @endif
            </div>

            <!-- Price & Status Actions -->
            <div class="flex flex-col md:items-end gap-4">
                <div class="text-left md:text-right">
                    <div class="text-3xl sm:text-4xl font-black text-[#1e2746] tracking-tight">{{ $plan->formattedPrice() }}</div>
                    <span class="text-xs text-[#64748b] font-medium">per {{ $plan->billing_cycle->value === 'yearly' ? 'year' : 'month' }} / store</span>
                </div>

                <!-- Status Action Toggle -->
                <form action="{{ route('super-admin.subscriptions.plans.update-status', $plan) }}" method="POST" onsubmit="return confirm('Are you sure you want to {{ $plan->isActive() ? 'DEACTIVATE' : 'ACTIVATE' }} this plan?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $plan->isActive() ? 'inactive' : 'active' }}">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl {{ $plan->isActive() ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }} text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        @if ($plan->isActive())
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Deactivate Plan</span>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Activate Plan</span>
                        @endif
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Grid: Capacity Limits vs Enabled Modules -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: Resource Capacity Limits -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/></svg>
                Resource Capacity Limits
            </h3>

            <div class="space-y-3 text-xs">
                @foreach ($supportedLimits as $key => $limitDef)
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/70 border border-slate-100">
                        <div>
                            <span class="font-bold text-[#1e2746] block">{{ $limitDef['label'] }}</span>
                            <span class="text-[10px] text-[#64748b]">{{ $limitDef['description'] }}</span>
                        </div>
                        <span class="font-mono font-bold text-sm {{ $plan->isUnlimited($key) ? 'text-purple-600 bg-purple-50 border border-purple-200' : 'text-[#1e2746] bg-white border border-slate-200' }} px-3 py-1 rounded-xl shadow-xs">
                            {{ $plan->displayLimit($key) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Card 2: Enabled Functional Modules -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Functional Modules Matrix
            </h3>

            <div class="space-y-2.5">
                @foreach ($supportedFeatures as $fKey => $feature)
                    @php $hasFeature = $plan->hasFeature($fKey); @endphp
                    <div class="flex items-start gap-3 p-2.5 rounded-xl {{ $hasFeature ? 'bg-emerald-50/40 border border-emerald-100' : 'bg-slate-50/40 border border-slate-100 opacity-60' }}">
                        @if ($hasFeature)
                            <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @else
                            <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        @endif
                        <div>
                            <span class="text-xs font-bold {{ $hasFeature ? 'text-[#1e2746]' : 'text-slate-500' }} block">
                                {{ $feature['name'] }}
                            </span>
                            <span class="text-[10px] text-[#64748b] leading-tight block">
                                {{ $feature['description'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Card 3: Governance & Record Details -->
        <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                System Audit & Metadata
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 text-xs">
                <div>
                    <span class="text-slate-400 block">Created By</span>
                    <span class="font-bold text-[#1e2746]">{{ $plan->creator->name ?? 'System Administrator' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Creation Timestamp</span>
                    <span class="font-mono text-slate-700">{{ $plan->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Last Modified</span>
                    <span class="font-mono text-slate-700">{{ $plan->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
