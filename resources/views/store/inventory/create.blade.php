@extends('store.layouts.app')

@section('title', 'Add Medicine Batch & Opening Stock')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Inventory
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Add Batch / Opening Stock</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">Initialize a medicine batch with manufacturing details and optional opening balance.</p>
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

    <form method="POST" action="{{ route('store.inventory.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <!-- Medicine & Batch -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Batch Identification</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Medicine <span class="text-rose-500">*</span></label>
                    <select name="medicine_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                        <option value="">-- Choose Medicine --</option>
                        @foreach ($medicines as $med)
                            <option value="{{ $med->id }}" {{ old('medicine_id', $selectedMedicineId) == $med->id ? 'selected' : '' }}>
                                {{ $med->name }} {{ $med->strength ? "({$med->strength})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Batch Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="batch_number" value="{{ old('batch_number') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. BATCH-2026-001">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Manufacturing Date</label>
                    <input type="date" name="manufacturing_date" value="{{ old('manufacturing_date') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Expiry Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Primary Barcode (POS Scan)</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. 8901234567890">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Secondary Barcode (Optional)</label>
                    <input type="text" name="secondary_barcode" value="{{ old('secondary_barcode') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. Internal SKU / QR code">
                </div>
            </div>
        </div>

        <!-- Pricing & Initial Stock -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Pricing & Initial Stock</h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Purchase / Cost Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', '0.00') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">MRP (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', '0.00') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Selling Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', '0.00') }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Opening Stock Qty</label>
                    <input type="number" min="0" name="opening_stock" value="{{ old('opening_stock', '0') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">
                Note: Opening stock quantity is only editable during initial creation. Future stock changes must be made via purchases or stock adjustments.
            </p>
        </div>

        <!-- Status & Notes -->
        <div>
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Status & Notes</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Batch Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="Storage location, rack, shelf number...">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('store.inventory.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Create Batch & Record Stock
            </button>
        </div>
    </form>

</div>
@endsection
