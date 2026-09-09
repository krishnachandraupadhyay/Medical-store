@extends('super-admin.layouts.app')

@section('title', 'Assign Subscription Plan to Store')
@section('page-title', 'Assign Subscription')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4b55c8] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Store Subscriptions</span>
        </a>
    </div>

    <!-- Form Container Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 bg-[#f8faff]">
            <h2 class="text-lg sm:text-xl font-bold text-[#1e2746] tracking-tight">Assign Subscription Plan to Store</h2>
            <p class="text-xs text-[#64748b] mt-0.5">Provision an active subscription tier and configure validity duration for a medical store tenant.</p>
        </div>

        <form action="{{ route('super-admin.subscriptions.stores.store') }}" method="POST" class="p-6 sm:p-8 space-y-8" id="assignSubscriptionForm">
            @csrf

            <!-- Section 1: Store Selection -->
            <div>
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    1. Target Medical Store
                </h3>

                <div class="space-y-4">
                    <div>
                        <label for="store_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Select Medical Store <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="store_id"
                            id="store_id"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('store_id') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            <option value="">-- Choose an active medical store --</option>
                            @foreach ($stores as $s)
                                <option
                                    value="{{ $s->id }}"
                                    data-code="{{ $s->code }}"
                                    data-owner="{{ $s->owners->isNotEmpty() ? $s->owners->first()->name : 'Unassigned' }}"
                                    data-location="{{ $s->city }}, {{ $s->state }}"
                                    data-active-plan="{{ $s->activeSubscription ? $s->activeSubscription->plan->name : '' }}"
                                    data-active-end="{{ $s->activeSubscription ? $s->activeSubscription->end_date->format('d M Y') : '' }}"
                                    {{ old('store_id', $selectedStoreId) == $s->id ? 'selected' : '' }}
                                >
                                    {{ $s->name }} ({{ $s->code }}) — {{ $s->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Store Info Preview Card -->
                    <div id="storePreviewCard" class="hidden p-4 rounded-2xl bg-blue-50/50 border border-blue-100 text-xs">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <span class="text-slate-400 text-[10px] block font-medium">Store Code</span>
                                <span id="previewStoreCode" class="font-mono font-bold text-[#4b55c8]"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-medium">Assigned Owner</span>
                                <span id="previewStoreOwner" class="font-semibold text-[#1e2746]"></span>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="text-slate-400 text-[10px] block font-medium">Physical Location</span>
                                <span id="previewStoreLocation" class="text-slate-700"></span>
                            </div>
                        </div>

                        <!-- Conflict Warning if active subscription exists -->
                        <div id="existingActiveWarning" class="hidden mt-3 pt-3 border-t border-blue-200/60 text-amber-800 bg-amber-50 p-2.5 rounded-xl border flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <span class="font-bold block">Active Subscription Detected</span>
                                <span>This store currently has an active plan: <strong id="activePlanText"></strong> (valid until <span id="activeEndText" class="font-mono font-bold"></span>). Assigning this new subscription will supersede the previous active record.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Subscription Plan Selection -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    2. Subscription Plan Tier
                </h3>

                <div class="space-y-4">
                    <div>
                        <label for="subscription_plan_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Select Plan Tier <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="subscription_plan_id"
                            id="subscription_plan_id"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('subscription_plan_id') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            <option value="">-- Choose an active subscription plan --</option>
                            @foreach ($plans as $p)
                                <option
                                    value="{{ $p->id }}"
                                    data-name="{{ $p->name }}"
                                    data-price="{{ $p->formattedPrice() }}"
                                    data-cycle="{{ $p->billing_cycle->value }}"
                                    data-trial-days="{{ $p->trial_days }}"
                                    data-limits="{{ json_encode($p->limits) }}"
                                    data-features="{{ json_encode($p->features) }}"
                                    {{ old('subscription_plan_id') == $p->id ? 'selected' : '' }}
                                >
                                    {{ $p->name }} — {{ $p->formattedPrice() }} / {{ $p->billing_cycle->value }} ({{ $p->trial_days }} Days Trial)
                                </option>
                            @endforeach
                        </select>
                        @error('subscription_plan_id')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Plan Capabilities Preview Card -->
                    <div id="planPreviewCard" class="hidden p-4 rounded-2xl bg-[#f8faff] border border-slate-200 text-xs space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div>
                                <span id="previewPlanName" class="font-bold text-[#1e2746] text-sm"></span>
                                <span id="previewPlanCycle" class="text-slate-400 capitalize text-[11px] block"></span>
                            </div>
                            <span id="previewPlanPrice" class="text-base font-black text-[#4b55c8]"></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 font-mono text-[11px]">
                            <div class="p-2 rounded-lg bg-white border border-slate-100">
                                <span class="text-slate-400 block text-[10px] font-sans">Max Staff</span>
                                <span id="limitStaff" class="font-bold text-[#1e2746]"></span>
                            </div>
                            <div class="p-2 rounded-lg bg-white border border-slate-100">
                                <span class="text-slate-400 block text-[10px] font-sans">Max Medicines</span>
                                <span id="limitMedicines" class="font-bold text-[#1e2746]"></span>
                            </div>
                            <div class="p-2 rounded-lg bg-white border border-slate-100">
                                <span class="text-slate-400 block text-[10px] font-sans">Max Invoices</span>
                                <span id="limitInvoices" class="font-bold text-[#1e2746]"></span>
                            </div>
                            <div class="p-2 rounded-lg bg-white border border-slate-100">
                                <span class="text-slate-400 block text-[10px] font-sans">Max Customers</span>
                                <span id="limitCustomers" class="font-bold text-[#1e2746]"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Dates & Duration -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    3. Validity Dates & Billing Period
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
                            value="{{ old('start_date', date('Y-m-d')) }}"
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
                            value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}"
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
                            value="{{ old('trial_ends_at') }}"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('trial_ends_at') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('trial_ends_at')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 4: Status & Notes -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    4. Operational Status & Admin Notes
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Status -->
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
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active (Authorized & Live)</option>
                            <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial (Free Trial Period)</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended (Temporarily Frozen)</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled (Deactivated)</option>
                        </select>
                        @error('status')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Administrative Notes <span class="text-slate-400 font-normal">(Internal remarks, voucher code, offline payment ref)</span>
                        </label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="2"
                            placeholder="e.g. Assigned annual package with promotional 10% discount on manual bank transfer."
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('notes') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition cursor-pointer"
                >
                    Confirm & Assign Subscription
                </button>
            </div>
        </form>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const storeSelect = document.getElementById('store_id');
        const planSelect = document.getElementById('subscription_plan_id');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const trialDateInput = document.getElementById('trial_ends_at');
        const statusSelect = document.getElementById('status');

        const storePreviewCard = document.getElementById('storePreviewCard');
        const previewStoreCode = document.getElementById('previewStoreCode');
        const previewStoreOwner = document.getElementById('previewStoreOwner');
        const previewStoreLocation = document.getElementById('previewStoreLocation');
        const existingActiveWarning = document.getElementById('existingActiveWarning');
        const activePlanText = document.getElementById('activePlanText');
        const activeEndText = document.getElementById('activeEndText');

        const planPreviewCard = document.getElementById('planPreviewCard');
        const previewPlanName = document.getElementById('previewPlanName');
        const previewPlanCycle = document.getElementById('previewPlanCycle');
        const previewPlanPrice = document.getElementById('previewPlanPrice');
        const limitStaff = document.getElementById('limitStaff');
        const limitMedicines = document.getElementById('limitMedicines');
        const limitInvoices = document.getElementById('limitInvoices');
        const limitCustomers = document.getElementById('limitCustomers');

        function updateStorePreview() {
            const selected = storeSelect.options[storeSelect.selectedIndex];
            if (selected && selected.value) {
                previewStoreCode.textContent = selected.getAttribute('data-code') || '';
                previewStoreOwner.textContent = selected.getAttribute('data-owner') || '';
                previewStoreLocation.textContent = selected.getAttribute('data-location') || '';
                storePreviewCard.classList.remove('hidden');

                const activePlan = selected.getAttribute('data-active-plan');
                const activeEnd = selected.getAttribute('data-active-end');
                if (activePlan && activeEnd) {
                    activePlanText.textContent = activePlan;
                    activeEndText.textContent = activeEnd;
                    existingActiveWarning.classList.remove('hidden');
                } else {
                    existingActiveWarning.classList.add('hidden');
                }
            } else {
                storePreviewCard.classList.add('hidden');
            }
        }

        function updatePlanPreviewAndDates() {
            const selected = planSelect.options[planSelect.selectedIndex];
            if (selected && selected.value) {
                const name = selected.getAttribute('data-name');
                const price = selected.getAttribute('data-price');
                const cycle = selected.getAttribute('data-cycle');
                const trialDays = parseInt(selected.getAttribute('data-trial-days') || '0', 10);
                const limits = JSON.parse(selected.getAttribute('data-limits') || '{}');

                previewPlanName.textContent = name;
                previewPlanCycle.textContent = cycle + ' billing cycle';
                previewPlanPrice.textContent = price;

                limitStaff.textContent = limits.max_staff === -1 || limits.max_staff === '-1' ? 'Unlimited' : (limits.max_staff || 'Unlimited');
                limitMedicines.textContent = limits.max_medicines === -1 || limits.max_medicines === '-1' ? 'Unlimited' : (limits.max_medicines || 'Unlimited');
                limitInvoices.textContent = limits.max_invoices === -1 || limits.max_invoices === '-1' ? 'Unlimited' : (limits.max_invoices || 'Unlimited');
                limitCustomers.textContent = limits.max_customers === -1 || limits.max_customers === '-1' ? 'Unlimited' : (limits.max_customers || 'Unlimited');

                planPreviewCard.classList.remove('hidden');

                // Auto calculate end date if not custom modified
                const startDateVal = startDateInput.value ? new Date(startDateInput.value) : new Date();
                if (!isNaN(startDateVal.getTime())) {
                    const endDateVal = new Date(startDateVal);
                    if (cycle === 'yearly') {
                        endDateVal.setFullYear(endDateVal.getFullYear() + 1);
                    } else {
                        endDateVal.setDate(endDateVal.getDate() + 30);
                    }
                    endDateInput.value = endDateVal.toISOString().split('T')[0];

                    if (trialDays > 0) {
                        const trialEndVal = new Date(startDateVal);
                        trialEndVal.setDate(trialEndVal.getDate() + trialDays);
                        trialDateInput.value = trialEndVal.toISOString().split('T')[0];
                        statusSelect.value = 'trial';
                    } else {
                        trialDateInput.value = '';
                        statusSelect.value = 'active';
                    }
                }
            } else {
                planPreviewCard.classList.add('hidden');
            }
        }

        storeSelect.addEventListener('change', updateStorePreview);
        planSelect.addEventListener('change', updatePlanPreviewAndDates);

        // Run initially for old values
        updateStorePreview();
        if (planSelect.value) {
            const selected = planSelect.options[planSelect.selectedIndex];
            previewPlanName.textContent = selected.getAttribute('data-name');
            previewPlanCycle.textContent = selected.getAttribute('data-cycle') + ' billing cycle';
            previewPlanPrice.textContent = selected.getAttribute('data-price');
            const limits = JSON.parse(selected.getAttribute('data-limits') || '{}');
            limitStaff.textContent = limits.max_staff === -1 || limits.max_staff === '-1' ? 'Unlimited' : (limits.max_staff || 'Unlimited');
            limitMedicines.textContent = limits.max_medicines === -1 || limits.max_medicines === '-1' ? 'Unlimited' : (limits.max_medicines || 'Unlimited');
            limitInvoices.textContent = limits.max_invoices === -1 || limits.max_invoices === '-1' ? 'Unlimited' : (limits.max_invoices || 'Unlimited');
            limitCustomers.textContent = limits.max_customers === -1 || limits.max_customers === '-1' ? 'Unlimited' : (limits.max_customers || 'Unlimited');
            planPreviewCard.classList.remove('hidden');
        }
    });
</script>
@endpush
@endsection
