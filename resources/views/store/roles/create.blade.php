@extends('store.layouts.app')

@section('title', 'Create Custom Role')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('store.staff.index') }}" class="hover:text-[#4b55c8] transition">Staff Management</a>
                <span>/</span>
                <a href="{{ route('store.roles.index') }}" class="hover:text-[#4b55c8] transition">Roles</a>
                <span>/</span>
                <span class="text-slate-600">Create Custom Role</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">New Store Role</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Define a tailored role with specific granular permissions across pharmacy modules.
            </p>
        </div>

        <a href="{{ route('store.roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs sm:text-sm transition">
            ✕ Cancel
        </a>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
            <p class="font-bold mb-1">Please fix the following issues:</p>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('store.roles.store') }}" class="space-y-6">
        @csrf

        <!-- Basic Role Information -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-base font-black text-[#1e2746] border-b border-slate-100 pb-3">Role Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Role Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Lead Cashier or Senior Pharmacist" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Short Description
                    </label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}" placeholder="Brief summary of duties and responsibilities" class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:border-[#4b55c8] transition"/>
                </div>
            </div>
        </div>

        <!-- Permissions Matrix -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-black text-[#1e2746]">Module Permissions Matrix</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Select the specific actions this role is authorized to perform.</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <button type="button" onclick="toggleAllCheckboxes(true)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                        Select All
                    </button>
                    <button type="button" onclick="toggleAllCheckboxes(false)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                        Clear All
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($permissionsByGroup as $group => $permissions)
                    <div class="p-4 rounded-2xl bg-slate-50/75 border border-slate-100 space-y-3" id="group-{{ Str::slug($group) }}">
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                                <span>{{ $group }}</span>
                            </span>
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <button type="button" onclick="toggleGroupCheckboxes('{{ Str::slug($group) }}', true)" class="text-[#4b55c8] hover:underline font-semibold">
                                    All
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" onclick="toggleGroupCheckboxes('{{ Str::slug($group) }}', false)" class="text-slate-500 hover:underline font-semibold">
                                    None
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @foreach ($permissions as $permission)
                                <label class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-white transition cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->slug }}" {{ is_array(old('permissions')) && in_array($permission->slug, old('permissions')) ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-[#4b55c8] focus:ring-[#4b55c8] group-checkbox-{{ Str::slug($group) }}">
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">{{ $permission->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $permission->slug }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Submit Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('store.roles.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs sm:text-sm hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Save Custom Role
            </button>
        </div>
    </form>

</div>

<script>
    function toggleGroupCheckboxes(groupSlug, check) {
        document.querySelectorAll('.group-checkbox-' + groupSlug).forEach(function(cb) {
            cb.checked = check;
        });
    }

    function toggleAllCheckboxes(check) {
        document.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(function(cb) {
            cb.checked = check;
        });
    }
</script>
@endsection
