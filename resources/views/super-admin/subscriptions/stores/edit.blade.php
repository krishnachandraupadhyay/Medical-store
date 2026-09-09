@extends('super-admin.layouts.app')

@section('title', 'Edit Subscription - ' . $subscription->store->name)
@section('page-title', 'Edit Subscription')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.subscriptions.stores.show', $subscription) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4b55c8] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Subscription Details</span>
        </a>
    </div>

    <!-- Form Container Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 bg-[#f8faff]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-[#1e2746] tracking-tight">Edit Store Subscription</h2>
                    <p class="text-xs text-[#64748b] mt-0.5">Modify tier plan, validity period, or operational status for this subscription.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-blue-50 border border-blue-200 text-[#4b55c8] text-xs font-mono font-bold">
                    <span>{{ $subscription->store->code }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('super-admin.subscriptions.stores.update', $subscription) }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Store Permanent Info -->
            <div class="p-4 rounded-2xl bg-blue-50/40 border border-blue-100/80 flex items-center justify-between">
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-semibold block">Target Medical Store</span>
                    <span class="text-base font-bold text-[#1e2746]">{{ $subscription->store->name }}</span>
                    <span class="text-xs text-slate-500 block">{{ $subscription->store->city }}, {{ $subscription->store->state }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 text-[10px] uppercase font-semibold block">Store Owner</span>
                    <span class="text-xs font-bold text-slate-700">
                        {{ $subscription->store->owners->isNotEmpty() ? $subscription->store->owners->first()->name : 'Unassigned' }}
                    </span>
                </div>
            </div>

            <!-- Section 1: Plan Tier -->
            <div>
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    1. Subscription Plan Tier
                </h3>

                <div>
                    <label for="subscription_plan_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Selected Plan Tier <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="subscription_plan_id"
                        id="subscription_plan_id"
                        required
                        class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('subscription_plan_id') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                    >
                        @foreach ($plans as $p)
                            <option value="{{ $p->id }}" {{ old('subscription_plan_id', $subscription->subscription_plan_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} — {{ $p->formattedPrice() }} / {{ $p->billing_cycle->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('subscription_plan_id')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 2: Validity Period -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    2. Validity Dates & Billing Period
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Start Date <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            value="{{ old('start_date', $subscription->start_date->format('Y-m-d')) }}"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('start_date') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('start_date')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            End / Expiry Date <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            value="{{ old('end_date', $subscription->end_date->format('Y-m-d')) }}"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('end_date') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('end_date')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Trial End Date -->
                    <div>
                        <label for="trial_ends_at" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Trial End Date <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <input
                            type="date"
                            name="trial_ends_at"
                            id="trial_ends_at"
                            value="{{ old('trial_ends_at', $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') : '') }}"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('trial_ends_at') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('trial_ends_at')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Status & Notes -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    3. Operational Status & Admin Notes
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Subscription Status <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('status') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            <option value="active" {{ old('status', $subscription->status->value) == 'active' ? 'selected' : '' }}>Active (Authorized & Live)</option>
                            <option value="trial" {{ old('status', $subscription->status->value) == 'trial' ? 'selected' : '' }}>Trial (Free Trial Period)</option>
                            <option value="suspended" {{ old('status', $subscription->status->value) == 'suspended' ? 'selected' : '' }}>Suspended (Temporarily Frozen)</option>
                            <option value="cancelled" {{ old('status', $subscription->status->value) == 'cancelled' ? 'selected' : '' }}>Cancelled (Deactivated)</option>
                            <option value="expired" {{ old('status', $subscription->status->value) == 'expired' ? 'selected' : '' }}>Expired (Period Ended)</option>
                        </select>
                        @error('status')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Administrative Notes
                        </label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="3"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('notes') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >{{ old('notes', $subscription->notes) }}</textarea>
                        @error('notes')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.subscriptions.stores.show', $subscription) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition cursor-pointer"
                >
                    Update Subscription
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
