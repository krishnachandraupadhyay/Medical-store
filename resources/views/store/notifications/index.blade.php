@extends('store.layouts.app')

@section('page-title', 'Notification Center')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header & Action Summary -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Notification Center</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Stay informed on low stock, expiring batches, payment dues, and store transactions.</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($unreadCount > 0)
                <form action="{{ route('store.notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs sm:text-sm font-bold transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Mark All as Read</span>
                    </button>
                </form>
            @endif
            <a href="{{ route('store.notifications.preferences') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold shadow-sm transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Preferences</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Notifications -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Alerts</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($totalCount) }}</p>
            </div>
        </div>

        <!-- Unread Notifications -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unread Alerts</p>
                <p class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ number_format($unreadCount) }}</p>
            </div>
        </div>

        <!-- Read Notifications -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Archived / Read</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ number_format($readCount) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters and Search Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" action="{{ route('store.notifications.index') }}" class="space-y-4">
            <!-- Status Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <a href="{{ route('store.notifications.index', array_merge(request()->except('status'), ['status' => 'all'])) }}" class="px-4 py-1.5 rounded-xl text-xs font-bold transition {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    All ({{ $totalCount }})
                </a>
                <a href="{{ route('store.notifications.index', array_merge(request()->except('status'), ['status' => 'unread'])) }}" class="px-4 py-1.5 rounded-xl text-xs font-bold transition {{ $statusFilter === 'unread' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    Unread ({{ $unreadCount }})
                </a>
                <a href="{{ route('store.notifications.index', array_merge(request()->except('status'), ['status' => 'read'])) }}" class="px-4 py-1.5 rounded-xl text-xs font-bold transition {{ $statusFilter === 'read' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    Read ({{ $readCount }})
                </a>
            </div>

            <!-- Search and Dropdowns -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search alert title or message..." class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div class="sm:col-span-3">
                    <select name="type" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-700">
                        <option value="">All Alert Types</option>
                        @foreach ($notificationTypes as $type)
                            <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <select name="priority" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-700">
                        <option value="">All Priorities</option>
                        @foreach ($priorities as $pri)
                            <option value="{{ $pri->value }}" {{ request('priority') === $pri->value ? 'selected' : '' }}>{{ ucfirst($pri->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <button type="submit" class="flex-1 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer">Filter</button>
                    @if (request()->hasAny(['search', 'type', 'priority']) || request('status', 'all') !== 'all')
                        <a href="{{ route('store.notifications.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center" title="Reset Filters">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Notification List Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if ($notifications->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach ($notifications as $notification)
                    <div class="p-4 sm:p-5 transition hover:bg-slate-50/70 {{ $notification->isUnread() ? 'bg-indigo-50/15' : '' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5 min-w-0 flex-1">
                            <!-- Type Icon -->
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $notification->type?->badgeClasses() ?? 'bg-slate-100 text-slate-700' }} shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>

                            <!-- Notification Content -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">{{ $notification->title }}</h3>
                                    
                                    @if ($notification->isUnread())
                                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200">Unread</span>
                                    @endif

                                    <!-- Priority Badge -->
                                    @php
                                        $priorityClass = match($notification->priority?->value) {
                                            'critical' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            'high', 'urgent' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'important' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            'low' => 'bg-slate-100 text-slate-600 border-slate-200',
                                            default => 'bg-blue-50 text-blue-700 border-blue-200',
                                        };
                                    @endphp
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $priorityClass }}">
                                        {{ ucfirst($notification->priority?->value ?? 'normal') }}
                                    </span>

                                    <!-- Category Badge -->
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                        {{ $notification->type?->category() ?? 'General' }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed">{{ $notification->message }}</p>

                                <div class="flex items-center gap-3 text-[11px] text-slate-400 mt-2">
                                    <span>{{ $notification->created_at?->format('d M Y, h:i A') }}</span>
                                    <span>•</span>
                                    <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                    @if ($notification->read_at)
                                        <span>•</span>
                                        <span class="text-emerald-600 font-medium">Read {{ $notification->read_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                            @if ($notification->action_url)
                                <a href="{{ route('store.notifications.show', $notification->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition">
                                    <span>View Action</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif

                            @if ($notification->isUnread())
                                <form action="{{ route('store.notifications.mark-read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition cursor-pointer" title="Mark as Read">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($notifications->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-indigo-50 text-indigo-500 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No notifications found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">There are no alerts matching your current filter criteria. You're all caught up!</p>
                <div class="mt-4">
                    <a href="{{ route('store.notifications.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Clear Filters</a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
