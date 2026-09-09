@extends('super-admin.layouts.app')

@section('title', 'Medical Stores Directory')
@section('page-title', 'Store Management')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Registered Medical Stores</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage tenant store profiles, business licenses, and platform operating status.</p>
        </div>

        <a href="{{ route('super-admin.stores.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 transition active:scale-[0.99] cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add New Store</span>
        </a>
    </div>

    <!-- Status Tabs & Search Filters Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 sm:p-5 shadow-lg space-y-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none text-xs">
                <a href="{{ route('super-admin.stores.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ empty($currentStatus) ? 'bg-teal-500/20 text-teal-300 border border-teal-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    All <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ route('super-admin.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ strtolower($currentStatus ?? '') === 'active' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    Active <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono text-emerald-400">{{ $counts['active'] }}</span>
                </a>
                <a href="{{ route('super-admin.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ strtolower($currentStatus ?? '') === 'inactive' ? 'bg-slate-500/20 text-slate-300 border border-slate-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    Inactive <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono">{{ $counts['inactive'] }}</span>
                </a>
                <a href="{{ route('super-admin.stores.index', array_merge(request()->except(['status', 'page']), ['status' => 'suspended'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ strtolower($currentStatus ?? '') === 'suspended' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    Suspended <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono text-rose-400">{{ $counts['suspended'] }}</span>
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('super-admin.stores.index') }}" method="GET" class="flex items-center gap-2">
                @if (!empty($currentStatus))
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative w-full md:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search name, code, mobile, city..."
                        class="w-full pl-9 pr-3 py-1.5 bg-slate-950/80 border border-slate-700/80 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-xl text-white placeholder-slate-500 text-xs focus:outline-none transition"
                    >
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold border border-slate-700 transition">
                    Search
                </button>
                @if (!empty($search))
                    <a href="{{ route('super-admin.stores.index', request()->only(['status'])) }}" class="p-1.5 text-slate-400 hover:text-rose-400" title="Clear Search">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Stores Data Table Card -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        @if ($stores->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/70 uppercase text-[10px] font-bold text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Store Code</th>
                            <th class="px-5 py-3.5">Store Name</th>
                            <th class="px-5 py-3.5">Assigned Owner</th>
                            <th class="px-5 py-3.5">Contact Mobile</th>
                            <th class="px-5 py-3.5">Location</th>
                            <th class="px-5 py-3.5">Store Type</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Created Date</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @foreach ($stores as $store)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4 font-mono font-bold text-teal-400">
                                    <a href="{{ route('super-admin.stores.show', $store) }}" class="hover:underline">
                                        {{ $store->code }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($store->logo)
                                            <img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}" class="w-8 h-8 rounded-lg object-cover bg-slate-800 border border-slate-700">
                                        @else
                                            <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 text-teal-400 font-bold flex items-center justify-center text-xs">
                                                {{ substr($store->name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('super-admin.stores.show', $store) }}" class="font-bold text-white hover:text-teal-300 transition block">
                                                {{ $store->name }}
                                            </a>
                                            <span class="text-[10px] text-slate-500">{{ $store->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-400">
                                    @if ($store->owners->isNotEmpty())
                                        <span class="font-semibold text-slate-200">{{ $store->owners->first()->name }}</span>
                                    @else
                                        <span class="text-slate-500 italic">Not assigned</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-300">{{ $store->mobile }}</td>
                                <td class="px-5 py-4">
                                    <span class="text-slate-200">{{ $store->city }}</span>,
                                    <span class="text-slate-500 text-[11px]">{{ $store->state }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700/60">
                                        {{ $store->store_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $store->status->badgeClasses() }}">
                                        {{ $store->status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                    {{ $store->created_at->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- View Details -->
                                        <a href="{{ route('super-admin.stores.show', $store) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-300 hover:bg-slate-800/80 transition" title="View Store">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('super-admin.stores.edit', $store) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-300 hover:bg-slate-800/80 transition" title="Edit Store">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-5 py-4 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400 bg-slate-950/40">
                <span>Showing {{ $stores->firstItem() ?? 0 }} to {{ $stores->lastItem() ?? 0 }} of {{ $stores->total() }} stores</span>
                <div>
                    {{ $stores->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="py-16 text-center p-6 flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-400 mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">No stores found</h3>
                <p class="text-xs text-slate-400 max-w-sm mt-1 mb-6">
                    @if (!empty($search) || !empty($currentStatus))
                        No medical store matching your current search or status filters. Try clearing your filters.
                    @else
                        No medical store tenants have been created yet on the platform. Add your first medical store to begin.
                    @endif
                </p>

                @if (!empty($search) || !empty($currentStatus))
                    <a href="{{ route('super-admin.stores.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition">
                        Clear All Filters
                    </a>
                @else
                    <a href="{{ route('super-admin.stores.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs shadow-lg shadow-teal-500/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Add First Store</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
