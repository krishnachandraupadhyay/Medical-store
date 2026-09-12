@extends('store.layouts.app')

@section('title', 'Role & Permission Management')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Access Control & Security
                </span>
                <span class="text-xs font-mono font-semibold text-slate-400">Phase 26</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Roles & Permissions</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Standard system roles and custom store roles with granular module-level authorization.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.staff.index') }}" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>View Staff Members</span>
            </a>

            <a href="{{ route('store.roles.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Custom Role</span>
            </a>
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

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($roles as $role)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between hover:border-slate-200 transition">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold {{ $role->is_system ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-[#4b55c8] border border-blue-200' }}">
                                {{ $role->is_system ? 'System Default' : 'Custom Store Role' }}
                            </span>
                            <h3 class="text-lg font-black text-[#1e2746] mt-1.5">{{ $role->name }}</h3>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-600">
                                {{ $role->users_count }} active staff
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed min-h-[36px]">
                        {{ $role->description ?: 'No detailed description provided for this role.' }}
                    </p>

                    <!-- Permissions Summary -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 mb-2">
                            <span>Permissions Granted</span>
                            <span class="font-bold text-slate-700">{{ $role->permissions->count() }} actions</span>
                        </div>
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                            @foreach ($role->permissions->take(8) as $perm)
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-50 border border-slate-200 text-slate-600">
                                    {{ $perm->name }}
                                </span>
                            @endforeach
                            @if ($role->permissions->count() > 8)
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-500">
                                    +{{ $role->permissions->count() - 8 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                    @if ($role->isSystem())
                        <span class="text-[11px] font-semibold text-slate-400 italic">
                            🔒 Protected System Role
                        </span>
                    @else
                        <div class="flex items-center gap-2 w-full justify-between">
                            <a href="{{ route('store.roles.edit', $role) }}" class="px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#4b55c8] font-bold text-xs transition">
                                Edit Permissions
                            </a>

                            <form method="POST" action="{{ route('store.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete this custom role?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition {{ $role->users_count > 0 ? 'opacity-40 cursor-not-allowed' : '' }}" {{ $role->users_count > 0 ? 'disabled title="Cannot delete role assigned to active staff"' : '' }}>
                                    Delete Role
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
