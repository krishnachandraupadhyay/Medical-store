@extends('super-admin.layouts.app')

@section('title', 'Edit Store Owner - ' . $owner->name)
@section('page-title', 'Edit Store Owner')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.store-owners.show', $owner) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Store Owner Details</span>
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-slate-900/90 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-800/80 bg-slate-950/40">
            <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">Edit Store Owner Profile</h2>
            <p class="text-xs text-slate-400 mt-0.5">Modify profile details, reassign medical store, or reset authentication credentials.</p>
        </div>

        <form action="{{ route('super-admin.store-owners.update', $owner) }}" method="POST" class="p-6 sm:p-8 space-y-7">
            @csrf
            @method('PUT')

            <!-- Section 1: Personal Details -->
            <div>
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    1. Personal & Contact Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Full Name <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $owner->name) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('name') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('name')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Email Address <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $owner->email) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('email') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('email')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label for="mobile" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Contact Mobile <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="mobile"
                            id="mobile"
                            value="{{ old('mobile', $owner->mobile) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('mobile') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('mobile')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Store Assignment & Status -->
            <div class="pt-6 border-t border-slate-800/80">
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    2. Store Assignment & Governance
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Store Selector -->
                    <div>
                        <label for="store_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Assigned Medical Store <span class="text-rose-400">*</span>
                        </label>
                        <select
                            name="store_id"
                            id="store_id"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('store_id') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            @foreach ($stores as $s)
                                <option value="{{ $s->id }}" {{ old('store_id', $owner->store_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->code }}) - [{{ $s->status->value }}]
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="is_active" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Account Status <span class="text-rose-400">*</span>
                        </label>
                        <select
                            name="is_active"
                            id="is_active"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('is_active') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            <option value="1" {{ old('is_active', $owner->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active (Authorized)</option>
                            <option value="0" {{ old('is_active', $owner->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive (Deactivated)</option>
                        </select>
                        @error('is_active')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Password Reset (Optional) -->
            <div class="pt-6 border-t border-slate-800/80">
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-1 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    3. Reset Password (Optional)
                </h3>
                <p class="text-[11px] text-slate-500 mb-4">Leave both password fields blank if you do not want to change the password.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            New Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Leave blank to keep unchanged"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('password') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('password')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Confirm New Password
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Re-type new password"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.store-owners.show', $owner) }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 transition cursor-pointer"
                >
                    Update Store Owner Profile
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
