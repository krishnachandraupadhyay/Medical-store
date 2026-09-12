@extends('store.layouts.app')

@section('title', 'Staff Member — ' . $staff->name)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('store.staff.index') }}" class="hover:text-[#4b55c8] transition">Staff Management</a>
                <span>/</span>
                <span class="text-slate-600">{{ $staff->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">{{ $staff->name }}</h1>
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
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('store.staff.edit', $staff) }}" class="px-4 py-2.5 rounded-xl bg-blue-50 text-[#4b55c8] hover:bg-blue-100 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit Profile</span>
            </a>

            <form method="POST" action="{{ route('store.staff.toggle-status', $staff) }}" class="inline" onsubmit="return confirm('Are you sure you want to {{ $staff->isActive() ? 'deactivate' : 'activate' }} this staff member?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2.5 rounded-xl {{ $staff->isActive() ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }} font-bold text-xs sm:text-sm transition">
                    {{ $staff->isActive() ? 'Deactivate Account' : 'Activate Account' }}
                </button>
            </form>
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

    <!-- Profile Overview Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Staff Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center font-black text-2xl shadow-sm">
                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-black text-[#1e2746]">{{ $staff->name }}</h2>
                    <p class="text-xs text-slate-400">Staff Account</p>
                </div>
            </div>

            <div class="space-y-3 pt-3 border-t border-slate-100 text-xs">
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-semibold">Email:</span>
                    <span class="text-slate-800 font-bold">{{ $staff->email }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-semibold">Mobile:</span>
                    <span class="text-slate-800 font-bold">{{ $staff->mobile ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-semibold">Assigned Role:</span>
                    <span class="font-bold text-[#4b55c8]">{{ $staff->staffRole?->name ?? 'None' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-semibold">Last Login:</span>
                    <span class="text-slate-700">{{ $staff->last_login_at ? $staff->last_login_at->format('M d, Y h:i A') : 'Never logged in' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-semibold">Created By:</span>
                    <span class="text-slate-700">{{ $staff->creator?->name ?? 'Store Administrator' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-semibold">Member Since:</span>
                    <span class="text-slate-700">{{ $staff->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <!-- Quick Password Reset -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Reset Staff Password</h4>
                <form method="POST" action="{{ route('store.staff.reset-password', $staff) }}" class="space-y-3">
                    @csrf
                    <div>
                        <input type="password" name="password" required placeholder="New Password" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#4b55c8]"/>
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" required placeholder="Confirm New Password" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#4b55c8]"/>
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition shadow-sm">
                        Reset Password Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Role & Permissions View -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-[#1e2746]">Assigned Role & Permissions</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $staff->staffRole?->name ?? 'No Role' }} — {{ $staff->staffRole?->description ?? 'No description provided.' }}
                    </p>
                </div>
                @if ($staff->staffRole)
                    <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $staff->staffRole->is_system ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-[#4b55c8] border border-blue-200' }}">
                        {{ $staff->staffRole->is_system ? 'Standard System Role' : 'Custom Role' }}
                    </span>
                @endif
            </div>

            <!-- Permissions Badges Grouped -->
            @if ($staff->staffRole && $staff->staffRole->permissions->isNotEmpty())
                <div class="space-y-4">
                    @php
                        $groupedPerms = $staff->staffRole->permissions->groupBy('group');
                    @endphp

                    @foreach ($groupedPerms as $group => $perms)
                        <div class="p-3.5 rounded-2xl bg-slate-50/75 border border-slate-100">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                                <span>{{ $group }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">({{ $perms->count() }} actions)</span>
                            </h4>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($perms as $perm)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-white border border-slate-200 text-slate-700 shadow-2xs" title="{{ $perm->description }}">
                                        ✓ {{ $perm->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200 text-slate-400 text-xs">
                    No permissions active for this role.
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Audit History & Activities -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-black text-[#1e2746]">Activity & Audit Trail</h3>
                <p class="text-xs text-slate-500 mt-0.5">Historical timeline of actions performed by or affecting this staff account.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-400">{{ $recentActivities->count() }} recent events</span>
        </div>

        <div class="space-y-3">
            @forelse ($recentActivities as $activity)
                <div class="p-3.5 rounded-2xl bg-slate-50/75 border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-bold text-sm shadow-2xs">
                            {{ $activity->action->label() === 'Logged In' ? '🔑' : '📝' }}
                        </span>
                        <div>
                            <p class="font-bold text-slate-800">{{ $activity->description }}</p>
                            <p class="text-[11px] text-slate-400">Actor: {{ $activity->user?->name ?? 'System' }} • IP: {{ $activity->ip_address ?? '—' }}</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-500 font-semibold whitespace-nowrap">
                        {{ $activity->created_at->diffForHumans() }}
                    </span>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No recorded activity logs for this user yet.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
