@extends('store.layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.suppliers.show', $supplier) }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Supplier Details
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Edit Supplier</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">Update contact details and credentials for {{ $supplier->name }}.</p>
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

    <form method="POST" action="{{ route('store.suppliers.update', $supplier) }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- General Info Section -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Supplier Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Supplier / Agency Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Company / Entity Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Person Name</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Primary Phone <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alternate Phone</label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone', $supplier->alternate_phone) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>
            </div>
        </div>

        <!-- Tax & Legal -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Tax & Regulatory Compliance</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GST Identification Number (GSTIN)</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number', $supplier->gst_number) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Drug License Number</label>
                    <input type="text" name="drug_license_no" value="{{ old('drug_license_no', $supplier->drug_license_no) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Location & Address</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Street Address</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">{{ old('address', $supplier->address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">City</label>
                        <input type="text" name="city" value="{{ old('city', $supplier->city) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state', $supplier->state) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode', $supplier->pincode) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
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
                        <option value="active" {{ old('status', $supplier->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $supplier->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Internal Notes</label>
                    <input type="text" name="notes" value="{{ old('notes', $supplier->notes) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('store.suppliers.show', $supplier) }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Update Supplier
            </button>
        </div>
    </form>

</div>
@endsection
