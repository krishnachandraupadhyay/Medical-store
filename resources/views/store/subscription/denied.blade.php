@extends('store.layouts.app')

@section('title', 'Feature Access Restricted')

@section('content')
<div class="h-full flex items-center justify-center p-4 sm:p-8">
    <div class="max-w-lg w-full bg-white border border-slate-200/80 rounded-3xl p-8 sm:p-10 shadow-xl shadow-slate-100 text-center space-y-6">
        <!-- Shield Icon -->
        <div class="w-16 h-16 mx-auto rounded-3xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-5a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <!-- Heading & Message -->
        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase {{ $code === 'SUBSCRIPTION_EXPIRED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                {{ str_replace('_', ' ', $code) }}
            </span>
            <h2 class="text-xl sm:text-2xl font-black text-[#1e2746] tracking-tight">
                @if ($code === 'SUBSCRIPTION_EXPIRED')
                    Subscription Expired
                @elseif ($code === 'FEATURE_NOT_INCLUDED')
                    Feature Not In Current Plan
                @else
                    Access Restricted
                @endif
            </h2>
            <p class="text-xs sm:text-sm text-[#64748b] leading-relaxed">
                {{ $message }}
            </p>
        </div>

        <!-- Store & Current Plan Meta Box -->
        @php
            $currentPlan = subscription_access()->getCurrentPlan($store ?? null);
        @endphp
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-left text-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Medical Store:</span>
                <span class="font-bold text-[#1e2746]">{{ $store->name ?? 'Your Store' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Current Plan:</span>
                <span class="font-bold text-[#4b55c8]">{{ $currentPlan->name ?? 'No Active Plan' }}</span>
            </div>
            @if (!empty($feature))
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Requested Module:</span>
                    <span class="font-mono font-semibold text-slate-700">{{ $feature }}</span>
                </div>
            @endif
        </div>

        <!-- Action CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <a href="{{ route('store.dashboard') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#4b55c8] text-white font-semibold text-xs sm:text-sm hover:bg-[#3f49b8] transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Back to Dashboard</span>
            </a>
            <a href="mailto:support@medistore.io?subject=Subscription%20Upgrade%20Inquiry%20-%20{{ urlencode($store->name ?? 'Medical Store') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                Contact Platform Support
            </a>
        </div>
    </div>
</div>
@endsection
