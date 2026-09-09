@extends('super-admin.layouts.app')

@section('title', 'Payment Details - ' . $payment->transaction_id)
@section('page-title', 'Payment Receipt & Details')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.payments.index') }}" class="hover:text-[#4b55c8] transition">Payments</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold font-mono">{{ $payment->transaction_id }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight flex items-center gap-3">
                <span>Receipt: {{ $payment->transaction_id }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $payment->status->badgeClasses() }}">
                    {{ $payment->status->label() }}
                </span>
            </h2>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.payments.edit', $payment) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Payment</span>
            </a>
            <a href="{{ route('super-admin.payments.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Back to List
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Payment Summary Banner -->
    <div class="bg-gradient-to-r from-[#1e2746] to-[#2d3a66] text-white rounded-3xl p-6 sm:p-8 shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider block">Payment Total Amount</span>
                <div class="text-3xl sm:text-4xl font-black tracking-tight text-white flex items-baseline gap-2">
                    <span>₹{{ number_format($payment->amount, 2) }}</span>
                    <span class="text-xs font-mono text-slate-300 font-normal uppercase">{{ $payment->currency }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs text-slate-300">
                    <span class="font-mono bg-white/10 px-2.5 py-1 rounded-lg">{{ $payment->transaction_id }}</span>
                    <span>•</span>
                    <span>Method: <strong>{{ $payment->payment_method->label() }}</strong></span>
                    <span>•</span>
                    <span>Date: <strong>{{ $payment->payment_date->format('M d, Y h:i A') }}</strong></span>
                </div>
            </div>

            <!-- Quick Status Transition Form -->
            <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10 max-w-sm w-full md:w-auto">
                <span class="text-[11px] font-bold text-slate-200 uppercase tracking-wider block mb-2">Update Payment Status</span>
                <form action="{{ route('super-admin.payments.update-status', $payment) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="px-3 py-1.5 text-xs rounded-xl bg-white text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-[#4b55c8]">
                        @foreach (\App\Enums\PaymentStatus::cases() as $st)
                            <option value="{{ $st->value }}" {{ $payment->status === $st ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 3-Column Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- 1. Payment Information -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Payment Details
            </h3>

            <div class="space-y-2.5 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Transaction Reference</span>
                    <span class="font-mono font-bold text-slate-800 select-all">{{ $payment->transaction_id }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Payment Method</span>
                    <span class="font-semibold text-slate-800">{{ $payment->payment_method->label() }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Payment Date</span>
                    <span class="font-semibold text-slate-800">{{ $payment->payment_date->format('F d, Y') }}</span>
                    <span class="text-[10px] text-slate-400 block font-mono">{{ $payment->payment_date->format('h:i:s A') }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Status</span>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $payment->status->badgeClasses() }}">
                        {{ $payment->status->label() }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Gateway Integration</span>
                    <span class="text-slate-500 font-mono text-[11px]">
                        {{ $payment->gateway_payment_id ?: 'Manual Admin Entry' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Store Information -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Medical Store Details
            </h3>

            @if ($payment->store)
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Store Name</span>
                        <a href="{{ route('super-admin.stores.show', $payment->store) }}" class="font-bold text-[#4b55c8] hover:underline">
                            {{ $payment->store->name }}
                        </a>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Store Code</span>
                        <span class="font-mono font-semibold text-slate-700">{{ $payment->store->code }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Store Owner</span>
                        <span class="font-semibold text-slate-800">
                            {{ $payment->store->owners->isNotEmpty() ? $payment->store->owners->first()->name : 'Unassigned' }}
                        </span>
                        @if ($payment->store->owners->isNotEmpty())
                            <span class="text-[10px] text-slate-400 block">{{ $payment->store->owners->first()->email }}</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Location</span>
                        <span class="text-slate-700">{{ $payment->store->city }}, {{ $payment->store->state }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Store Status</span>
                        <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $payment->store->status->badgeClasses() }}">
                            {{ $payment->store->status->label() }}
                        </span>
                    </div>
                </div>
            @else
                <div class="text-xs text-slate-400 italic">Store record no longer exists.</div>
            @endif
        </div>

        <!-- 3. Subscription & Plan Information -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Subscription Plan
            </h3>

            @php
                $plan = $payment->subscriptionPlan ?? $payment->subscription?->plan;
                $sub = $payment->subscription;
            @endphp

            @if ($plan)
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Plan Name</span>
                        <a href="{{ route('super-admin.subscriptions.plans.show', $plan) }}" class="font-bold text-[#4b55c8] hover:underline">
                            {{ $plan->name }}
                        </a>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Plan Tier Price</span>
                        <span class="font-bold text-slate-800">₹{{ number_format($plan->price, 2) }}</span>
                        <span class="text-slate-400 text-[10px]">/ {{ $plan->billing_cycle->label() }}</span>
                    </div>

                    @if ($sub)
                        <div>
                            <span class="text-slate-400 block text-[11px]">Subscription Validity</span>
                            <span class="font-mono text-slate-700 text-[11px]">
                                {{ $sub->start_date->format('M d, Y') }} &rarr; {{ $sub->end_date->format('M d, Y') }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px]">Subscription Status</span>
                            <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sub->effectiveBadgeClasses() }}">
                                {{ $sub->effectiveStatusLabel() }}
                            </span>
                        </div>

                        <div class="pt-1">
                            <a href="{{ route('super-admin.subscriptions.stores.show', $sub) }}" class="text-[11px] font-bold text-[#4b55c8] hover:underline">
                                View Full Subscription Record &rarr;
                            </a>
                        </div>
                    @else
                        <div>
                            <span class="text-slate-400 block text-[11px]">Subscription Link</span>
                            <span class="text-slate-500 text-xs">Direct Plan Payment</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-xs text-slate-400 italic">No specific subscription plan linked.</div>
            @endif
        </div>

    </div>

    <!-- Additional Information & Audit Meta Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Notes & Audit Logs
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <!-- Notes -->
            <div class="space-y-1.5">
                <span class="text-slate-400 block text-[11px] font-bold uppercase tracking-wider">Internal Payment Notes</span>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 whitespace-pre-line text-xs font-mono">
                    {{ $payment->notes ?: 'No notes or special remarks entered.' }}
                </div>
            </div>

            <!-- Audit Meta -->
            <div class="space-y-2.5">
                <span class="text-slate-400 block text-[11px] font-bold uppercase tracking-wider">System Audit Trail</span>
                
                <div class="space-y-2 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Recorded By:</span>
                        <span class="font-bold text-slate-700">{{ $payment->creator?->name ?? 'Super Admin' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Created Timestamp:</span>
                        <span class="font-mono text-[11px] text-slate-700">{{ $payment->created_at->format('M d, Y h:i:s A') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Last Modified:</span>
                        <span class="font-mono text-[11px] text-slate-700">{{ $payment->updated_at->format('M d, Y h:i:s A') }}</span>
                    </div>

                    @if ($payment->updater)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Last Updated By:</span>
                            <span class="font-bold text-slate-700">{{ $payment->updater->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
