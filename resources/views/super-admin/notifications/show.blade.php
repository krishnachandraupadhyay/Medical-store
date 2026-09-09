@extends('super-admin.layouts.app')

@section('title', 'Notification - ' . $notification->title)
@section('page-title', 'Notification Details')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.notifications.index') }}" class="hover:text-[#4b55c8] transition">Notifications</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold truncate max-w-xs">{{ $notification->title }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight flex items-center gap-3">
                <span>{{ $notification->title }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $notification->status->badgeClasses() }}">
                    {{ $notification->status->label() }}
                </span>
            </h2>
        </div>

        <div class="flex items-center gap-2">
            @if ($notification->canBeEdited())
                <a href="{{ route('super-admin.notifications.edit', $notification) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit</span>
                </a>
            @endif

            @if ($notification->canBeCancelled())
                <form action="{{ route('super-admin.notifications.cancel', $notification) }}" method="POST" onsubmit="return confirm('Cancel this scheduled notification?');" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs transition">
                        Cancel Schedule
                    </button>
                </form>
            @endif

            <a href="{{ route('super-admin.notifications.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
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
    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Message Preview Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
        
        <!-- Header Tags -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-bold border {{ $notification->type->badgeClasses() }}">
                    {{ $notification->type->icon() }} {{ $notification->type->label() }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $notification->priority->badgeClasses() }}">
                    {{ $notification->priority->label() }} Priority
                </span>
            </div>

            <div class="text-xs text-slate-400 font-mono">
                ID: #{{ $notification->id }}
            </div>
        </div>

        <!-- Notification Message Body -->
        <div class="space-y-2">
            <h3 class="text-lg sm:text-xl font-bold text-[#1e2746] tracking-tight">
                {{ $notification->title }}
            </h3>
            <div class="p-5 rounded-2xl bg-[#f8faff] border border-slate-100 text-slate-700 text-sm leading-relaxed whitespace-pre-line font-medium">
                {{ $notification->message }}
            </div>
        </div>

    </div>

    <!-- 3-Column Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- 1. Audience Targeting -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Audience Target
            </h3>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Target Scope</span>
                    <span class="font-bold text-slate-800">{{ $notification->target_type->label() }}</span>
                </div>

                @if ($notification->target_type === \App\Enums\NotificationTargetType::SPECIFIC_STORE && $notification->store)
                    <div>
                        <span class="text-slate-400 block text-[11px]">Selected Store</span>
                        <a href="{{ route('super-admin.stores.show', $notification->store) }}" class="font-bold text-[#4b55c8] hover:underline">
                            {{ $notification->store->name }}
                        </a>
                        <span class="text-[10px] text-slate-400 block font-mono">{{ $notification->store->code }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Store Location</span>
                        <span class="text-slate-700">{{ $notification->store->city }}, {{ $notification->store->state }}</span>
                    </div>
                @else
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 text-[11px]">
                        Broadcast message intended for all active store owners on the platform.
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Delivery & Scheduling Timeline -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Delivery Status
            </h3>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Current Status</span>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $notification->status->badgeClasses() }}">
                        {{ $notification->status->label() }}
                    </span>
                </div>

                @if ($notification->isSent())
                    <div>
                        <span class="text-slate-400 block text-[11px]">Sent / Published At</span>
                        <span class="font-mono font-semibold text-emerald-700">{{ $notification->sent_at?->format('M d, Y h:i:s A') }}</span>
                    </div>
                @elseif ($notification->isScheduled())
                    <div>
                        <span class="text-slate-400 block text-[11px]">Scheduled Delivery</span>
                        <span class="font-mono font-semibold text-amber-700">{{ $notification->scheduled_at?->format('M d, Y h:i A') }}</span>
                    </div>
                @else
                    <div>
                        <span class="text-slate-400 block text-[11px]">Draft Mode</span>
                        <span class="text-slate-500 italic">Not scheduled or delivered.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. Audit Trail -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Audit Trail
            </h3>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Author / Created By</span>
                    <span class="font-bold text-slate-800">{{ $notification->creator?->name ?? 'Super Admin' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Created Date</span>
                    <span class="font-mono text-slate-600">{{ $notification->created_at->format('M d, Y h:i A') }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Last Modified</span>
                    <span class="font-mono text-slate-600">{{ $notification->updated_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
