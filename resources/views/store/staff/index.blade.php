@extends('store.layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Store Operations & RBAC
                </span>
                <span class="text-xs font-mono font-semibold text-slate-400">Phase 26</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Staff Management</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Manage pharmacy staff accounts, assign granular role-based permissions, and monitor active sessions.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('store.roles.index') }}" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Roles & Permissions</span>
            </a>

            @if ($isQuotaUnlimited || $quotaRemaining > 0)
                <a href="{{ route('store.staff.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add New Staff</span>
                </a>
            @else
                <button disabled title="Staff quota reached" class="px-5 py-2.5 rounded-2xl bg-slate-300 text-slate-500 font-bold text-xs sm:text-sm cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-8a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Quota Full (Max {{ $quotaLimit }})</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Quota & Subscription Status Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Staff Registered</p>
                <p class="text-xl font-black text-[#1e2746] mt-1">{{ $staffMembers->total() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                👨‍⚕️
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan Staff Quota</p>
                <p class="text-xl font-black text-[#1e2746] mt-1">
                    @if ($isQuotaUnlimited)
                        Unlimited
                    @else
                        {{ $quotaLimit }} accounts
                    @endif
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                📊
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Available Capacity</p>
                <p class="text-xl font-black {{ ($quotaRemaining === 0 && ! $isQuotaUnlimited) ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                    @if ($isQuotaUnlimited)
                        Unlimited
                    @else
                        {{ $quotaRemaining }} remaining
                    @endif
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl {{ ($quotaRemaining === 0 && ! $isQuotaUnlimited) ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-[#4b55c8]' }} flex items-center justify-center font-bold">
                ⚡
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.staff.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..." class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
            </div>

            <div>
                <select name="role_id" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition">
                    <option value="">All Staff Roles</option>
                    @foreach ($availableRoles as $role)
                        <option value="{{ $role->id }}" {{ (string) request('role_id') === (string) $role->id ? 'selected' : '' }}>
                            {{ $role->name }} {{ $role->is_system ? '(System)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Accounts</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Deactivated Accounts</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-sm">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'role_id', 'status']))
                    <a href="{{ route('store.staff.index') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs sm:text-sm rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Staff Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Staff Member</th>
                        <th class="px-5 py-3.5">Contact</th>
                        <th class="px-5 py-3.5">Assigned Role</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Last Login</th>
                        <th class="px-5 py-3.5">Joined Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($staffMembers as $staff)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center font-black text-sm">
                                        {{ strtoupper(substr($staff->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('store.staff.show', $staff) }}" class="font-bold text-[#1e2746] hover:text-[#4b55c8] transition">
                                            {{ $staff->name }}
                                        </a>
                                        <p class="text-[11px] text-slate-400 font-normal">ID: #STF-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-xs text-slate-700 font-semibold">{{ $staff->email }}</p>
                                <p class="text-[11px] text-slate-400">{{ $staff->mobile ?: '—' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if ($staff->staffRole)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold {{ $staff->staffRole->is_system ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-[#4b55c8] border border-blue-200' }}">
                                        {{ $staff->staffRole->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-500">
                                        No Role Assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($staff->isActive())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $staff->last_login_at ? $staff->last_login_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $staff->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('store.staff.show', $staff) }}" title="View Activity" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <a href="{{ route('store.staff.edit', $staff) }}" title="Edit Details" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('store.staff.toggle-status', $staff) }}" class="inline" onsubmit="return confirm('Are you sure you want to {{ $staff->isActive() ? 'deactivate' : 'activate' }} this staff member?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="{{ $staff->isActive() ? 'Deactivate Account' : 'Activate Account' }}" class="p-2 {{ $staff->isActive() ? 'text-amber-600 hover:text-amber-800 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50' }} rounded-lg transition">
                                            @if ($staff->isActive())
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl">
                                        👥
                                    </div>
                                    <p class="font-bold text-slate-600 text-sm">No staff members found</p>
                                    <p class="text-xs text-slate-400">Get started by creating staff accounts and assigning role permissions.</p>
                                    @if ($isQuotaUnlimited || $quotaRemaining > 0)
                                        <a href="{{ route('store.staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#4b55c8] text-white text-xs font-bold hover:bg-[#3f49b8] transition">
                                            Add Staff Member
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($staffMembers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $staffMembers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
