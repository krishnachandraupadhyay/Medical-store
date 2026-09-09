@extends('super-admin.layouts.app')

@section('title', $owner->name . ' - Store Owner Details')
@section('page-title', 'Store Owner Profile')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.store-owners.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4b55c8] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Store Owners Directory</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.store-owners.edit', $owner) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#4b55c8] border border-blue-200 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Profile</span>
            </a>
        </div>
    </div>

    <!-- Store Owner Hero Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-7 shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] text-white font-extrabold flex items-center justify-center text-2xl shadow-md shadow-[#4b55c8]/25">
                    {{ strtoupper(substr($owner->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">{{ $owner->name }}</h2>
                        @if ($owner->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>
                                Inactive
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 font-mono">
                        <span class="text-[#4b55c8] font-semibold">{{ $owner->email }}</span>
                        <span>•</span>
                        <span>STORE_OWNER</span>
                        @if ($owner->store)
                            <span>•</span>
                            <a href="{{ route('super-admin.stores.show', $owner->store) }}" class="text-[#4b55c8] hover:underline font-bold">
                                {{ $owner->store->name }} ({{ $owner->store->code }})
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Action Form -->
            <div class="flex items-center gap-2 pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                <form action="{{ route('super-admin.store-owners.update-status', $owner) }}" method="POST" onsubmit="return confirm('Are you sure you want to {{ $owner->is_active ? 'DEACTIVATE' : 'ACTIVATE' }} this Store Owner?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $owner->is_active ? '0' : '1' }}">
                    <button type="submit" class="px-4 py-2 rounded-xl {{ $owner->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }} text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        @if ($owner->is_active)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Deactivate Owner</span>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Activate Owner</span>
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: Personal & Contact -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Personal & Contact Information
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Full Name</span>
                    <span class="text-[#1e2746] font-semibold">{{ $owner->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Email Address</span>
                    <span class="text-[#1e2746] font-mono">{{ $owner->email }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Mobile Number</span>
                    <span class="text-[#1e2746] font-mono font-semibold">{{ $owner->mobile ?? '—' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">System Role</span>
                    <span class="text-[#4b55c8] font-bold font-mono">STORE_OWNER</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Assigned Store -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Assigned Medical Store
            </h3>

            @if ($owner->store)
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Store Name</span>
                        <a href="{{ route('super-admin.stores.show', $owner->store) }}" class="text-[#4b55c8] font-bold hover:underline">
                            {{ $owner->store->name }}
                        </a>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Store Code</span>
                        <span class="text-[#4b55c8] font-mono font-bold">{{ $owner->store->code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Store Operating Status</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $owner->store->status->badgeClasses() }}">
                            {{ $owner->store->status->label() }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Location</span>
                        <span class="text-[#1e2746] font-medium">{{ $owner->store->city }}, {{ $owner->store->state }}</span>
                    </div>
                </div>
            @else
                <div class="py-6 text-center text-xs text-slate-400">
                    No medical store is currently associated with this owner account.
                </div>
            @endif
        </div>

        <!-- Card 3: Account Governance & Security -->
        <div class="sm:col-span-2 bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Account Governance & Timestamps
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                    <span class="text-slate-400 block text-[11px]">User Record ID</span>
                    <span class="text-[#1e2746] font-mono font-bold mt-1 block">#{{ $owner->id }}</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Registered On</span>
                    <span class="text-[#1e2746] font-mono font-bold mt-1 block">{{ $owner->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-[#f8faff] border border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Last Updated</span>
                    <span class="text-[#1e2746] font-mono font-bold mt-1 block">{{ $owner->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
