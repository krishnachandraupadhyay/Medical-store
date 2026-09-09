@extends('super-admin.layouts.app')

@section('title', 'System Audit Logs')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs font-semibold text-[#4b55c8] uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                Security & Compliance
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1e2746] tracking-tight">System Audit Logs</h1>
            <p class="text-xs sm:text-sm text-[#627094]">Complete chronological trace of platform administrative activities, configuration modifications, and security events.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-[#4b55c8] border border-blue-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Immutable Trail
            </span>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Logs -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#4b55c8] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Recorded Events</span>
                <span class="text-2xl font-black text-[#1e2746]">{{ number_format($metrics['total_logs']) }}</span>
            </div>
        </div>

        <!-- Today's Events -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Events Today</span>
                <span class="text-2xl font-black text-[#1e2746]">{{ number_format($metrics['today_logs']) }}</span>
            </div>
        </div>

        <!-- Active Administrators -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Active Admins</span>
                <span class="text-2xl font-black text-[#1e2746]">{{ number_format($metrics['active_admins']) }}</span>
            </div>
        </div>

        <!-- Top Active Module -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Top Activity Module</span>
                <span class="text-xl font-black text-[#1e2746] truncate max-w-[150px] block">{{ $metrics['top_module'] }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
        <form action="{{ route('super-admin.audit-logs.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-4 relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search description, IP, admin name..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Module Filter -->
                <div class="lg:col-span-2">
                    <select name="module" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white text-slate-600 font-medium">
                        <option value="">All Modules</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod->value }}" {{ $module === $mod->value ? 'selected' : '' }}>
                                {{ $mod->icon() }} {{ $mod->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div class="lg:col-span-2">
                    <select name="action" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white text-slate-600 font-medium">
                        <option value="">All Actions</option>
                        @foreach($actions as $act)
                            <option value="{{ $act->value }}" {{ $action === $act->value ? 'selected' : '' }}>
                                {{ $act->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Admin User Filter -->
                <div class="lg:col-span-2">
                    <select name="user_id" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white text-slate-600 font-medium">
                        <option value="">All Admins</option>
                        @foreach($admins as $adminUser)
                            <option value="{{ $adminUser->id }}" {{ (string)$userId === (string)$adminUser->id ? 'selected' : '' }}>
                                {{ $adminUser->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Preset Filter -->
                <div class="lg:col-span-2">
                    <select name="date_preset" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white text-slate-600 font-medium">
                        <option value="">Any Date</option>
                        <option value="today" {{ $datePreset === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $datePreset === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="7_days" {{ $datePreset === '7_days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30_days" {{ $datePreset === '30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span>Showing {{ $auditLogs->firstItem() ?? 0 }} - {{ $auditLogs->lastItem() ?? 0 }} of {{ $auditLogs->total() }} records</span>
                </div>
                <div class="flex items-center gap-2">
                    @if(!empty($search) || !empty($module) || !empty($action) || !empty($userId) || !empty($datePreset) || !empty($dateFrom) || !empty($dateTo))
                        <a href="{{ route('super-admin.audit-logs.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition">
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#1e2746]">
                <thead class="bg-[#f8faff] text-[11px] font-bold text-[#627094] uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Timestamp</th>
                        <th class="py-4 px-6">Administrator</th>
                        <th class="py-4 px-6">Action</th>
                        <th class="py-4 px-6">Module</th>
                        <th class="py-4 px-6">Activity Description</th>
                        <th class="py-4 px-6">IP Address</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Timestamp -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="font-bold text-[#1e2746]">{{ $log->created_at->format(setting('date_format', 'Y-m-d')) }}</div>
                                <div class="text-[11px] text-[#627094]">{{ $log->created_at->format(setting('time_format', 'h:i A')) }}</div>
                            </td>

                            <!-- Administrator -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($log->user)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-bold text-[#1e2746]">{{ $log->user->name }}</span>
                                            <span class="block text-[11px] text-[#627094]">{{ $log->user->email }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                        System Automated
                                    </span>
                                @endif
                            </td>

                            <!-- Action Badge -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($log->action)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $log->action->badgeClasses() }}">
                                        {{ $log->action->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                        Action
                                    </span>
                                @endif
                            </td>

                            <!-- Module Badge -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($log->module)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold border {{ $log->module->badgeClasses() }}">
                                        <span>{{ $log->module->icon() }}</span>
                                        <span>{{ $log->module->label() }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Description & Subject -->
                            <td class="py-4 px-6">
                                <div class="font-semibold text-[#1e2746] max-w-md truncate" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </div>
                                @if($log->subject_type)
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Target: <span class="font-medium text-slate-600">{{ $log->subject_display_name }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="py-4 px-6 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                <a href="{{ route('super-admin.audit-logs.show', $log) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-[#4b55c8] bg-blue-50 hover:bg-blue-100 transition">
                                    <span>Details</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-16 h-16 rounded-3xl bg-blue-50 text-[#4b55c8] flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-[#1e2746] mb-1">No Audit Logs Found</h3>
                                <p class="text-xs text-[#627094] max-w-sm mx-auto">No administrative events match your search criteria. Try modifying your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($auditLogs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
