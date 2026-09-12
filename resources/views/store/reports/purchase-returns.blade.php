@extends('store.layouts.app')

@section('title', 'Purchase Return Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Purchase Return Report</h1>
            <p class="text-sm text-slate-500 mt-1">Audit of supplier returns, damaged stock dispatches, and debit notes.</p>
        </div>
        <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
            Reports Hub
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.purchase-returns') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Supplier</label>
                <select name="supplier_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ ($filters['supplier_id'] ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Return #</label>
                <input type="text" name="return_number" value="{{ $filters['return_number'] ?? '' }}" placeholder="Search return..." class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.purchase-returns') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Return #</th>
                        <th class="py-3 px-3.5">Original Purchase</th>
                        <th class="py-3 px-3.5">Supplier</th>
                        <th class="py-3 px-3.5">Date</th>
                        <th class="py-3 px-3.5 text-center">Items</th>
                        <th class="py-3 px-3.5 text-right">Amount</th>
                        <th class="py-3 px-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($returns as $r)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.purchase-returns.show', $r->id) }}" class="hover:underline">
                                {{ $r->return_number }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3.5 font-mono font-bold text-slate-800">{{ $r->purchase?->invoice_number }}</td>
                        <td class="py-2.5 px-3.5 font-semibold text-slate-900">{{ $r->supplier?->name }}</td>
                        <td class="py-2.5 px-3.5">{{ $r->return_date->format('d/m/Y') }}</td>
                        <td class="py-2.5 px-3.5 text-center font-bold">{{ $r->items->count() }}</td>
                        <td class="py-2.5 px-3.5 text-right font-black text-rose-600">₹{{ number_format($r->grand_total, 2) }}</td>
                        <td class="py-2.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ ucfirst($r->status->value) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">No purchase return records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
