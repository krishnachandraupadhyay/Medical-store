@extends('store.layouts.app')

@section('title', 'Store Dashboard')
@section('page-title', 'Pharmacy Dashboard')

@section('content')
<div class="space-y-6 max-w-full mx-auto">

    <!-- 1. Top Welcome Banner & Key Store Identification -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#eef3fe] via-[#eaf0fc] to-[#e4ecfb] border border-[#dce5f8] p-6 sm:p-7 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
        
        <div class="z-10 max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100/90 text-[#4b55c8] text-xs font-bold mb-3 border border-blue-200/60">
                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                <span>Tenant Pharmacy Management</span>
            </div>
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-[#1e2746] tracking-tight">
                Welcome back, {{ $user->name }} 👋
            </h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-2 font-medium leading-relaxed">
                Managing <strong class="text-[#1e2746]">{{ $store->name }}</strong> • Store Code: <span class="font-mono text-[#4b55c8] font-bold">{{ $store->code }}</span> • {{ $store->city }}, {{ $store->state }}
            </p>
        </div>

        <!-- Store Profile Card Widget -->
        <div class="bg-white/95 backdrop-blur rounded-2xl border border-white shadow-lg p-4 w-full md:w-auto min-w-[260px] flex flex-col gap-2">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white text-xs font-bold">
                        +
                    </div>
                    <span class="text-xs font-bold text-[#1e2746]">{{ $store->name }}</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Active
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                <div>
                    <span class="text-slate-400 block text-[10px]">Drug License</span>
                    <span class="font-mono font-semibold text-slate-700 truncate block">{{ $store->drug_license_no ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">GSTIN</span>
                    <span class="font-mono font-semibold text-slate-700 truncate block">{{ $store->gstin ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Real Database Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Card 1: Subscription Status -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Subscription Plan</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                        💳
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#1e2746] tracking-tight mt-2">
                    @if ($activeSubscription && $activeSubscription->plan)
                        {{ $activeSubscription->plan->name }}
                    @else
                        No Active Plan
                    @endif
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    @if ($activeSubscription)
                        Valid until: <strong class="text-[#1e2746]">{{ $activeSubscription->end_date->format('d M Y') }}</strong>
                    @else
                        No subscription assigned yet
                    @endif
                </p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Plan Status:</span>
                <span class="font-bold {{ $activeSubscription ? 'text-[#4b55c8]' : 'text-amber-600' }}">
                    {{ $activeSubscription ? strtoupper($activeSubscription->status->value) : 'UNASSIGNED' }}
                </span>
            </div>
        </div>

        <!-- Card 2: Store Users & Staff -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Store Staff & Users</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                        👥
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#1e2746] tracking-tight mt-2">
                    {{ $totalUsers }} <span class="text-xs font-normal text-slate-400">Total User(s)</span>
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    Staff Management is optional for solo store owners.
                </p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Store Staff:</span>
                <span class="font-bold text-[#1e2746]">{{ $totalStaff }} Staff Member(s)</span>
            </div>
        </div>

        <!-- Card 3: Payments & Invoices -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Payments Recorded</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                        💰
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#1e2746] tracking-tight mt-2">
                    ₹{{ number_format($totalPaidRevenue, 2) }}
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    {{ $totalPaidInvoices }} paid subscription transaction(s)
                </p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Billing Ledger:</span>
                <span class="font-bold text-emerald-600">Verified</span>
            </div>
        </div>

        <!-- Card 4: Store Operations Readiness -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">Operations State</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                        🏥
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-[#1e2746] tracking-tight mt-2">
                    Ready
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    Store is active & ready for Inventory & POS modules.
                </p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Architecture:</span>
                <span class="font-bold text-[#4b55c8]">Multi-Tenant Scoped</span>
            </div>
        </div>

    </div>

    <!-- 3. Middle Row: Subscription Details & Quick Actions Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Subscription Details & Quota Limits -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Subscription Deep-Dive Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Active Subscription & Plan Quotas</h3>
                        <p class="text-xs text-[#64748b] mt-0.5">Tier configuration and usage quotas enabled for {{ $store->name }}.</p>
                    </div>
                    @if ($activeSubscription)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#4b55c8] border border-blue-200 self-start sm:self-auto">
                            ● Active Subscription
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 self-start sm:self-auto">
                            No Subscription
                        </span>
                    @endif
                </div>

                @if ($activeSubscription && $activeSubscription->plan)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-5">
                        <div class="p-4 rounded-2xl bg-[#f8faff] border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 block">Plan Name</span>
                            <span class="text-lg font-bold text-[#1e2746] mt-1 block">{{ $activeSubscription->plan->name }}</span>
                            <span class="text-[11px] font-mono text-[#4b55c8]">₹{{ number_format($activeSubscription->plan->price) }} / {{ $activeSubscription->plan->billing_cycle->value ?? 'cycle' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#f8faff] border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 block">Max Products / Medicines</span>
                            <span class="text-lg font-bold text-[#1e2746] mt-1 block">
                                {{ method_exists($activeSubscription->plan, 'displayLimit') ? $activeSubscription->plan->displayLimit('max_medicines') : 'Unlimited' }}
                            </span>
                            <span class="text-[11px] text-slate-400">Inventory SKU limit</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#f8faff] border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 block">Max Staff Accounts</span>
                            <span class="text-lg font-bold text-[#1e2746] mt-1 block">
                                {{ method_exists($activeSubscription->plan, 'displayLimit') ? $activeSubscription->plan->displayLimit('max_staff') : 'Unlimited' }}
                            </span>
                            <span class="text-[11px] text-slate-400">Pharmacists & Cashiers</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-gradient-to-r from-[#f0f4ff] to-[#f8faff] border border-[#dce5f8] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-slate-700">
                                Subscription valid from <strong>{{ $activeSubscription->start_date->format('d M Y') }}</strong> to <strong>{{ $activeSubscription->end_date->format('d M Y') }}</strong>.
                            </span>
                        </div>
                        <span class="text-xs font-mono font-bold text-[#4b55c8]">
                            {{ max(0, (int) now()->diffInDays($activeSubscription->end_date, false)) }} Days Left
                        </span>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <p class="text-xs text-slate-500 font-medium">No active subscription plan assigned to your medical store.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Please contact your Platform Super Administrator to assign or renew a subscription tier.</p>
                    </div>
                @endif
            </div>

            <!-- Recent Payments Record Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Recent Subscription Payments</h3>
                        <p class="text-xs text-[#64748b] mt-0.5">Payment receipts and transaction records for this pharmacy.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 border-b border-slate-200/70">
                            <tr>
                                <th class="px-4 py-3 rounded-l-xl">Transaction ID</th>
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Method</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right rounded-r-xl">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse ($recentPayments as $payment)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 font-mono font-bold text-[#1e2746]">
                                        {{ $payment->transaction_id ?? 'TXN-'.$payment->id }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $payment->subscriptionPlan->name ?? ($payment->plan->name ?? 'Subscription') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-[#1e2746]">
                                        ₹{{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                            {{ $payment->payment_method->value ?? 'UPI' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $payment->status->value ?? 'Paid' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-slate-400">
                                        {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-xs text-slate-400">
                                        No payment transactions recorded for this store yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Col: Quick Actions & Platform Announcements & Activity -->
        <div class="space-y-6">
            
            <!-- Quick Actions Panel -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-base font-bold text-[#1e2746] tracking-tight mb-4">Quick Actions</h3>

                <div class="grid grid-cols-2 gap-3">
                    
                    <!-- 1. Add Medicine -->
                    <div class="p-3 rounded-2xl bg-[#f8faff] border border-slate-100 text-center flex flex-col items-center justify-center gap-2 cursor-not-allowed group">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center">
                            💊
                        </div>
                        <span class="text-xs font-bold text-[#1e2746]">Add Medicine</span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Phase 14</span>
                    </div>

                    <!-- 2. Create Sale / Billing -->
                    <div class="p-3 rounded-2xl bg-[#f8faff] border border-slate-100 text-center flex flex-col items-center justify-center gap-2 cursor-not-allowed group">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center">
                            🧾
                        </div>
                        <span class="text-xs font-bold text-[#1e2746]">POS Billing</span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Phase 14</span>
                    </div>

                    <!-- 3. Record Purchase -->
                    <div class="p-3 rounded-2xl bg-[#f8faff] border border-slate-100 text-center flex flex-col items-center justify-center gap-2 cursor-not-allowed group">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center">
                            📥
                        </div>
                        <span class="text-xs font-bold text-[#1e2746]">Add Purchase</span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 4. Add Customer -->
                    <div class="p-3 rounded-2xl bg-[#f8faff] border border-slate-100 text-center flex flex-col items-center justify-center gap-2 cursor-not-allowed group">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center">
                            👤
                        </div>
                        <span class="text-xs font-bold text-[#1e2746]">Add Customer</span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                </div>
            </div>

            <!-- Platform Broadcasts / Announcements -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-[#1e2746] tracking-tight">Announcements</h3>
                    <span class="text-[10px] font-bold text-[#4b55c8] bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                        {{ $notifications->count() }} Notice(s)
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($notifications as $notif)
                        <div class="p-3 rounded-2xl bg-[#f8faff] border border-slate-100 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-[#1e2746] truncate">{{ $notif->title }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-600 text-[11px] leading-relaxed">{{ $notif->message }}</p>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">
                            No platform announcements at this time.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Tenant Activity -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-base font-bold text-[#1e2746] tracking-tight mb-4">Store Activity Journal</h3>

                <div class="space-y-3">
                    @forelse ($recentActivities as $activity)
                        <div class="p-3 rounded-2xl bg-[#f8faff] border border-slate-100 text-xs">
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="font-bold text-[#1e2746] truncate max-w-[160px]">
                                    {{ $activity->user->name ?? 'System' }}
                                </span>
                                <span class="text-slate-400 font-mono text-[10px]">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-[#627094] line-clamp-1 leading-snug">{{ $activity->description }}</p>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">
                            No recent activity recorded for your account.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
