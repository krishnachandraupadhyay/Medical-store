@extends('store.layouts.app')

@section('title', 'Edit Customer: ' . $customer->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Customer</h1>
                <span class="font-mono text-sm text-[#4b55c8] font-bold">[{{ $customer->customer_code }}]</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Update patient profile, status, contact details, and medical reference.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.customers.show', $customer->id) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-2xs">
                View Profile
            </a>
            <a href="{{ route('store.customers.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-2xs">
                Back to Directory
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('store.customers.update', $customer->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Patient / Customer Profile -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-black uppercase text-slate-700 tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    <span>Personal & Demographics</span>
                </h2>
                <!-- Status Dropdown -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500">Account Status:</label>
                    <select name="status" class="text-xs font-black rounded-xl border border-slate-200 px-3 py-1.5 outline-none focus:border-[#4b55c8] {{ old('status', $customer->status) === 'active' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-600 bg-slate-50' }}">
                        <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>● Active</option>
                        <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>○ Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                    @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Primary Phone Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required maxlength="10" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                    @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Alternate Contact Phone
                    </label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone', $customer->alternate_phone) }}" maxlength="10" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                    @error('alternate_phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                    @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Gender
                    </label>
                    <select name="gender" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $customer->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $customer->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $customer->gender) === 'other' ? 'selected' : '' }}>Other</option>
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
                            <option value="{{ $bg }}" {{ old('blood_group', $customer->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                    @error('blood_group') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Date of Birth
                    </label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $customer->date_of_birth?->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                    @error('date_of_birth') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Address & Residence -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-black uppercase text-slate-700 tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Address & Location</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Street Address</label>
                    <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $customer->state) }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">PIN / Postal Code</label>
                    <input type="text" name="pincode" value="{{ old('pincode', $customer->pincode) }}" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
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
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $customer->emergency_contact_name) }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Sunita Kumar (Spouse / Guardian)">
                    @error('emergency_contact_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Emergency Contact Phone Number
                    </label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $customer->emergency_contact_phone) }}" maxlength="10" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
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
                    <input type="text" name="doctor_name" value="{{ old('doctor_name', $customer->doctor_name) }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none" placeholder="e.g. Dr. A. K. Sharma">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        GSTIN / Tax ID (Optional)
                    </label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="w-full text-sm font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Medical Remarks / Chronic Conditions / Notes
                    </label>
                    <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-[#4b55c8] outline-none">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Controls -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('store.customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Update Customer Master
            </button>
        </div>
    </form>
</div>
@endsection
