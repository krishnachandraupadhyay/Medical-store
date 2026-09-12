@extends('store.layouts.app')

@section('title', $medicine->name . ' - Product Master Details')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.medicines.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Medicines Catalog
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">{{ $medicine->name }}</h1>
                @if ($medicine->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                    </span>
                @endif

                @if ($medicine->prescription_required)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span>℞</span> Prescription Required
                    </span>
                @else
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        OTC Medication
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Pharmaceutical specifications and tax compliance for this catalog product.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.medicines.edit', $medicine) }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Product</span>
            </a>

            <!-- Toggle Status Form -->
            <form action="{{ route('store.medicines.toggle-status', $medicine) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm transition">
                    {{ $medicine->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Banners -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ session('status') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    <!-- Specifications Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Card 1: Taxonomy & Brand -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                    🏷️
                </div>
                <h3 class="text-sm font-bold text-[#1e2746]">Product Identification</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Medicine Name</span>
                    <span class="font-extrabold text-[#1e2746] text-sm">{{ $medicine->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Generic Chemical Formula</span>
                    <span class="font-semibold text-slate-700">{{ $medicine->generic_name ?: 'None specified' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Commercial Brand</span>
                    <span class="font-semibold text-slate-700">{{ $medicine->brand_name ?: 'Generic / Store label' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Therapeutic Category</span>
                    <span class="font-bold text-[#4b55c8] bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-100 inline-block mt-0.5">
                        {{ $medicine->category->name ?? 'Uncategorized' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Manufacturer</span>
                    <span class="font-semibold text-slate-700">{{ $medicine->manufacturer->name ?? 'Not specified' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Pharmaceutical Parameters -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                    💊
                </div>
                <h3 class="text-sm font-bold text-[#1e2746]">Dosage & Packaging</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Dosage Form</span>
                    <span class="font-bold text-[#1e2746]">{{ $medicine->dosageForm->name ?? 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Strength / Potency</span>
                    <span class="font-mono font-bold text-[#4b55c8] text-sm">{{ $medicine->strength ?: 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Pack Size</span>
                    <span class="font-semibold text-slate-700">{{ $medicine->pack_size ?: 'Standard Unit' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Unit of Measurement</span>
                    <span class="font-semibold text-slate-700">{{ $medicine->unit->name ?? 'Default Unit' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Schedule / Dispensing</span>
                    <span class="font-bold {{ $medicine->prescription_required ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $medicine->prescription_required ? '℞ Prescription Required' : 'Over The Counter (OTC)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Tax, Stock Alert & Regulatory -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold text-xs">
                    ⚖️
                </div>
                <h3 class="text-sm font-bold text-[#1e2746]">Tax & Inventory Thresholds</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Harmonized System of Nomenclature (HSN)</span>
                    <span class="font-mono font-bold text-[#1e2746]">{{ $medicine->hsn_code ?: 'None' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Goods & Services Tax (GST)</span>
                    <span class="font-mono font-bold text-[#4b55c8] text-sm">{{ (float) $medicine->gst_rate }}%</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Low Stock Reorder Alert Level</span>
                    <span class="font-mono font-bold text-amber-600">{{ $medicine->reorder_level }} units</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Inventory Tracking Status</span>
                    <span class="text-[11px] text-[#64748b]">Ready for Phase 17 Batch & Stock management</span>
                </div>
            </div>
        </div>

        <!-- Clinical Notes / Description (Full Width) -->
        <div class="md:col-span-2 lg:col-span-3 p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-[#1e2746] uppercase tracking-wider">Clinical Description & Indications</h3>
            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                {{ $medicine->description ?: 'No clinical description or dosage remarks recorded for this product.' }}
            </p>
        </div>

        <!-- Audit Metadata Card -->
        <div class="md:col-span-2 lg:col-span-3 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-[11px] text-slate-500 flex flex-wrap items-center justify-between gap-3">
            <div>
                Created on: <strong class="text-slate-700">{{ $medicine->created_at->format('d M Y, h:i A') }}</strong>
                @if ($medicine->creator)
                    by <strong class="text-[#4b55c8]">{{ $medicine->creator->name }}</strong>
                @endif
            </div>
            <div>
                Last modified: <strong class="text-slate-700">{{ $medicine->updated_at->format('d M Y, h:i A') }}</strong>
                @if ($medicine->updater)
                    by <strong class="text-[#4b55c8]">{{ $medicine->updater->name }}</strong>
                @endif
            </div>
            <div>
                Medical Store: <strong class="text-slate-700">{{ $store->name ?? 'N/A' }}</strong>
            </div>
        </div>

    </div>

</div>
@endsection
