@extends('super-admin.layouts.app')

@section('title', 'Notification Management')
@section('page-title', 'Platform Notifications')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Notification Management</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Broadcast announcements, system alerts, and subscription warnings to tenant store owners.</p>
        </div>

        <a href="{{ route('super-admin.notifications.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition active:scale-[0.99] cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Create Notification</span>
        </a>
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

    <!-- Metrics Summary Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Notifications</span>
            <span class="text-xl font-bold text-[#1e2746] mt-0.5 block">{{ number_format($stats['total']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-600 block">Sent / Published</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ number_format($stats['sent']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-amber-600 block">Scheduled</span>
            <span class="text-xl font-bold text-amber-700 mt-0.5 block">{{ number_format($stats['scheduled']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Drafts</span>
            <span class="text-xl font-bold text-slate-700 mt-0.5 block">{{ number_format($stats['draft']) }}</span>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm">
            <span class="text-[11px] font-semibold text-rose-600 block">Cancelled</span>
            <span class="text-xl font-bold text-rose-700 mt-0.5 block">{{ number_format($stats['cancelled']) }}</span>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('super-admin.notifications.index') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- Search -->
                <div class="lg:col-span-3 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search title, message, store..."
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition" />
                </div>

                <!-- Type Filter -->
                <div class="lg:col-span-2">
                    <select name="type" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Types</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" {{ $type === $t->value ? 'selected' : '' }}>
                                {{ $t->icon() }} {{ $t->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="lg:col-span-2">
                    <select name="priority" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Priorities</option>
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}" {{ $priority === $p->value ? 'selected' : '' }}>
                                {{ $p->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ $status === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Type Filter -->
                <div class="lg:col-span-2">
                    <select name="target_type" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                        <option value="">All Targets</option>
                        @foreach ($targetTypes as $tt)
                            <option value="{{ $tt->value }}" {{ $targetType === $tt->value ? 'selected' : '' }}>
                                {{ $tt->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Button -->
                <div class="lg:col-span-1 flex items-center gap-1">
                    <button type="submit" class="w-full px-3 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                        Filter
                    </button>
                    @if ($search || $type || $priority || $status || $targetType || $dateFrom || $dateTo)
                        <a href="{{ route('super-admin.notifications.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition" title="Clear Filters">
                            ✕
                        </a>
                    @endif
                </div>

            </div>

            <!-- Date Range Filter Row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Created Date:</span>
                <div class="flex items-center gap-2">
                    <label for="date_from" class="text-[11px]">From:</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                </div>
                <div class="flex items-center gap-2">
                    <label for="date_to" class="text-[11px]">To:</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8]" />
                </div>
            </div>
        </form>
    </div>

    <!-- Notifications Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                    <tr>
                        <th class="px-4 py-3.5">Notification Title</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Priority</th>
                        <th class="px-4 py-3.5">Target Audience</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Delivery Time</th>
                        <th class="px-4 py-3.5">Created By</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($notifications as $notif)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Title & Preview -->
                            <td class="px-4 py-3.5 max-w-xs">
                                <a href="{{ route('super-admin.notifications.show', $notif) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] block truncate">
                                    {{ $notif->title }}
                                </a>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ $notif->message }}</p>
                            </td>

                            <!-- Type -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $notif->type->badgeClasses() }}">
                                    {{ $notif->type->icon() }} {{ $notif->type->label() }}
                                </span>
                            </td>

                            <!-- Priority -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $notif->priority->badgeClasses() }}">
                                    {{ $notif->priority->label() }}
                                </span>
                            </td>

                            <!-- Target -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($notif->target_type === \App\Enums\NotificationTargetType::SPECIFIC_STORE)
                                    <div class="text-[#4b55c8] font-bold">{{ $notif->store?->name ?? 'Specific Store' }}</div>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $notif->store?->code ?? '' }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        All Stores
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $notif->status->badgeClasses() }}">
                                    {{ $notif->status->label() }}
                                </span>
                            </td>

                            <!-- Delivery Date -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                @if ($notif->isSent())
                                    <span class="text-emerald-700">Sent: {{ $notif->sent_at?->format('M d, Y h:i A') ?? '—' }}</span>
                                @elseif ($notif->isScheduled())
                                    <span class="text-amber-700">Sch: {{ $notif->scheduled_at?->format('M d, Y h:i A') ?? '—' }}</span>
                                @else
                                    <span class="text-slate-400">Draft</span>
                                @endif
                            </td>

                            <!-- Created By -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                                {{ $notif->creator?->name ?? 'Super Admin' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('super-admin.notifications.show', $notif) }}" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#4b55c8] transition" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if ($notif->canBeEdited())
                                        <a href="{{ route('super-admin.notifications.edit', $notif) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Edit Notification">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if ($notif->canBeCancelled())
                                        <form action="{{ route('super-admin.notifications.cancel', $notif) }}" method="POST" onsubmit="return confirm('Cancel this scheduled notification?');" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition" title="Cancel Scheduled Notification">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($notif->canBeDeleted())
                                        <form action="{{ route('super-admin.notifications.destroy', $notif) }}" method="POST" onsubmit="return confirm('Delete this notification record?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="Delete Draft Notification">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="text-2xl">🔔</div>
                                    <div class="text-sm font-bold text-[#1e2746]">No notification records found</div>
                                    <p class="text-xs text-slate-400">Broadcast a new announcement or alert to tenant stores.</p>
                                    <a href="{{ route('super-admin.notifications.create') }}" class="mt-2 px-4 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white text-xs font-bold transition">
                                        Create Notification
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($notifications->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
