@extends('super-admin.layouts.app')

@section('title', 'Create Subscription Plan')
@section('page-title', 'Create Plan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#64748b] hover:text-[#1e2746] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Plans Directory</span>
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-lg sm:text-xl font-bold text-[#1e2746] tracking-tight">Provision New Subscription Tier</h2>
            <p class="text-xs text-[#64748b] mt-0.5">Define pricing structure, resource capacities, and enabled functional modules.</p>
        </div>

        <form action="{{ route('super-admin.subscriptions.plans.store') }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf

            <!-- Section 1: Basic Information -->
            <div>
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    1. General Information & Pricing
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Plan Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="e.g. Standard Growth Plan"
                            class="w-full px-3.5 py-2.5 bg-white border @error('name') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none transition"
                        >
                        @error('name')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug (Optional) -->
                    <div>
                        <label for="slug" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            URL Slug <span class="text-slate-400 font-normal">(Optional - auto-generated)</span>
                        </label>
                        <input
                            type="text"
                            name="slug"
                            id="slug"
                            value="{{ old('slug') }}"
                            placeholder="e.g. standard-growth-plan"
                            class="w-full px-3.5 py-2.5 bg-white border @error('slug') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none transition font-mono"
                        >
                        @error('slug')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Price (₹) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                                ₹
                            </div>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="price"
                                id="price"
                                value="{{ old('price', '999.00') }}"
                                required
                                placeholder="0.00 for free"
                                class="w-full pl-8 pr-3.5 py-2.5 bg-white border @error('price') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none transition font-mono"
                            >
                        </div>
                        @error('price')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Cycle -->
                    <div>
                        <label for="billing_cycle" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Billing Frequency <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="billing_cycle"
                            id="billing_cycle"
                            required
                            class="w-full px-3.5 py-2.5 bg-white border @error('billing_cycle') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none transition"
                        >
                            @foreach ($billingCycles as $cycle)
                                <option value="{{ $cycle->value }}" {{ old('billing_cycle', 'monthly') === $cycle->value ? 'selected' : '' }}>
                                    {{ $cycle->label() }} Recurring Billing
                                </option>
                            @endforeach
                        </select>
                        @error('billing_cycle')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Trial Days -->
                    <div>
                        <label for="trial_days" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Trial Period (Days) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            min="0"
                            max="365"
                            name="trial_days"
                            id="trial_days"
                            value="{{ old('trial_days', '14') }}"
                            required
                            placeholder="0 for no trial"
                            class="w-full px-3.5 py-2.5 bg-white border @error('trial_days') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none transition font-mono"
                        >
                        @error('trial_days')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Plan Status <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="w-full px-3.5 py-2.5 bg-white border @error('status') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none transition"
                        >
                            @foreach ($planStatuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', 'active') === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }} (Available for Stores)
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Plan Summary Description
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="2"
                            placeholder="Highlight who this plan is suitable for (e.g. ideal for small to medium retail pharmacy stores with up to 5 staff members)."
                            class="w-full px-3.5 py-2.5 bg-white border @error('description') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#4b55c8] focus:ring-4 focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none transition"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Highlight Options -->
                    <div class="sm:col-span-2 flex items-center gap-6 pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[#334155]">
                            <input
                                type="checkbox"
                                name="is_popular"
                                value="1"
                                {{ old('is_popular') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-[#4b55c8] focus:ring-[#4b55c8]/20 transition cursor-pointer"
                            >
                            <span>Mark as "Most Popular" Featured Tier</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 2: Store Resource Limits -->
            <div class="pt-6 border-t border-slate-100">
                <div class="mb-4">
                    <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                        2. Store Resource & Capacity Limits
                    </h3>
                    <p class="text-[11px] text-[#64748b] mt-0.5">Enter numerical limits or specify <span class="font-mono font-bold text-[#4b55c8]">-1</span> for unlimited capacity.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach ($supportedLimits as $key => $limit)
                        <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-1.5">
                            <label for="limit_{{ $key }}" class="block text-xs font-bold text-[#1e2746]">
                                {{ $limit['label'] }} <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[11px] text-[#64748b]">{{ $limit['description'] }}</p>
                            <div class="pt-1">
                                <input
                                    type="number"
                                    min="-1"
                                    name="limits[{{ $key }}]"
                                    id="limit_{{ $key }}"
                                    value="{{ old('limits.'.$key, $limit['default']) }}"
                                    required
                                    class="w-full px-3.5 py-2 bg-white border border-slate-200 focus:border-[#4b55c8] focus:ring-2 focus:ring-[#4b55c8]/10 rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none transition font-mono font-bold"
                                >
                            </div>
                            @error('limits.'.$key)
                                <p class="text-rose-500 text-[11px]">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Section 3: Enabled Modules & Features -->
            <div class="pt-6 border-t border-slate-100">
                <div class="mb-4">
                    <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                        3. Enabled Functional Modules
                    </h3>
                    <p class="text-[11px] text-[#64748b] mt-0.5">Select all functional capabilities granted to medical stores subscribed to this tier.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($supportedFeatures as $fKey => $feature)
                        <label class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-[#4b55c8]/40 hover:bg-[#f8faff] transition flex items-start gap-3 cursor-pointer group select-none">
                            <input
                                type="checkbox"
                                name="features[]"
                                value="{{ $fKey }}"
                                {{ is_array(old('features', ['inventory_management', 'purchase_management', 'sales_management', 'customer_management', 'reports'])) && in_array($fKey, old('features', ['inventory_management', 'purchase_management', 'sales_management', 'customer_management', 'reports'])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-[#4b55c8] focus:ring-[#4b55c8]/20 transition cursor-pointer mt-0.5"
                            >
                            <div>
                                <span class="text-xs font-bold text-[#1e2746] group-hover:text-[#4b55c8] transition block">
                                    {{ $feature['name'] }}
                                </span>
                                <span class="text-[11px] text-[#64748b] leading-tight block mt-0.5">
                                    {{ $feature['description'] }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-[#64748b] text-xs font-semibold transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] active:scale-[0.99] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/20 transition cursor-pointer"
                >
                    Create Subscription Plan
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
