@extends('store.layouts.app')

@section('title', 'Edit Medicine - ' . $medicine->name)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.medicines.show', $medicine) }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Product Details
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Edit Medicine Master</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Update pharmaceutical specifications, classification, and tax parameters for <span class="font-bold text-[#1e2746]">{{ $medicine->name }}</span>.
            </p>
        </div>
    </div>

    <!-- Error Alert Banner -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium space-y-1 shadow-sm">
            <div class="font-bold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Please correct the errors in the form below:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Medicine Form Card -->
    <form action="{{ route('store.medicines.update', $medicine) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic Information -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                    1
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#1e2746]">Basic Product Information</h3>
                    <p class="text-[11px] text-[#64748b]">Medicine naming and classification taxonomy.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Medicine Name -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="name" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Medicine Name <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $medicine->name) }}"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border @error('name') border-rose-400 @else border-slate-200 @enderror focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition"
                    >
                    @error('name')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Generic Name -->
                <div>
                    <label for="generic_name" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Generic Formulation / Chemical
                    </label>
                    <input
                        type="text"
                        name="generic_name"
                        id="generic_name"
                        value="{{ old('generic_name', $medicine->generic_name) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition"
                    >
                </div>

                <!-- Brand Name -->
                <div>
                    <label for="brand_name" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Commercial Brand Name
                    </label>
                    <input
                        type="text"
                        name="brand_name"
                        id="brand_name"
                        value="{{ old('brand_name', $medicine->brand_name) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition"
                    >
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Therapeutic Category
                    </label>
                    <select name="category_id" id="category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition">
                        <option value="">-- Select Category --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $medicine->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Manufacturer -->
                <div class="sm:col-span-2">
                    <label for="manufacturer_id" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Manufacturer / Pharmaceutical Company
                    </label>
                    <select name="manufacturer_id" id="manufacturer_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition">
                        <option value="">-- Select Manufacturer --</option>
                        @foreach ($manufacturers as $mfg)
                            <option value="{{ $mfg->id }}" {{ old('manufacturer_id', $medicine->manufacturer_id) == $mfg->id ? 'selected' : '' }}>
                                {{ $mfg->name }} {{ $mfg->code ? "({$mfg->code})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Pharmaceutical & Dosage Information -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                    2
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#1e2746]">Pharmaceutical & Packaging Parameters</h3>
                    <p class="text-[11px] text-[#64748b]">Dosage forms, unit measurements, and prescription requirements.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Dosage Form -->
                <div>
                    <label for="dosage_form_id" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Dosage Form
                    </label>
                    <select name="dosage_form_id" id="dosage_form_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition">
                        <option value="">-- Select Dosage Form --</option>
                        @foreach ($dosageForms as $df)
                            <option value="{{ $df->id }}" {{ old('dosage_form_id', $medicine->dosage_form_id) == $df->id ? 'selected' : '' }}>
                                {{ $df->name }} {{ $df->short_name ? "({$df->short_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Strength -->
                <div>
                    <label for="strength" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Strength / Potency
                    </label>
                    <input
                        type="text"
                        name="strength"
                        id="strength"
                        value="{{ old('strength', $medicine->strength) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition"
                    >
                </div>

                <!-- Pack Size -->
                <div>
                    <label for="pack_size" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Pack Size Description
                    </label>
                    <input
                        type="text"
                        name="pack_size"
                        id="pack_size"
                        value="{{ old('pack_size', $medicine->pack_size) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition"
                    >
                </div>

                <!-- Packaging Unit -->
                <div>
                    <label for="unit_id" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Packaging Unit
                    </label>
                    <select name="unit_id" id="unit_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition">
                        <option value="">-- Select Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id', $medicine->unit_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Prescription Required Checkbox -->
                <div class="sm:col-span-2 lg:col-span-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-[#1e2746] block">Prescription Drug Schedule (℞ Required)</span>
                        <span class="text-[11px] text-[#64748b]">Mandatory doctor's prescription flag for future POS dispensing validation.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="prescription_required" value="1" {{ old('prescription_required', $medicine->prescription_required) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Section 3: Tax & Inventory Alert Thresholds -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                    3
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#1e2746]">Tax & Stock Alert Configurations</h3>
                    <p class="text-[11px] text-[#64748b]">GST rate, HSN classification, and low stock threshold.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- HSN Code -->
                <div>
                    <label for="hsn_code" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        HSN Code
                    </label>
                    <input
                        type="text"
                        name="hsn_code"
                        id="hsn_code"
                        value="{{ old('hsn_code', $medicine->hsn_code) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition font-mono"
                    >
                </div>

                <!-- GST Rate -->
                <div>
                    <label for="gst_rate" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        GST Rate (%) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        name="gst_rate"
                        id="gst_rate"
                        value="{{ old('gst_rate', $medicine->gst_rate) }}"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border @error('gst_rate') border-rose-400 @else border-slate-200 @enderror focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition font-mono font-bold"
                    >
                    @error('gst_rate')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reorder Level -->
                <div>
                    <label for="reorder_level" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Reorder Level Threshold <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        min="0"
                        name="reorder_level"
                        id="reorder_level"
                        value="{{ old('reorder_level', $medicine->reorder_level) }}"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border @error('reorder_level') border-rose-400 @else border-slate-200 @enderror focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition font-mono font-bold"
                    >
                    @error('reorder_level')
                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Product Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" id="status" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition font-semibold">
                        <option value="active" {{ old('status', $medicine->status) === 'active' ? 'selected' : '' }}>Active (Available in Catalog)</option>
                        <option value="inactive" {{ old('status', $medicine->status) === 'inactive' ? 'selected' : '' }}>Inactive (Archived)</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="sm:col-span-2 lg:col-span-4">
                    <label for="description" class="block text-xs font-bold text-[#1e2746] mb-1.5">
                        Product Clinical Notes / Indications
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#4b55c8] rounded-xl text-xs sm:text-sm text-[#1e2746] focus:outline-none transition"
                    >{{ old('description', $medicine->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('store.medicines.show', $medicine) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Save Changes</span>
            </button>
        </div>
    </form>

</div>
@endsection
