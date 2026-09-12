@extends('store.layouts.app')

@section('title', 'Medicine Master')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Pharmaceutical Catalog
                </span>
                <span class="text-xs font-mono font-semibold text-slate-400">Phase 16</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Medicine Master</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Centralized store catalog of pharmaceutical products, strengths, dosage forms, and HSN codes.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($quota['can_add'])
                <a href="{{ route('store.medicines.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add New Medicine</span>
                </a>
            @else
                <button type="button" disabled class="px-5 py-2.5 rounded-2xl bg-slate-200 text-slate-400 font-bold text-xs sm:text-sm cursor-not-allowed flex items-center gap-2" title="Plan limit reached">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-5a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Limit Reached (Upgrade Plan)</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Banners (Success & Error) -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ session('status') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Quota & Subscription Capacity Banner -->
    <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#eef2fd] text-[#4b55c8] flex items-center justify-center font-extrabold text-base flex-shrink-0">
                💊
            </div>
            <div>
                <span class="text-xs font-bold text-[#1e2746]">Medicine Catalog Capacity</span>
                <p class="text-[11px] text-[#64748b]">
                    @if ($quota['is_unlimited'])
                        Your current subscription provides <span class="font-bold text-[#4b55c8]">Unlimited</span> medicine product capacity.
                    @else
                        Plan limit: <strong class="text-[#1e2746]">{{ number_format($quota['limit']) }}</strong> items &bull; 
                        Currently active: <strong class="text-[#4b55c8]">{{ number_format($quota['usage']) }}</strong> &bull; 
                        Remaining: <strong class="{{ $quota['remaining'] <= 10 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($quota['remaining']) }}</strong>
                    @endif
                </p>
            </div>
        </div>

        @if (! $quota['is_unlimited'] && $quota['limit'] > 0)
            @php
                $percentage = min(100, round(($quota['usage'] / $quota['limit']) * 100));
            @endphp
            <div class="w-full md:w-64 space-y-1.5">
                <div class="flex justify-between text-[11px] font-bold text-[#1e2746]">
                    <span>Quota Used:</span>
                    <span class="{{ $percentage >= 90 ? 'text-rose-600' : 'text-[#4b55c8]' }}">{{ $percentage }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $percentage >= 90 ? 'bg-rose-500' : ($percentage >= 75 ? 'bg-amber-500' : 'bg-[#4b55c8]') }}" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Metric Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Catalog Items</span>
            <div class="text-xl sm:text-2xl font-black text-[#1e2746] mt-1">{{ number_format($counts['all']) }}</div>
        </div>

        <!-- Card 2: Active -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Active Products</span>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 mt-1">{{ number_format($counts['active']) }}</div>
        </div>

        <!-- Card 3: Inactive -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Inactive / Hidden</span>
            <div class="text-xl sm:text-2xl font-black text-slate-600 mt-1">{{ number_format($counts['inactive']) }}</div>
        </div>

        <!-- Card 4: Rx Required -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Prescription Required (Rx)</span>
            <div class="text-xl sm:text-2xl font-black text-rose-700 mt-1">{{ number_format($counts['prescription']) }}</div>
        </div>
    </div>

    <!-- Search & Filters Container -->
    <form method="GET" action="{{ route('store.medicines.index') }}" class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            
            <!-- Search Text -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-[11px] font-bold text-[#64748b] uppercase mb-1">Search Products</label>
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Search medicine name, generic, brand..."
                        class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-[#1e2746] focus:bg-white focus:border-[#4b55c8] focus:outline-none transition"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category_id" class="block text-[11px] font-bold text-[#64748b] uppercase mb-1">Category</label>
                <select name="category_id" id="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-[#1e2746] focus:bg-white focus:border-[#4b55c8] focus:outline-none transition">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Manufacturer Filter -->
            <div>
                <label for="manufacturer_id" class="block text-[11px] font-bold text-[#64748b] uppercase mb-1">Manufacturer</label>
                <select name="manufacturer_id" id="manufacturer_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-[#1e2746] focus:bg-white focus:border-[#4b55c8] focus:outline-none transition">
                    <option value="">All Manufacturers</option>
                    @foreach ($manufacturers as $mfg)
                        <option value="{{ $mfg->id }}" {{ request('manufacturer_id') == $mfg->id ? 'selected' : '' }}>
                            {{ $mfg->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Dosage Form Filter -->
            <div>
                <label for="dosage_form_id" class="block text-[11px] font-bold text-[#64748b] uppercase mb-1">Dosage Form</label>
                <select name="dosage_form_id" id="dosage_form_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-[#1e2746] focus:bg-white focus:border-[#4b55c8] focus:outline-none transition">
                    <option value="">All Forms</option>
                    @foreach ($dosageForms as $df)
                        <option value="{{ $df->id }}" {{ request('dosage_form_id') == $df->id ? 'selected' : '' }}>
                            {{ $df->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-[11px] font-bold text-[#64748b] uppercase mb-1">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-[#1e2746] focus:bg-white focus:border-[#4b55c8] focus:outline-none transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
        </div>

        <!-- Filter Action Buttons & Sorting Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
            <div class="flex items-center gap-3">
                <span class="text-xs text-[#64748b] font-medium">Sort By:</span>
                <select name="sort" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-[#1e2746] focus:bg-white focus:outline-none">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A - Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z - A)</option>
                </select>

                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-[#334155] ml-2">
                    <input type="checkbox" name="prescription_required" value="1" {{ request('prescription_required') ? 'checked' : '' }} class="w-3.5 h-3.5 rounded border-slate-300 text-[#4b55c8] focus:ring-0 cursor-pointer">
                    <span>Rx Required Only</span>
                </label>
            </div>

            <div class="flex items-center gap-2">
                @if (request()->hasAny(['search', 'category_id', 'manufacturer_id', 'dosage_form_id', 'status', 'prescription_required', 'sort']))
                    <a href="{{ route('store.medicines.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-[#4b55c8] text-white hover:bg-[#3f49b8] text-xs font-bold transition shadow-sm">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>

    <!-- Medicines Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm overflow-hidden">
        @if ($medicines->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-bold text-[#64748b] uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">Medicine & Brand</th>
                            <th class="py-3.5 px-4">Generic Formulation</th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4">Dosage / Strength</th>
                            <th class="py-3.5 px-4">GST / HSN</th>
                            <th class="py-3.5 px-4">Rx Type</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                        @foreach ($medicines as $med)
                            <tr class="hover:bg-slate-50/60 transition group">
                                <!-- Medicine Name & Brand -->
                                <td class="py-3 px-4 sm:px-6">
                                    <div class="font-extrabold text-[#1e2746]">
                                        <a href="{{ route('store.medicines.show', $med) }}" class="hover:text-[#4b55c8] transition">
                                            {{ $med->name }}
                                        </a>
                                    </div>
                                    <div class="text-[11px] text-[#64748b] flex items-center gap-2 mt-0.5">
                                        @if ($med->brand_name)
                                            <span class="font-medium text-slate-500">Brand: {{ $med->brand_name }}</span>
                                        @endif
                                        @if ($med->pack_size)
                                            <span class="text-[10px] px-1.5 py-0.5 bg-slate-100 rounded text-slate-600 font-mono">{{ $med->pack_size }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Generic Name -->
                                <td class="py-3 px-4 text-xs font-medium text-slate-600">
                                    {{ $med->generic_name ?: '—' }}
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-[#4b55c8] border border-blue-100/80">
                                        {{ $med->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>

                                <!-- Dosage Form & Strength -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-[#1e2746] text-xs">
                                        {{ $med->dosageForm->name ?? 'Form N/A' }}
                                    </div>
                                    <div class="text-[11px] font-mono text-[#4b55c8] font-bold mt-0.5">
                                        {{ $med->strength ?: 'Strength N/A' }}
                                    </div>
                                </td>

                                <!-- GST & HSN -->
                                <td class="py-3 px-4 text-xs">
                                    <span class="font-bold text-slate-800">{{ (float) $med->gst_rate }}% GST</span>
                                    <span class="block text-[10px] font-mono text-slate-400">{{ $med->hsn_code ? 'HSN: '.$med->hsn_code : 'No HSN' }}</span>
                                </td>

                                <!-- Rx Type -->
                                <td class="py-3 px-4">
                                    @if ($med->prescription_required)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>℞</span> Rx Required
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            OTC
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4">
                                    @if ($med->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions Dropdown / Quick buttons -->
                                <td class="py-3 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- View -->
                                        <a href="{{ route('store.medicines.show', $med) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-[#4b55c8] hover:bg-blue-50 transition" title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('store.medicines.edit', $med) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Edit Medicine">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Toggle Status -->
                                        <form action="{{ route('store.medicines.toggle-status', $med) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="{{ $med->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </button>
                                        </form>

                                        <!-- Soft Delete -->
                                        <form action="{{ route('store.medicines.destroy', $med) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this medicine product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete Medicine">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $medicines->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-blue-50 text-[#4b55c8] flex items-center justify-center text-2xl shadow-inner">
                    💊
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#1e2746]">No medicines found</h3>
                    <p class="text-xs text-[#64748b] max-w-sm mx-auto mt-1">
                        @if (request()->hasAny(['search', 'category_id', 'manufacturer_id', 'dosage_form_id', 'status', 'prescription_required']))
                            No medicine products match your active filter criteria. Try resetting filters.
                        @else
                            Start building your store catalog by adding pharmaceutical products, dosages, and GST rates.
                        @endif
                    </p>
                </div>
                <div>
                    @if (request()->hasAny(['search', 'category_id', 'manufacturer_id', 'dosage_form_id', 'status', 'prescription_required']))
                        <a href="{{ route('store.medicines.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                            Clear Filters
                        </a>
                    @elseif ($quota['can_add'])
                        <a href="{{ route('store.medicines.create') }}" class="px-5 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-md transition">
                            + Add First Medicine
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
