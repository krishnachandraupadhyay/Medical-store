@extends('store.layouts.app')

@section('title', 'Add New Supplier')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.suppliers.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Suppliers
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Add New Supplier</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">Register a pharmaceutical distributor or vendor for purchase orders.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
            <div class="font-bold mb-1">Please fix the following validation errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('store.suppliers.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <!-- General Info Section -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Supplier Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Supplier / Agency Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Apex Pharma Distributors">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Company / Entity Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Apex Healthcare Private Limited">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Person Name</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Rahul Sharma">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Primary Phone <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. +91 9876543210">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alternate Phone</label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. 022 12345678">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. billing@apexpharma.com">
                </div>
            </div>
        </div>

        <!-- Tax & Legal -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Tax & Regulatory Compliance</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GST Identification Number (GSTIN)</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. 27AAAAA0000A1Z5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Drug License Number</label>
                    <input type="text" name="drug_license_no" value="{{ old('drug_license_no') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. DL-20B-123456">
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Location & Address</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Street Address</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="Shop No, Building, Street, Area...">{{ old('address') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Mumbai">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Maharashtra">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. 400001">
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Notes -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Status & Notes</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Internal Notes</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="Credit period, delivery terms, bank details...">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('store.suppliers.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Save Supplier
            </button>
        </div>
    </form>

</div>
@endsection
