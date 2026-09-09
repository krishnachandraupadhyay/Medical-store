@extends('super-admin.layouts.app')

@section('title', 'Audit Log Details #' . $auditLog->id)

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.audit-logs.index') }}"
               class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-[#627094]">Event #{{ $auditLog->id }}</span>
                    @if($auditLog->action)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $auditLog->action->badgeClasses() }}">
                            {{ $auditLog->action->label() }}
                        </span>
                    @endif
                    @if($auditLog->module)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-xl text-[11px] font-bold border {{ $auditLog->module->badgeClasses() }}">
                            <span>{{ $auditLog->module->icon() }}</span>
                            <span>{{ $auditLog->module->label() }}</span>
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-[#1e2746] tracking-tight">{{ $auditLog->description }}</h1>
            </div>
        </div>
        <div class="text-right">
            <span class="text-xs font-bold text-slate-400 block uppercase tracking-wider">Recorded At</span>
            <span class="text-xs font-semibold text-[#1e2746]">{{ $auditLog->created_at->format(setting('date_format', 'Y-m-d').' '.setting('time_format', 'h:i:s A')) }}</span>
            <span class="text-[11px] text-[#627094] block">{{ $auditLog->created_at->diffForHumans() }}</span>
        </div>
    </div>

    <!-- Telemetry & Actor Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Administrator Actor -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-[#1e2746]">Actor Information</h3>
            </div>

            @if($auditLog->user)
                <div class="space-y-3 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                            {{ strtoupper(substr($auditLog->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="block font-bold text-[#1e2746] text-sm">{{ $auditLog->user->name }}</span>
                            <span class="block text-slate-500 text-xs">{{ $auditLog->user->email }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">User ID:</span>
                            <span class="font-bold text-[#1e2746]">#{{ $auditLog->user->id }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Platform Role:</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 uppercase">
                                {{ $auditLog->user->role->value ?? 'Super Admin' }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 bg-slate-50 rounded-2xl text-center text-xs text-slate-500 font-medium">
                    Automated System Daemon
                </div>
            @endif
        </div>

        <!-- Card 2: Subject Entity -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-[#1e2746]">Subject Target</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px] mb-0.5">Target Identifier</span>
                    <span class="font-bold text-[#1e2746] text-sm">{{ $auditLog->subject_display_name }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Subject Class:</span>
                        <span class="font-mono text-[11px] text-slate-600 truncate max-w-[160px]" title="{{ $auditLog->subject_type }}">
                            {{ class_basename($auditLog->subject_type ?? 'Global System') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Subject ID:</span>
                        <span class="font-mono text-[11px] text-slate-600">
                            {{ $auditLog->subject_id ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Network & Client Telemetry -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-[#1e2746]">Client Telemetry</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">IP Address:</span>
                    <span class="font-mono font-bold text-[#1e2746]">{{ $auditLog->ip_address ?? '127.0.0.1' }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 space-y-1">
                    <span class="text-slate-400 block text-[11px]">User Agent Browser String:</span>
                    <p class="font-mono text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-xl break-all">
                        {{ $auditLog->user_agent ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Before / After Changes Inspector -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f8faff]">
            <div>
                <h3 class="text-base font-extrabold text-[#1e2746]">Field-Level Activity Diff</h3>
                <p class="text-xs text-[#627094]">Comparison of state changes recorded before and after this event.</p>
            </div>
            <span class="text-[11px] font-semibold text-slate-400 inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Sensitive keys (passwords, tokens) are excluded
            </span>
        </div>

        @php
            $diffs = $auditLog->diffs;
        @endphp

        @if(!empty($diffs))
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-[#627094] uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6 w-1/4">Field / Parameter</th>
                            <th class="py-3.5 px-6 w-3/8 text-rose-700 bg-rose-50/50">Previous Value (Old)</th>
                            <th class="py-3.5 px-6 w-3/8 text-emerald-700 bg-emerald-50/50">Updated Value (New)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @foreach($diffs as $field => $diff)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-bold font-sans text-[#1e2746] bg-slate-50/30">
                                    {{ ucwords(str_replace('_', ' ', $field)) }}
                                    <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $field }}</span>
                                </td>
                                <td class="py-3.5 px-6 text-slate-700 bg-rose-50/20 break-all">
                                    @if(is_array($diff['old']))
                                        <pre class="text-[10px] whitespace-pre-wrap">{{ json_encode($diff['old'], JSON_PRETTY_PRINT) }}</pre>
                                    @elseif(is_null($diff['old']))
                                        <span class="text-slate-400 italic">null</span>
                                    @elseif(is_bool($diff['old']))
                                        <span class="font-bold">{{ $diff['old'] ? 'true' : 'false' }}</span>
                                    @else
                                        {{ (string)$diff['old'] }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-[#1e2746] bg-emerald-50/20 font-bold break-all">
                                    @if(is_array($diff['new']))
                                        <pre class="text-[10px] whitespace-pre-wrap">{{ json_encode($diff['new'], JSON_PRETTY_PRINT) }}</pre>
                                    @elseif(is_null($diff['new']))
                                        <span class="text-slate-400 italic font-normal">null</span>
                                    @elseif(is_bool($diff['new']))
                                        <span class="text-emerald-700">{{ $diff['new'] ? 'true' : 'false' }}</span>
                                    @else
                                        <span class="text-emerald-700">{{ (string)$diff['new'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(!empty($auditLog->new_values))
            <!-- Initial Creation Payload -->
            <div class="p-6 space-y-3">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Initial Payload (New Attributes)</h4>
                <div class="bg-slate-900 text-slate-100 p-4 rounded-2xl font-mono text-xs overflow-x-auto">
                    <pre>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @else
            <div class="p-8 text-center text-xs text-slate-500">
                No discrete field-level payload modified for this standard event.
            </div>
        @endif
    </div>

</div>
@endsection
