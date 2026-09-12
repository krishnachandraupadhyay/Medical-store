@extends('store.layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sales Invoices</h1>
            <p class="text-sm text-slate-500 mt-1">Review retail invoices, payment statuses, and customer settlements.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.pos.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-md shadow-emerald-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>New POS Sale</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.sales.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or Customer..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <select name="customer_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <select name="payment_status" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Payments</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.sales.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Invoice #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Items</th>
                        <th class="py-3.5 px-4">Grand Total</th>
                        <th class="py-3.5 px-4">Paid</th>
                        <th class="py-3.5 px-4">Due</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.sales.show', $sale->id) }}" class="hover:underline">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $sale->sale_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">
                            {{ $sale->customer_name ?: 'Walk-in Customer' }}
                        </td>
                        <td class="py-3 px-4">{{ $sale->items->count() }} items</td>
                        <td class="py-3 px-4 font-black text-slate-900">₹{{ number_format($sale->grand_total, 2) }}</td>
                        <td class="py-3 px-4 font-semibold text-emerald-600">₹{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="py-3 px-4 {{ $sale->outstandingAmount() > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                            ₹{{ number_format($sale->outstandingAmount(), 2) }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sale->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($sale->isDraft() ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($sale->status->value) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('store.sales.show', $sale->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">View</a>
                                <a href="{{ route('store.sales.invoice', $sale->id) }}" target="_blank" class="px-2.5 py-1 text-xs font-semibold text-[#4b55c8] hover:bg-[#eef2fd] rounded-lg transition">Invoice</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            No sales invoices found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
