@extends('store.layouts.app')

@section('title', 'Store Profile & Settings')
@section('page-title', 'Store Profile & Settings')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- Top Summary Banner & System Badges -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#eef3fe] via-[#eaf0fc] to-[#e4ecfb] border border-[#dce5f8] p-6 sm:p-7 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
        <div class="z-10 max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100/90 text-[#4b55c8] text-xs font-bold mb-3 border border-blue-200/60">
                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                <span>Pharmacy Configuration & Profile</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#1e2746] tracking-tight">
                {{ $store->name }}
            </h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1.5 font-medium leading-relaxed">
                Configure your store identification, address, drug license, invoice preferences, and store owner credentials.
            </p>
        </div>

        <!-- Read-Only System Controls (Store Code & Status) -->
        <div class="bg-white/95 backdrop-blur rounded-2xl border border-white shadow-lg p-4 w-full md:w-auto min-w-[280px] flex flex-col gap-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">System Code</span>
                <span class="font-mono font-black text-sm text-[#4b55c8] bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200">
                    {{ $store->code }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Store Status</span>
                @if ($store->isActive())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                @elseif ($store->isSuspended())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Suspended
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Inactive
                    </span>
                @endif
            </div>
            <p class="text-[10px] text-slate-400 text-center italic">
                * Store Code & Status are managed exclusively by Super Admin.
            </p>
        </div>
    </div>

    <!-- License Expiry Warning Alerts -->
    @if ($isExpired)
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 sm:p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-xl font-bold">
                ⚠️
            </div>
            <div>
                <h4 class="text-sm font-bold text-rose-800">Drug License Has Expired!</h4>
                <p class="text-xs text-rose-700 mt-1">
                    Your Drug License (<strong>{{ $store->drug_license_no ?? 'N/A' }}</strong>) expired on 
                    <strong>{{ $store->license_expiry_date?->format('d M Y') }}</strong> ({{ abs($daysToExpiry) }} days ago). 
                    Please update your license credentials immediately to avoid regulatory non-compliance.
                </p>
            </div>
        </div>
    @elseif ($isExpiringSoon)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 sm:p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 text-xl font-bold">
                ⏳
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-800">Drug License Expiring Soon!</h4>
                <p class="text-xs text-amber-700 mt-1">
                    Your Drug License (<strong>{{ $store->drug_license_no ?? 'N/A' }}</strong>) will expire in 
                    <strong>{{ $daysToExpiry }} days</strong> (on <strong>{{ $store->license_expiry_date?->format('d M Y') }}</strong>). 
                    Please initiate license renewal with regulatory authorities.
                </p>
            </div>
        </div>
    @endif

    <!-- Main Grid: Settings Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Store Profile, Address, Legal, Preferences -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Section 1 & 2 & 3: Store Profile, Contact, Address, Legal & Preferences Form -->
            <form action="{{ route('store.settings.update-profile') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm space-y-7">
                @csrf
                @method('PUT')

                <!-- 1. Basic Information -->
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#4b55c8] flex items-center justify-center text-sm font-bold">
                            1
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#1e2746]">Store Profile</h3>
                            <p class="text-xs text-slate-400">Basic pharmacy details and trading type.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Store Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $store->name) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                            @error('name')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="store_type" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Store Type <span class="text-rose-500">*</span>
                            </label>
                            <select id="store_type" name="store_type" required
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('store_type') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition bg-white">
                                <option value="Retail" {{ old('store_type', $store->store_type) === 'Retail' ? 'selected' : '' }}>Retail Pharmacy</option>
                                <option value="Wholesale" {{ old('store_type', $store->store_type) === 'Wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                                <option value="Retail + Wholesale" {{ old('store_type', $store->store_type) === 'Retail + Wholesale' || old('store_type', $store->store_type) === 'Both' ? 'selected' : '' }}>Retail + Wholesale</option>
                                <option value="Hospital Pharmacy" {{ old('store_type', $store->store_type) === 'Hospital Pharmacy' ? 'selected' : '' }}>Hospital Pharmacy</option>
                            </select>
                            @error('store_type')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- 2. Contact Information -->
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#4b55c8] flex items-center justify-center text-sm font-bold">
                            2
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#1e2746]">Contact Information</h3>
                            <p class="text-xs text-slate-400">Communication lines for customers and suppliers.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="mobile" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Primary Mobile <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="mobile" name="mobile" value="{{ old('mobile', $store->mobile) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('mobile') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                            @error('mobile')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="alternate_mobile" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alternate Mobile (Optional)
                            </label>
                            <input type="text" id="alternate_mobile" name="alternate_mobile" value="{{ old('alternate_mobile', $store->alternate_mobile) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('alternate_mobile') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                            @error('alternate_mobile')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Store Email
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $store->email) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                            @error('email')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- 3. Store Address -->
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#4b55c8] flex items-center justify-center text-sm font-bold">
                            3
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#1e2746]">Store Physical Address</h3>
                            <p class="text-xs text-slate-400">Street address, city, state and postal code.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="address" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Street Address / Shop No.
                            </label>
                            <textarea id="address" name="address" rows="2"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('address') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">{{ old('address', $store->address) }}</textarea>
                            @error('address')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="city" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    City <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="city" name="city" value="{{ old('city', $store->city) }}" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('city') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                                @error('city')
                                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="state" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    State <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="state" name="state" value="{{ old('state', $store->state) }}" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('state') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                                @error('state')
                                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="pincode" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Pincode <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="pincode" name="pincode" value="{{ old('pincode', $store->pincode) }}" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('pincode') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                                @error('pincode')
                                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- 4. Legal Information -->
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#4b55c8] flex items-center justify-center text-sm font-bold">
                            4
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#1e2746]">Legal & Compliance Information</h3>
                            <p class="text-xs text-slate-400">GSTIN and mandatory Drug License registration.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="gstin" class="block text-xs font-bold text-slate-700 mb-1.5">
                                GSTIN (Tax ID)
                            </label>
                            <input type="text" id="gstin" name="gstin" value="{{ old('gstin', $store->gstin) }}" placeholder="e.g. 09ABCDE1234F1Z5"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('gstin') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition uppercase">
                            @error('gstin')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="drug_license_no" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Drug License Number
                            </label>
                            <input type="text" id="drug_license_no" name="drug_license_no" value="{{ old('drug_license_no', $store->drug_license_no) }}" placeholder="e.g. DL-UP-2026-001"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('drug_license_no') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition uppercase">
                            @error('drug_license_no')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="license_expiry_date" class="block text-xs font-bold text-slate-700 mb-1.5">
                                License Expiry Date
                            </label>
                            <input type="date" id="license_expiry_date" name="license_expiry_date" value="{{ old('license_expiry_date', $store->license_expiry_date?->format('Y-m-d')) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('license_expiry_date') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                            @error('license_expiry_date')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- 5. Store Preferences -->
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#4b55c8] flex items-center justify-center text-sm font-bold">
                            5
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#1e2746]">Store Preferences & Billing Settings</h3>
                            <p class="text-xs text-slate-400">Localization and sales invoice configuration.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="timezone" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Timezone
                            </label>
                            <select id="timezone" name="timezone"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition bg-white">
                                <option value="Asia/Kolkata" {{ old('timezone', $store->timezone()) === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                <option value="UTC" {{ old('timezone', $store->timezone()) === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Asia/Dubai" {{ old('timezone', $store->timezone()) === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                <option value="Asia/Singapore" {{ old('timezone', $store->timezone()) === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT)</option>
                            </select>
                        </div>

                        <div>
                            <label for="currency" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Currency
                            </label>
                            <select id="currency" name="currency"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition bg-white">
                                <option value="INR" {{ old('currency', $store->currency()) === 'INR' ? 'selected' : '' }}>INR (₹ - Indian Rupee)</option>
                                <option value="USD" {{ old('currency', $store->currency()) === 'USD' ? 'selected' : '' }}>USD ($ - US Dollar)</option>
                                <option value="EUR" {{ old('currency', $store->currency()) === 'EUR' ? 'selected' : '' }}>EUR (€ - Euro)</option>
                                <option value="AED" {{ old('currency', $store->currency()) === 'AED' ? 'selected' : '' }}>AED (د.إ - Dirham)</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_format" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Date Format
                            </label>
                            <select id="date_format" name="date_format"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition bg-white">
                                <option value="d-m-Y" {{ old('date_format', $store->dateFormat()) === 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY ({{ now()->format('d-m-Y') }})</option>
                                <option value="d/m/Y" {{ old('date_format', $store->dateFormat()) === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ now()->format('d/m/Y') }})</option>
                                <option value="Y-m-d" {{ old('date_format', $store->dateFormat()) === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ now()->format('Y-m-d') }})</option>
                                <option value="d M Y" {{ old('date_format', $store->dateFormat()) === 'd M Y' ? 'selected' : '' }}>DD Mon YYYY ({{ now()->format('d M Y') }})</option>
                            </select>
                        </div>

                        <div>
                            <label for="invoice_prefix" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Invoice Prefix
                            </label>
                            <input type="text" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $store->invoicePrefix()) }}" placeholder="e.g. INV" maxlength="10"
                                class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('invoice_prefix') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition uppercase font-mono">
                            <span class="text-[10px] text-slate-400 block mt-1">Example: {{ old('invoice_prefix', $store->invoicePrefix()) }}-000001</span>
                            @error('invoice_prefix')
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5a65d8] text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-100 hover:shadow-lg hover:from-[#3e48b2] hover:to-[#4b55c8] transition cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Save Store Profile</span>
                    </button>
                </div>
            </form>

        </div>

        <!-- Right Col: Logo Upload & Store Owner Account / Password -->
        <div class="space-y-6">

            <!-- Card 2: Store Logo Upload & Management -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-[#1e2746]">Store Logo</h3>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Max 2 MB</span>
                </div>

                <div class="flex flex-col items-center justify-center p-4 bg-[#f8faff] rounded-2xl border border-slate-100 text-center gap-3">
                    @if ($store->logo)
                        <div class="relative group">
                            <img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}" class="w-24 h-24 object-contain rounded-2xl border border-slate-200 shadow-sm bg-white p-2">
                        </div>
                        <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                            ✓ Custom Logo Active
                        </span>
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white text-2xl font-black shadow-md">
                            {{ strtoupper(substr($store->name, 0, 2)) }}
                        </div>
                        <span class="text-xs text-slate-400">
                            No custom logo uploaded yet.
                        </span>
                    @endif
                </div>

                <!-- Logo Upload Form -->
                <form action="{{ route('store.settings.update-logo') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <input type="file" name="logo" id="logo" accept="image/*" required
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#4b55c8] hover:file:bg-blue-100 file:cursor-pointer border border-slate-200 rounded-xl p-1 bg-white">
                        @error('logo')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#4b55c8] text-white text-xs font-bold hover:bg-[#3e48b2] transition cursor-pointer flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>Upload Logo</span>
                    </button>
                </form>

                @if ($store->logo)
                    <!-- Remove Logo Form -->
                    <form action="{{ route('store.settings.remove-logo') }}" method="POST" onsubmit="return confirm('Are you sure you want to remove the store logo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2 rounded-xl bg-slate-100 text-rose-600 text-xs font-bold hover:bg-rose-50 hover:text-rose-700 transition cursor-pointer">
                            Remove Logo
                        </button>
                    </form>
                @endif
            </div>

            <!-- Card 3: Store Owner Personal Profile -->
            <form action="{{ route('store.settings.update-account') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-[#1e2746]">Store Owner Profile</h3>
                        <p class="text-xs text-slate-400">Personal credentials for {{ $user->name }}</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-[#4b55c8] border border-blue-200 uppercase">
                        {{ $user->role->value ?? 'Store Owner' }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label for="owner_name" class="block text-xs font-bold text-slate-700 mb-1">
                            Your Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="owner_name" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3.5 py-2 rounded-xl border {{ $errors->has('name') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                        @error('name')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="owner_email" class="block text-xs font-bold text-slate-700 mb-1">
                            Login Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="owner_email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-3.5 py-2 rounded-xl border {{ $errors->has('email') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                        @error('email')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="owner_mobile" class="block text-xs font-bold text-slate-700 mb-1">
                            Personal Mobile Number
                        </label>
                        <input type="text" id="owner_mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}"
                            class="w-full px-3.5 py-2 rounded-xl border {{ $errors->has('mobile') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                        @error('mobile')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-black transition cursor-pointer flex items-center justify-center gap-2">
                    <span>Save Personal Info</span>
                </button>
            </form>

            <!-- Card 4: Change Password -->
            <form action="{{ route('store.settings.update-password') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                @csrf
                @method('PUT')

                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-[#1e2746]">Change Password</h3>
                    <p class="text-xs text-slate-400">Ensure a strong, secure passphrase.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                            class="w-full px-3.5 py-2 rounded-xl border {{ $errors->has('current_password') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                        @error('current_password')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-bold text-slate-700 mb-1">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="new_password" name="new_password" required
                            class="w-full px-3.5 py-2 rounded-xl border {{ $errors->has('new_password') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                        @error('new_password')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                            Confirm New Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm text-[#1e2746] focus:outline-none focus:border-[#4b55c8] focus:ring-2 focus:ring-indigo-100 transition">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-100 text-[#1e2746] text-xs font-bold hover:bg-slate-200 transition cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Update Password</span>
                </button>
            </form>

        </div>

    </div>

</div>
@endsection
