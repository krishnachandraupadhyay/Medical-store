@extends('store.layouts.app')

@section('page-title', 'Notification Preferences')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('store.notifications.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Back to Notifications</span>
                </a>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Notification Preferences</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Configure which in-app alerts and reminders you would like to receive.</p>
        </div>
    </div>

    <!-- Preferences Form -->
    <form action="{{ route('store.notifications.update-preferences') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            @foreach ($categories as $categoryName => $types)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <!-- Category Header -->
                    <div class="px-5 py-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $categoryName }}</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Control alert notifications for {{ strtolower($categoryName) }}.</p>
                        </div>
                    </div>

                    <!-- Type Toggles List -->
                    <div class="divide-y divide-slate-100">
                        @foreach ($types as $type)
                            @php
                                $isEnabled = $existingPreferences[$type->value] ?? true;
                            @endphp
                            <div class="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition">
                                <div class="flex items-start gap-3.5 flex-1">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $type->badgeClasses() }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <label for="pref_{{ $type->value }}" class="text-xs sm:text-sm font-bold text-slate-900 block cursor-pointer">
                                            {{ $type->label() }}
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            @switch($type->value)
                                                @case('low_stock')
                                                    Receive alerts when any medicine stock drops below its defined reorder level.
                                                    @break
                                                @case('out_of_stock')
                                                    Receive high-priority critical alerts when a medicine reaches zero units in stock.
                                                    @break
                                                @case('expiring_batch')
                                                    Get advance warnings for batches expiring within the next 30 days.
                                                    @break
                                                @case('expired_batch')
                                                    Critical notifications for expired medicine batches currently in stock.
                                                    @break
                                                @case('customer_outstanding')
                                                    Weekly reminders for customers with pending unpaid sales invoices.
                                                    @break
                                                @case('supplier_outstanding')
                                                    Weekly reminders for outstanding purchase payables due to suppliers.
                                                    @break
                                                @case('customer_payment')
                                                    In-app notifications when a payment is received from a customer.
                                                    @break
                                                @case('supplier_payment')
                                                    In-app notifications when a payment disbursement is made to a supplier.
                                                    @break
                                                @case('sale_completed')
                                                    Get notified immediately when a sales invoice is successfully completed.
                                                    @break
                                                @case('purchase_completed')
                                                    Get notified when purchase goods are received and inventory is restocked.
                                                    @break
                                                @case('sales_return_completed')
                                                    Notifications for customer returns processed and restocked.
                                                    @break
                                                @case('purchase_return_completed')
                                                    Notifications for items returned to suppliers.
                                                    @break
                                                @case('expense_created')
                                                    Notifications when operating expenses or disbursements are recorded.
                                                    @break
                                                @case('subscription_expiring')
                                                    Advance milestone reminders at 7 days, 3 days, and 1 day before store plan expiration.
                                                    @break
                                                @case('subscription_expired')
                                                    Urgent alerts when your store plan has expired to prevent service interruption.
                                                    @break
                                                @default
                                                    Receive system notifications for this category.
                                            @endswitch
                                        </p>
                                    </div>
                                </div>

                                <!-- Toggle Switch -->
                                <div class="flex-shrink-0">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="preferences[{{ $type->value }}]" value="1" id="pref_{{ $type->value }}" class="sr-only peer" {{ $isEnabled ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Action Bar -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('store.notifications.index') }}" class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-sm shadow-indigo-200 transition cursor-pointer">
                    Save Preferences
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
