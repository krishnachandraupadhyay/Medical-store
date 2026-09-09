@extends('super-admin.layouts.app')

@section('title', 'Add New Medical Store')
@section('page-title', 'Create Store')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.stores.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4b55c8] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Stores Directory</span>
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 bg-[#f8faff]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-[#1e2746] tracking-tight">Register New Medical Store</h2>
                    <p class="text-xs text-[#64748b] mt-0.5">Fill in the required business details to provision a new medical store.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-blue-50 border border-blue-200 text-[#4b55c8] text-xs font-mono font-bold">
                    <span class="text-slate-400 text-[10px]">AUTO-CODE:</span>
                    <span>{{ $previewCode }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('super-admin.stores.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8">
            @csrf

            <!-- Section 1: Basic Information -->
            <div>
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    1. Basic Store Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    
                    <!-- Store Name -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Store Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="e.g. Apex Care Pharmacy"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('name') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('name')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Store Contact Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Official Email Address
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="store@domain.com"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('email') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('email')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Store Type -->
                    <div>
                        <label for="store_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Store Classification <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="store_type"
                            id="store_type"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('store_type') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                            <option value="Retail" {{ old('store_type') == 'Retail' ? 'selected' : '' }}>Retail Pharmacy</option>
                            <option value="Wholesale" {{ old('store_type') == 'Wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                            <option value="Hospital Pharmacy" {{ old('store_type') == 'Hospital Pharmacy' ? 'selected' : '' }}>Hospital Pharmacy</option>
                            <option value="Both" {{ old('store_type') == 'Both' ? 'selected' : '' }}>Retail & Wholesale (Both)</option>
                        </select>
                        @error('store_type')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Primary Mobile -->
                    <div>
                        <label for="mobile" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Primary Mobile Number <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="mobile"
                            id="mobile"
                            value="{{ old('mobile') }}"
                            required
                            placeholder="+91 9876543210"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('mobile') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('mobile')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alternate Mobile -->
                    <div>
                        <label for="alternate_mobile" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alternate Mobile / Landline
                        </label>
                        <input
                            type="text"
                            name="alternate_mobile"
                            id="alternate_mobile"
                            value="{{ old('alternate_mobile') }}"
                            placeholder="+91 9123456780"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('alternate_mobile') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('alternate_mobile')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Store Logo -->
                    <div class="sm:col-span-2">
                        <label for="logo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Store Branding / Logo <span class="text-slate-400 font-normal">(PNG, JPG, WEBP, max 2MB)</span>
                        </label>
                        <input
                            type="file"
                            name="logo"
                            id="logo"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                            class="w-full px-3 py-2 bg-[#f8faff] border @error('logo') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-600 text-xs file:mr-4 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#4b55c8] hover:file:bg-blue-100 cursor-pointer"
                        >
                        @error('logo')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Section 2: Address & Location -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    2. Address & Physical Location
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <!-- Street Address -->
                    <div class="sm:col-span-3">
                        <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Street Address / Building / Area
                        </label>
                        <textarea
                            name="address"
                            id="address"
                            rows="2"
                            placeholder="e.g. Shop No. 14, Metro Commercial Complex"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('address') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            City <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="city"
                            id="city"
                            value="{{ old('city') }}"
                            required
                            placeholder="e.g. Mumbai"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('city') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('city')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- State -->
                    <div>
                        <label for="state" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            State <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="state"
                            id="state"
                            value="{{ old('state') }}"
                            required
                            placeholder="e.g. Maharashtra"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('state') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('state')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label for="pincode" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Pincode / Postal Code <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="pincode"
                            id="pincode"
                            value="{{ old('pincode') }}"
                            required
                            placeholder="e.g. 400001"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('pincode') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('pincode')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Business & Compliance -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    3. Business & Licensing Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <!-- GSTIN -->
                    <div>
                        <label for="gstin" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            GSTIN
                        </label>
                        <input
                            type="text"
                            name="gstin"
                            id="gstin"
                            value="{{ old('gstin') }}"
                            placeholder="e.g. 27ABCDE1234F1Z5"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('gstin') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition uppercase"
                        >
                        @error('gstin')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Drug License Number -->
                    <div>
                        <label for="drug_license_no" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Drug License No. (DL)
                        </label>
                        <input
                            type="text"
                            name="drug_license_no"
                            id="drug_license_no"
                            value="{{ old('drug_license_no') }}"
                            placeholder="e.g. MH-MZ-20B-123456"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('drug_license_no') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('drug_license_no')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- License Expiry Date -->
                    <div>
                        <label for="license_expiry_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            License Expiry Date
                        </label>
                        <input
                            type="date"
                            name="license_expiry_date"
                            id="license_expiry_date"
                            value="{{ old('license_expiry_date') }}"
                            class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('license_expiry_date') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                        >
                        @error('license_expiry_date')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 4: Initial Status -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    4. Initial Operational Status
                </h3>

                <div class="max-w-xs">
                    <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Operating Status <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        required
                        class="w-full px-3.5 py-2.5 bg-[#f8faff] border @error('status') border-rose-300 @else border-slate-200 focus:border-[#4b55c8] focus:ring-[#4b55c8]/20 focus:bg-white @enderror rounded-xl text-[#1e2746] text-xs sm:text-sm focus:outline-none focus:ring-2 transition"
                    >
                        <option value="ACTIVE" {{ old('status', 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>Active (Operational)</option>
                        <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive (Deactivated)</option>
                        <option value="SUSPENDED" {{ old('status') == 'SUSPENDED' ? 'selected' : '' }}>Suspended (Blocked)</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.stores.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition cursor-pointer"
                >
                    Save & Create Store
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
