@extends('store.layouts.app')

@section('title', 'Inventory Valuation & Batch Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Inventory & Batch Report</h1>
            <p class="text-sm text-slate-500 mt-1">Batch stock levels, pricing, expiry schedules, and shelf availability.</p>
        </div>
        <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
            Reports Hub
        </a>
    </div>

    <!-- Filters (PART W) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.inventory') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Medicine</label>
                <select name="medicine_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Medicines</option>
                    @foreach($medicines as $m)
                    <option value="{{ $m->id }}" {{ ($filters['medicine_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Category</label>
                <select name="category_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Manufacturer</label>
                <select name="manufacturer_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Manufacturers</option>
                    @foreach($manufacturers as $mf)
                    <option value="{{ $mf->id }}" {{ ($filters['manufacturer_id'] ?? '') == $mf->id ? 'selected' : '' }}>{{ $mf->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Stock Status</label>
                <select name="stock_status" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ ($filters['stock_status'] ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock (>0)</option>
                    <option value="out_of_stock" {{ ($filters['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Expiry Status</label>
                <select name="expiry_status" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Statuses</option>
                    <option value="valid" {{ ($filters['expiry_status'] ?? '') === 'valid' ? 'selected' : '' }}>Valid (>90 days)</option>
                    <option value="expiring_soon" {{ ($filters['expiry_status'] ?? '') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (≤90d)</option>
                    <option value="expired" {{ ($filters['expiry_status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.inventory') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table (Columns from PART W) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Medicine</th>
                        <th class="py-3 px-3.5">Generic</th>
                        <th class="py-3 px-3.5">Category</th>
                        <th class="py-3 px-3.5">Batch</th>
                        <th class="py-3 px-3.5">Expiry</th>
                        <th class="py-3 px-3.5 text-right">MRP</th>
                        <th class="py-3 px-3.5 text-right">Selling Price</th>
                        <th class="py-3 px-3.5 text-center">Quantity</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($batches as $b)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 font-bold text-slate-900">{{ $b->medicine?->displayName() }}</td>
                        <td class="py-2.5 px-3.5 text-slate-500">{{ $b->medicine?->generic_name ?: '—' }}</td>
                        <td class="py-2.5 px-3.5 text-slate-500">{{ $b->medicine?->category?->name ?: '—' }}</td>
                        <td class="py-2.5 px-3.5 font-mono font-bold text-slate-800">{{ $b->batch_number }}</td>
                        <td class="py-2.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $b->expiryBadgeClasses() }}">
                                {{ $b->expiry_date->format('d/m/Y') }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3.5 text-right">₹{{ number_format($b->mrp, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-right font-bold text-slate-900">₹{{ number_format($b->selling_price, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-center font-black {{ $b->quantity <= 0 ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ $b->quantity }}
                        </td>
                        <td class="py-2.5 px-3.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $b->quantity > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $b->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">No inventory batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $batches->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
