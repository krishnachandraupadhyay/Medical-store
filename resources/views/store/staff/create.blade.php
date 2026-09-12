@extends('store.layouts.app')

@section('title', 'Add New Staff Member')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl mx-auto">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('store.staff.index') }}" class="hover:text-[#4b55c8] transition">Staff Management</a>
                <span>/</span>
                <span class="text-slate-600">Add Staff</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">Create Staff Account</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Register a new pharmacy team member with secure login credentials and role permissions.
            </p>
        </div>

        <a href="{{ route('store.staff.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs sm:text-sm transition flex items-center gap-1.5">
            ✕ Cancel
        </a>
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

    <!-- Staff Creation Form -->
    <form method="POST" action="{{ route('store.staff.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Ramesh Verma" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition @error('name') border-rose-300 bg-rose-50/50 @enderror"/>
                @error('name')
                    <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Email Address (Login Username) <span class="text-rose-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="ramesh@pharmacy.com" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition @error('email') border-rose-300 bg-rose-50/50 @enderror"/>
                @error('email')
                    <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile / Phone -->
            <div>
                <label for="mobile" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Mobile / Contact Phone
                </label>
                <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="+91 9876543210" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition @error('mobile') border-rose-300 bg-rose-50/50 @enderror"/>
                @error('mobile')
                    <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Assigned Role <span class="text-rose-500">*</span>
                </label>
                <select id="role_id" name="role_id" required class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition @error('role_id') border-rose-300 bg-rose-50/50 @enderror">
                    <option value="">Select a Role</option>
                    @foreach ($availableRoles as $role)
                        <option value="{{ $role->id }}" {{ (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>
                            {{ $role->name }} {{ $role->is_system ? '— Standard System Role' : '— Custom Role' }} ({{ $role->permissions->count() }} permissions)
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <hr class="border-slate-100 my-2">

        <!-- Security / Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Password <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="password" name="password" required placeholder="Minimum 8 characters" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition @error('password') border-rose-300 bg-rose-50/50 @enderror"/>
                @error('password')
                    <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Confirm Password <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
            </div>
        </div>

        <!-- Initial Status -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-bold text-slate-800">Account Status: Active</p>
                <p class="text-[11px] sm:text-xs text-slate-500">Allow this staff member to log in immediately upon creation.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('store.staff.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs sm:text-sm hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <span>Save Staff Account</span>
            </button>
        </div>
    </form>

</div>
@endsection
