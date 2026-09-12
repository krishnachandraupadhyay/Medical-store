@extends('store.layouts.app')

@section('title', 'Edit Batch - ' . $batch->batch_number)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.show', $batch) }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Batch Details
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Edit Batch Details</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Update batch dates, pricing, and status for {{ $batch->medicine->name }} (Batch: {{ $batch->batch_number }}).
            </p>
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

    <form method="POST" action="{{ route('store.inventory.update', $batch) }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Batch & Medicine info -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Batch Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Medicine (Locked)</label>
                    <input type="text" disabled value="{{ $batch->medicine->name }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-xs sm:text-sm text-slate-600 font-semibold cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Batch Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="batch_number" value="{{ old('batch_number', $batch->batch_number) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Manufacturing Date</label>
                    <input type="date" name="manufacturing_date" value="{{ old('manufacturing_date', $batch->manufacturing_date?->toDateString()) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Expiry Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', $batch->expiry_date->toDateString()) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Primary Barcode (POS Scan)</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $batch->barcode) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. 8901234567890">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Secondary Barcode (Optional)</label>
                    <input type="text" name="secondary_barcode" value="{{ old('secondary_barcode', $batch->secondary_barcode) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. Internal SKU / QR code">
                </div>
            </div>
        </div>

        <!-- Pricing info -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Pricing</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Purchase / Cost Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $batch->purchase_price) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">MRP (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', $batch->mrp) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Selling Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', $batch->selling_price) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>
            </div>
        </div>

        <!-- Current Stock (Informational banner) -->
        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 text-xs sm:text-sm text-[#4b55c8] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Current Stock Level: <strong class="font-mono text-base">{{ $batch->quantity }}</strong> units.</span>
            </div>
            <span class="text-[11px] font-semibold text-slate-500">Stock quantities cannot be changed directly here. Use Stock Adjustment.</span>
        </div>

        <!-- Status & Notes -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Status & Notes</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Batch Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                        <option value="active" {{ old('status', $batch->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $batch->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes', $batch->notes) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('store.inventory.show', $batch) }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Update Batch Details
            </button>
        </div>
    </form>

</div>
@endsection
