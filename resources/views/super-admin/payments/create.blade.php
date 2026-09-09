@extends('super-admin.layouts.app')

@section('title', 'Record Payment')
@section('page-title', 'Record Manual Payment')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.payments.index') }}" class="hover:text-[#4b55c8] transition">Payments</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Record Payment</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Record Subscription Payment</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Manually log offline or direct payments for tenant store subscriptions.</p>
        </div>

        <a href="{{ route('super-admin.payments.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
            &larr; Back to Payments
        </a>
    </div>

    <!-- Error Validation Banner -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1 shadow-sm">
            <div class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Please fix the following validation errors:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Payment Form Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('super-admin.payments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Store & Subscription Linkage -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-[#1e2746] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    Store & Subscription Association
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Store Selector -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label for="store_id" class="block text-xs font-bold text-slate-700">
                            Medical Store <span class="text-rose-500">*</span>
                        </label>
                        <select id="store_id" name="store_id" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('store_id') border-rose-400 bg-rose-50/30 @enderror">
                            <option value="">-- Select Medical Store --</option>
                            @foreach ($stores as $store)
                                @php
                                    $isSelected = (string)old('store_id', $selectedStoreId) === (string)$store->id;
                                    $activeSub = $store->activeSubscription;
                                    $subInfo = $activeSub ? ($activeSub->plan?->name . ' - Expires ' . $activeSub->end_date->format('M d, Y')) : 'No Active Subscription';
                                @endphp
                                <option value="{{ $store->id }}" 
                                        data-plan-id="{{ $activeSub?->subscription_plan_id ?? '' }}"
                                        data-sub-id="{{ $activeSub?->id ?? '' }}"
                                        data-amount="{{ $activeSub?->plan?->price ?? '' }}"
                                        {{ $isSelected ? 'selected' : '' }}>
                                    {{ $store->name }} ({{ $store->code }}) — {{ $store->city }} [{{ $subInfo }}]
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subscription Plan (Optional Override) -->
                    <div class="space-y-1.5">
                        <label for="subscription_plan_id" class="block text-xs font-bold text-slate-700">
                            Subscription Plan
                        </label>
                        <select id="subscription_plan_id" name="subscription_plan_id" class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                            <option value="">-- Auto-detect from Store's Active Subscription --</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" data-price="{{ $plan->price }}" {{ (string)old('subscription_plan_id') === (string)$plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} (₹{{ number_format($plan->price, 2) }} / {{ $plan->billing_cycle->label() }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400">Leave blank to link with store's current active subscription plan.</p>
                    </div>

                    <!-- Custom Subscription ID (Optional) -->
                    <div class="space-y-1.5">
                        <label for="subscription_id" class="block text-xs font-bold text-slate-700">
                            Subscription Record ID (Optional)
                        </label>
                        <input type="number" 
                               id="subscription_id" 
                               name="subscription_id" 
                               value="{{ old('subscription_id', $selectedSubscriptionId) }}" 
                               placeholder="e.g. 1" 
                               class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                        <p class="text-[11px] text-slate-400">Auto-resolved if not specified.</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Transaction & Payment Particulars -->
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-bold text-[#1e2746] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    Transaction Particulars
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    
                    <!-- Amount -->
                    <div class="space-y-1.5">
                        <label for="amount" class="block text-xs font-bold text-slate-700">
                            Payment Amount (₹) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-xs sm:text-sm">
                                ₹
                            </span>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   id="amount"
                                   name="amount"
                                   value="{{ old('amount') }}"
                                   placeholder="0.00"
                                   required
                                   class="w-full pl-8 pr-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('amount') border-rose-400 bg-rose-50/30 @enderror" />
                        </div>
                        @error('amount')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="space-y-1.5">
                        <label for="currency" class="block text-xs font-bold text-slate-700">
                            Currency <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               id="currency"
                               name="currency"
                               value="{{ old('currency', 'INR') }}"
                               required
                               class="w-full px-4 py-2.5 text-xs sm:text-sm uppercase font-mono rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                    </div>

                    <!-- Payment Method -->
                    <div class="space-y-1.5">
                        <label for="payment_method" class="block text-xs font-bold text-slate-700">
                            Payment Method <span class="text-rose-500">*</span>
                        </label>
                        <select id="payment_method" name="payment_method" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('payment_method') border-rose-400 @enderror">
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->value }}" {{ old('payment_method', 'cash') === $method->value ? 'selected' : '' }}>
                                    {{ $method->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Transaction ID -->
                    <div class="space-y-1.5">
                        <label for="transaction_id" class="block text-xs font-bold text-slate-700">
                            Transaction / Reference ID <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="transaction_id"
                                   name="transaction_id"
                                   value="{{ old('transaction_id', $generatedTxnId) }}"
                                   required
                                   class="w-full px-4 py-2.5 text-xs sm:text-sm font-mono font-bold text-[#1e2746] rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('transaction_id') border-rose-400 bg-rose-50/30 @enderror" />
                        </div>
                        @error('transaction_id')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Date -->
                    <div class="space-y-1.5">
                        <label for="payment_date" class="block text-xs font-bold text-slate-700">
                            Payment Date & Time <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local"
                               id="payment_date"
                               name="payment_date"
                               value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}"
                               required
                               class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('payment_date') border-rose-400 @enderror" />
                        @error('payment_date')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Status -->
                    <div class="space-y-1.5">
                        <label for="status" class="block text-xs font-bold text-slate-700">
                            Payment Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="status" name="status" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('status') border-rose-400 @enderror">
                            @foreach ($paymentStatuses as $statusCase)
                                <option value="{{ $statusCase->value }}" {{ old('status', 'paid') === $statusCase->value ? 'selected' : '' }}>
                                    {{ $statusCase->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Section 3: Notes / Remarks -->
            <div class="space-y-1.5 pt-2">
                <label for="notes" class="block text-xs font-bold text-slate-700">
                    Payment Notes / Remarks (Optional)
                </label>
                <textarea id="notes"
                          name="notes"
                          rows="3"
                          placeholder="e.g. Received via NEFT reference #876238491 for quarterly renewal..."
                          class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('super-admin.payments.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition active:scale-[0.99] cursor-pointer">
                    Save Payment Record
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
        const amountInput = document.getElementById('amount');
        const subIdInput = document.getElementById('subscription_id');

        storeSelect?.addEventListener('change', function () {
            const selectedOption = storeSelect.options[storeSelect.selectedIndex];
            if (selectedOption) {
                const planId = selectedOption.getAttribute('data-plan-id');
                const subId = selectedOption.getAttribute('data-sub-id');
                const defaultAmount = selectedOption.getAttribute('data-amount');

                if (planId && planSelect && !planSelect.value) {
                    planSelect.value = planId;
                }
                if (subId && subIdInput && !subIdInput.value) {
                    subIdInput.value = subId;
                }
                if (defaultAmount && amountInput && !amountInput.value) {
                    amountInput.value = defaultAmount;
                }
            }
        });
    });
</script>
@endpush
@endsection
