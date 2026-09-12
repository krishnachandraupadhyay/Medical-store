@extends('store.layouts.app')

@section('title', 'Add Customer - Customer Master')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Add New Customer</h1>
            <p class="text-sm text-slate-500 mt-1">Register a patient or corporate customer in your pharmacy master directory.</p>
        </div>
        <a href="{{ route('store.customers.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-2xs">
            Back to Directory
        </a>
    </div>

    <form method="POST" action="{{ route('store.customers.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Patient / Customer Profile -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-black uppercase text-slate-700 tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                <span>Personal & Demographics</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Ramesh Kumar">
                    @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Primary Phone Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="10" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="10-digit mobile number (e.g. 9876543210)">
                    <p class="text-[11px] text-slate-400 mt-0.5">Primary identifier for patient records & POS lookup.</p>
                    @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Alternate Contact Phone
                    </label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone') }}" maxlength="10" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="Secondary 10-digit number">
                    @error('alternate_phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="patient@example.com">
                    @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Gender
                    </label>
                    <select name="gender" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Blood Group
                    </label>
                    <select name="blood_group" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                        <option value="">Select Blood Group</option>
                        @foreach($bloodGroups as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                    @error('blood_group') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Date of Birth
                    </label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                    @error('date_of_birth') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Address & Location -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-black uppercase text-slate-700 tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Address & Residence</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Street Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="House/Flat No, Apartment, Street, Colony">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Mumbai">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Maharashtra">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">PIN / Postal Code</label>
                    <input type="text" name="pincode" value="{{ old('pincode') }}" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. 400001">
                </div>
            </div>
        </div>

        <!-- 3. Emergency Contact Information -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-black uppercase text-slate-700 tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Emergency Contact Information</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Emergency Contact Person Name
                    </label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Sunita Kumar (Spouse / Guardian)">
                    @error('emergency_contact_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Emergency Contact Phone Number
                    </label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="10" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="10-digit mobile number">
                    @error('emergency_contact_phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 4. Medical Reference & Tax Details -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-black uppercase text-slate-700 tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Medical Reference & Tax Info</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Prescribing / Consulting Doctor
                    </label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Dr. A. K. Sharma">
                    <p class="text-[11px] text-slate-400 mt-1">Used for prescription reference and POS auto-tagging.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        GSTIN / Tax ID (Optional)
                    </label>
                    <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. 27AAAAA0000A1Z5">
                    <p class="text-[11px] text-slate-400 mt-1">Required if billing to corporate or GST-registered customer.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Medical Remarks / Chronic Conditions / Notes
                    </label>
                    <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Diabetic patient, allergic to penicillin, allowed 15 days credit...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Controls -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('store.customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Save Customer Master
            </button>
        </div>
    </form>
</div>
@endsection
