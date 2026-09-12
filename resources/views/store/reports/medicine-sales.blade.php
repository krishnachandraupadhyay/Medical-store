@extends('store.layouts.app')

@section('title', 'Medicine Sales & Returns Analysis')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Medicine Sales & Volume Analysis</h1>
            <p class="text-sm text-slate-500 mt-1">Dispensed quantities, gross turnover, customer returns, and net sold volume per drug.</p>
        </div>
        <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
            Reports Hub
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.medicine-sales') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Search Medicine</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or generic..." class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
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
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.medicine-sales') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table (Columns from PART Y) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Medicine Name</th>
                        <th class="py-3 px-3.5">Generic / Formula</th>
                        <th class="py-3 px-3.5 text-center">Gross Quantity Sold</th>
                        <th class="py-3 px-3.5 text-right">Sales Turnover Value</th>
                        <th class="py-3 px-3.5 text-center">Returned Qty</th>
                        <th class="py-3 px-3.5 text-center">Net Quantity Sold</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($medicinesReport as $row)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 font-bold text-slate-900">
                            {{ $row->name }} @if($row->strength) ({{ $row->strength }}) @endif
                        </td>
                        <td class="py-2.5 px-3.5 text-slate-500">{{ $row->generic_name ?: '—' }}</td>
                        <td class="py-2.5 px-3.5 text-center font-bold text-slate-800">{{ $row->sold_quantity }}</td>
                        <td class="py-2.5 px-3.5 text-right font-black text-slate-900">₹{{ number_format($row->sales_value, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-center font-bold text-rose-600">{{ $row->return_quantity }}</td>
                        <td class="py-2.5 px-3.5 text-center font-black {{ $row->net_quantity > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                            {{ $row->net_quantity }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">No medicine sales records found in selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($medicinesReport->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $medicinesReport->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
