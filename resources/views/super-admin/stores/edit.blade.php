@extends('super-admin.layouts.app')

@section('title', 'Edit Store - ' . $store->name)
@section('page-title', 'Edit Store')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.stores.show', $store) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Store Details</span>
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-slate-900/90 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-800/80 bg-slate-950/40">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">Edit Store Profile</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Modify store profile, business licenses, and platform operating status.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-xs font-mono">
                    <span class="text-slate-500 text-[10px]">STORE CODE:</span>
                    <span class="font-bold text-teal-400">{{ $store->code }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('super-admin.stores.update', $store) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Information -->
            <div>
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    1. Basic Store Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    
                    <!-- Permanent Store Code (Read-Only) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                            Store Code <span class="text-slate-500 font-normal">(Permanent)</span>
                        </label>
                        <input
                            type="text"
                            value="{{ $store->code }}"
                            disabled
                            class="w-full px-3.5 py-2 bg-slate-950/40 border border-slate-800 rounded-xl text-slate-400 font-mono text-xs sm:text-sm cursor-not-allowed"
                        >
                    </div>

                    <!-- Store Classification -->
                    <div>
                        <label for="store_type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Store Classification <span class="text-rose-400">*</span>
                        </label>
                        <select
                            name="store_type"
                            id="store_type"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('store_type') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            <option value="Retail" {{ old('store_type', $store->store_type) == 'Retail' ? 'selected' : '' }}>Retail Pharmacy</option>
                            <option value="Wholesale" {{ old('store_type', $store->store_type) == 'Wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                            <option value="Hospital Pharmacy" {{ old('store_type', $store->store_type) == 'Hospital Pharmacy' ? 'selected' : '' }}>Hospital Pharmacy</option>
                            <option value="Both" {{ old('store_type', $store->store_type) == 'Both' ? 'selected' : '' }}>Retail & Wholesale (Both)</option>
                        </select>
                        @error('store_type')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Store Name -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Store Name <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $store->name) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('name') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('name')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Store Contact Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Official Email Address
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $store->email) }}"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('email') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('email')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Primary Mobile -->
                    <div>
                        <label for="mobile" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Primary Mobile Number <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="mobile"
                            id="mobile"
                            value="{{ old('mobile', $store->mobile) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('mobile') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('mobile')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alternate Mobile -->
                    <div>
                        <label for="alternate_mobile" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Alternate Mobile
                        </label>
                        <input
                            type="text"
                            name="alternate_mobile"
                            id="alternate_mobile"
                            value="{{ old('alternate_mobile', $store->alternate_mobile) }}"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('alternate_mobile') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('alternate_mobile')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Logo & Logo Upload -->
                    <div class="sm:col-span-2">
                        <label for="logo" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Store Logo <span class="text-slate-500 font-normal">(Leave empty to retain current logo)</span>
                        </label>
                        <div class="flex items-center gap-4">
                            @if ($store->logo)
                                <img src="{{ asset('storage/'.$store->logo) }}" alt="Logo" class="w-12 h-12 rounded-xl object-cover bg-slate-800 border border-slate-700">
                            @endif
                            <input
                                type="file"
                                name="logo"
                                id="logo"
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                class="w-full px-3 py-2 bg-slate-950/80 border @error('logo') border-rose-500 @else border-slate-700/80 @enderror rounded-xl text-slate-300 text-xs file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-500/20 file:text-teal-300 hover:file:bg-teal-500/30 cursor-pointer"
                            >
                        </div>
                        @error('logo')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Section 2: Address -->
            <div class="pt-6 border-t border-slate-800/80">
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    2. Address Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="sm:col-span-3">
                        <label for="address" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Street Address
                        </label>
                        <textarea
                            name="address"
                            id="address"
                            rows="2"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('address') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >{{ old('address', $store->address) }}</textarea>
                        @error('address')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            City <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="city"
                            id="city"
                            value="{{ old('city', $store->city) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('city') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('city')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="state" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            State <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="state"
                            id="state"
                            value="{{ old('state', $store->state) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('state') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('state')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pincode" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Pincode <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="pincode"
                            id="pincode"
                            value="{{ old('pincode', $store->pincode) }}"
                            required
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('pincode') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('pincode')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Business & Licensing -->
            <div class="pt-6 border-t border-slate-800/80">
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    3. Business & Licensing Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="gstin" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            GSTIN
                        </label>
                        <input
                            type="text"
                            name="gstin"
                            id="gstin"
                            value="{{ old('gstin', $store->gstin) }}"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('gstin') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition uppercase"
                        >
                        @error('gstin')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="drug_license_no" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Drug License No.
                        </label>
                        <input
                            type="text"
                            name="drug_license_no"
                            id="drug_license_no"
                            value="{{ old('drug_license_no', $store->drug_license_no) }}"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('drug_license_no') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('drug_license_no')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="license_expiry_date" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            License Expiry Date
                        </label>
                        <input
                            type="date"
                            name="license_expiry_date"
                            id="license_expiry_date"
                            value="{{ old('license_expiry_date', $store->license_expiry_date ? $store->license_expiry_date->format('Y-m-d') : '') }}"
                            class="w-full px-3.5 py-2 bg-slate-950/80 border @error('license_expiry_date') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('license_expiry_date')
                            <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 4: Status -->
            <div class="pt-6 border-t border-slate-800/80">
                <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    4. Operational Status
                </h3>

                <div class="max-w-xs">
                    <label for="status" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Operating Status <span class="text-rose-400">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        required
                        class="w-full px-3.5 py-2 bg-slate-950/80 border @error('status') border-rose-500 @else border-slate-700/80 focus:border-teal-500 focus:ring-teal-500/20 @enderror rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                    >
                        <option value="ACTIVE" {{ old('status', $store->status->value) == 'ACTIVE' ? 'selected' : '' }}>Active (Operational)</option>
                        <option value="INACTIVE" {{ old('status', $store->status->value) == 'INACTIVE' ? 'selected' : '' }}>Inactive (Deactivated)</option>
                        <option value="SUSPENDED" {{ old('status', $store->status->value) == 'SUSPENDED' ? 'selected' : '' }}>Suspended (Blocked)</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.stores.show', $store) }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 transition cursor-pointer"
                >
                    Update Store Profile
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
