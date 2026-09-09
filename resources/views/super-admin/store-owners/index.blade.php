@extends('super-admin.layouts.app')

@section('title', 'Store Owners Directory')
@section('page-title', 'Store Owner Management')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Registered Store Owners</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage tenant primary administrators, store assignments, and account credentials.</p>
        </div>

        <a href="{{ route('super-admin.store-owners.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 transition active:scale-[0.99] cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Add Store Owner</span>
        </a>
    </div>

    <!-- Status Tabs & Filter Controls Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 sm:p-5 shadow-lg space-y-4">
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none text-xs">
                <a href="{{ route('super-admin.store-owners.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ empty($currentStatus) ? 'bg-teal-500/20 text-teal-300 border border-teal-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    All Owners <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ route('super-admin.store-owners.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ strtolower($currentStatus ?? '') === 'active' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    Active <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono text-emerald-400">{{ $counts['active'] }}</span>
                </a>
                <a href="{{ route('super-admin.store-owners.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                   class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ strtolower($currentStatus ?? '') === 'inactive' ? 'bg-slate-500/20 text-slate-300 border border-slate-500/40 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    Inactive <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-slate-800 font-mono">{{ $counts['inactive'] }}</span>
                </a>
            </div>

            <!-- Filters Form (Store Filter & Search) -->
            <form action="{{ route('super-admin.store-owners.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                @if (!empty($currentStatus))
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif

                <!-- Store Filter Dropdown -->
                <select
                    name="store_id"
                    onchange="this.form.submit()"
                    class="px-3 py-1.5 bg-slate-950/80 border border-slate-700/80 focus:border-teal-500 rounded-xl text-slate-300 text-xs focus:outline-none"
                >
                    <option value="">All Stores</option>
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" {{ $selectedStoreId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->code }})
                        </option>
                    @endforeach
                </select>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search name, email, mobile, store..."
                        class="w-full pl-9 pr-3 py-1.5 bg-slate-950/80 border border-slate-700/80 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-xl text-white placeholder-slate-500 text-xs focus:outline-none transition"
                    >
                </div>

                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold border border-slate-700 transition">
                    Search
                </button>

                @if (!empty($search) || !empty($selectedStoreId))
                    <a href="{{ route('super-admin.store-owners.index', request()->only(['status'])) }}" class="p-1.5 text-slate-400 hover:text-rose-400 self-center" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Store Owners Data Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        @if ($storeOwners->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/70 uppercase text-[10px] font-bold text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Owner Name</th>
                            <th class="px-5 py-3.5">Email Address</th>
                            <th class="px-5 py-3.5">Contact Mobile</th>
                            <th class="px-5 py-3.5">Assigned Medical Store</th>
                            <th class="px-5 py-3.5">Account Status</th>
                            <th class="px-5 py-3.5">Created Date</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @foreach ($storeOwners as $owner)
                            <tr class="hover:bg-slate-800/40 transition">
                                <!-- Name -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 text-indigo-300 font-bold flex items-center justify-center text-xs">
                                            {{ strtoupper(substr($owner->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('super-admin.store-owners.show', $owner) }}" class="font-bold text-white hover:text-teal-300 transition block">
                                                {{ $owner->name }}
                                            </a>
                                            <span class="text-[10px] font-mono text-slate-500">ID: #{{ $owner->id }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="px-5 py-4 font-mono text-slate-300">{{ $owner->email }}</td>

                                <!-- Mobile -->
                                <td class="px-5 py-4 font-mono text-slate-300">{{ $owner->mobile ?? '—' }}</td>

                                <!-- Store (Link to Store Details) -->
                                <td class="px-5 py-4">
                                    @if ($owner->store)
                                        <a href="{{ route('super-admin.stores.show', $owner->store) }}" class="group flex items-center gap-1.5 hover:underline">
                                            <span class="font-semibold text-white group-hover:text-teal-300">{{ $owner->store->name }}</span>
                                            <span class="font-mono text-[10px] text-teal-400 bg-teal-500/10 px-1.5 py-0.2 rounded border border-teal-500/20">
                                                {{ $owner->store->code }}
                                            </span>
                                        </a>
                                    @else
                                        <span class="text-slate-500 italic">No store assigned</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-4">
                                    @if ($owner->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Created -->
                                <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                    {{ $owner->created_at->format('d M Y') }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- View Details -->
                                        <a href="{{ route('super-admin.store-owners.show', $owner) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-300 hover:bg-slate-800/80 transition" title="View Store Owner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('super-admin.store-owners.edit', $owner) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-300 hover:bg-slate-800/80 transition" title="Edit Store Owner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Status Toggle Form -->
                                        <form action="{{ route('super-admin.store-owners.update-status', $owner) }}" method="POST" onsubmit="return confirm('Are you sure you want to {{ $owner->is_active ? 'DEACTIVATE' : 'ACTIVATE' }} this Store Owner?');" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $owner->is_active ? '0' : '1' }}">
                                            <button type="submit" class="p-1.5 rounded-lg {{ $owner->is_active ? 'text-amber-400 hover:bg-amber-500/10' : 'text-emerald-400 hover:bg-emerald-500/10' }} transition cursor-pointer" title="{{ $owner->is_active ? 'Deactivate' : 'Activate' }}">
                                                @if ($owner->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-5 py-4 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400 bg-slate-950/40">
                <span>Showing {{ $storeOwners->firstItem() ?? 0 }} to {{ $storeOwners->lastItem() ?? 0 }} of {{ $storeOwners->total() }} Store Owners</span>
                <div>
                    {{ $storeOwners->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="py-16 text-center p-6 flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-400 mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">No Store Owners found</h3>
                <p class="text-xs text-slate-400 max-w-sm mt-1 mb-6">
                    @if (!empty($search) || !empty($currentStatus) || !empty($selectedStoreId))
                        No Store Owner accounts match your current filter criteria. Try clearing your filters.
                    @else
                        No Store Owners have been registered yet. Add a Store Owner and assign them to a medical store.
                    @endif
                </p>

                @if (!empty($search) || !empty($currentStatus) || !empty($selectedStoreId))
                    <a href="{{ route('super-admin.store-owners.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition">
                        Clear All Filters
                    </a>
                @else
                    <a href="{{ route('super-admin.store-owners.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs shadow-lg shadow-teal-500/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Add First Store Owner</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
