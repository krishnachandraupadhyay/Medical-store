@extends('store.layouts.app')

@section('title', 'Batch & Inventory Management')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Top Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Warehouse & Stock
                </span>
                <span class="text-xs font-mono font-semibold text-slate-400">Phase 17</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Batch & Inventory Management</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Batch-level tracking, manufacturing & expiry dates, stock movements, and inventory adjustments.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.inventory.history') }}" class="px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Stock Movement Ledger</span>
            </a>

            <a href="{{ route('store.inventory.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Batch / Opening Stock</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Stock -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Units in Stock</div>
            <div class="text-2xl font-black text-[#1e2746] mt-1">{{ number_format($totalItemsCount) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Across all active batches</div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Out of Stock Batches</div>
            <div class="text-2xl font-black {{ $outOfStockCount > 0 ? 'text-rose-600' : 'text-slate-700' }} mt-1">
                {{ $outOfStockCount }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Batches with 0 units remaining</div>
        </div>

        <!-- Expiring Soon (<90 Days) -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Expiring in 90 Days</div>
            <div class="text-2xl font-black {{ $expiringSoonCount > 0 ? 'text-amber-600' : 'text-slate-700' }} mt-1">
                {{ $expiringSoonCount }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Requires inventory clearance</div>
        </div>

        <!-- Expired Batches -->
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Expired Batches</div>
            <div class="text-2xl font-black {{ $expiredCount > 0 ? 'text-rose-600' : 'text-slate-700' }} mt-1">
                {{ $expiredCount }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Disposal / adjustment required</div>
        </div>
    </div>

    <!-- Phase 24 Advanced Stock Operations Navigation Bar -->
    <div class="flex flex-wrap items-center gap-2 p-3 bg-white rounded-2xl border border-slate-100 shadow-sm text-xs font-bold">
        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 px-2">Advanced Operations:</span>
        <a href="{{ route('store.inventory.stock-counts.index') }}" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-[#4b55c8] transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Stock Counts & Reconciliation
        </a>
        <a href="{{ route('store.inventory.valuation') }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Inventory Valuation
        </a>
        <a href="{{ route('store.inventory.damaged.index') }}" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Damaged Stock
        </a>
        <a href="{{ route('store.inventory.lost.index') }}" class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Lost / Shrinkage
        </a>
        <a href="{{ route('store.inventory.expired.index') }}" class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Expired Disposal
        </a>
    </div>

    <!-- Alert Banners -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('status') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search batch number, medicine name..." class="w-full pl-9 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div>
                <select name="medicine_id" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Medicines</option>
                    @foreach ($medicines as $med)
                        <option value="{{ $med->id }}" {{ request('medicine_id') == $med->id ? 'selected' : '' }}>
                            {{ $med->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock_status" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤ Reorder)</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>

            <div>
                <select name="expiry_status" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Expiry Statuses</option>
                    <option value="valid" {{ request('expiry_status') === 'valid' ? 'selected' : '' }}>Valid (>90 Days)</option>
                    <option value="expiring_soon" {{ request('expiry_status') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (≤90 Days)</option>
                    <option value="expired" {{ request('expiry_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'medicine_id', 'stock_status', 'expiry_status']))
                    <a href="{{ route('store.inventory.index') }}" class="px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Inventory Batches Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        @if ($batches->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1e2746]">No Batches Found</h3>
                <p class="text-xs text-[#64748b] mt-1 max-w-sm mx-auto">
                    No batches match your criteria. Record a purchase or add opening stock to track inventory.
                </p>
                <div class="mt-4">
                    <a href="{{ route('store.inventory.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-[#4b55c8] text-white text-xs font-bold shadow-md shadow-[#4b55c8]/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Batch</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">Medicine & Batch</th>
                            <th class="py-3.5 px-6">Dates (Mfg / Exp)</th>
                            <th class="py-3.5 px-6 text-right">Pricing (Cost / MRP)</th>
                            <th class="py-3.5 px-6 text-center">Stock Quantity</th>
                            <th class="py-3.5 px-6 text-center">Expiry Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                        @foreach ($batches as $batch)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#1e2746]">
                                        <a href="{{ route('store.inventory.show', $batch) }}" class="hover:text-[#4b55c8] transition">
                                            {{ $batch->medicine->name }}
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-xs font-bold text-[#4b55c8] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                            Batch: {{ $batch->batch_number }}
                                        </span>
                                        @if ($batch->medicine->dosageForm)
                                            <span class="text-[10px] text-slate-400">{{ $batch->medicine->dosageForm->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-xs">
                                    <div><span class="text-slate-400">Exp:</span> <span class="font-bold">{{ $batch->expiry_date->format('M Y') }}</span> ({{ $batch->expiry_date->format('d/m/Y') }})</div>
                                    @if ($batch->manufacturing_date)
                                        <div class="text-[10px] text-slate-400 mt-0.5">Mfg: {{ $batch->manufacturing_date->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right text-xs font-mono">
                                    <div class="font-bold text-[#1e2746]">MRP: ₹{{ number_format($batch->mrp, 2) }}</div>
                                    <div class="text-[10px] text-slate-400">Cost: ₹{{ number_format($batch->purchase_price, 2) }} | Sale: ₹{{ number_format($batch->selling_price, 2) }}</div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ $batch->quantity <= 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($batch->quantity <= $batch->medicine->reorder_level ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                        {{ $batch->quantity }} {{ $batch->medicine->unit?->short_name ?? 'units' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $batch->expiryBadgeClasses() }}">
                                        {{ $batch->expiryStatusLabel() }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('store.inventory.show', $batch) }}" class="p-2 rounded-xl text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 transition" title="View details & movement history">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('store.inventory.edit', $batch) }}" class="p-2 rounded-xl text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 transition" title="Edit batch metadata">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
