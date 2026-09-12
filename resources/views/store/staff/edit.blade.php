@extends('store.layouts.app')

@section('title', 'Edit Staff Member — ' . $staff->name)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl mx-auto">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('store.staff.index') }}" class="hover:text-[#4b55c8] transition">Staff Management</a>
                <span>/</span>
                <a href="{{ route('store.staff.show', $staff) }}" class="hover:text-[#4b55c8] transition">{{ $staff->name }}</a>
                <span>/</span>
                <span class="text-slate-600">Edit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">Edit Staff Profile</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Update account details, change role assignments, or modify active status.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('store.staff.show', $staff) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs sm:text-sm transition">
                View Profile
            </a>
        </div>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
            <p class="font-bold mb-1">Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Edit Form -->
    <form method="POST" action="{{ route('store.staff.update', $staff) }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $staff->name) }}" required class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Email Address <span class="text-rose-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $staff->email) }}" required class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
            </div>

            <!-- Mobile -->
            <div>
                <label for="mobile" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Mobile / Phone Number
                </label>
                <input type="text" id="mobile" name="mobile" value="{{ old('mobile', $staff->mobile) }}" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
            </div>

            <!-- Assigned Role -->
            <div>
                <label for="role_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Assigned Role <span class="text-rose-500">*</span>
                </label>
                <select id="role_id" name="role_id" required class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition">
                    @foreach ($availableRoles as $role)
                        <option value="{{ $role->id }}" {{ (string) old('role_id', $staff->role_id) === (string) $role->id ? 'selected' : '' }}>
                            {{ $role->name }} {{ $role->is_system ? '— Standard System Role' : '— Custom Role' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="border-slate-100 my-2">

        <!-- Optional Password Reset -->
        <div>
            <h3 class="text-sm font-bold text-slate-800 mb-1">Change Password (Optional)</h3>
            <p class="text-xs text-slate-500 mb-4">Leave password fields blank if you do not want to alter the current password.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        New Password
                    </label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep unchanged" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Confirm New Password
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat new password" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
                </div>
            </div>
        </div>

        <!-- Account Status Toggle -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-bold text-slate-800">Account Status</p>
                <p class="text-[11px] sm:text-xs text-slate-500">When disabled, this user will be blocked from logging in immediately.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
            </label>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('store.staff.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs sm:text-sm hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <span>Update Staff Account</span>
            </button>
        </div>
    </form>

</div>
@endsection
