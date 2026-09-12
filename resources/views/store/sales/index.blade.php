@extends('store.layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sales Invoices & Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">Review retail invoices, payment statuses, customer settlements, and ledger records.</p>
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
        <form method="GET" action="{{ route('store.sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or Customer..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Customer</label>
                <select name="customer_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->customer_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Payment</label>
                <select name="payment_status" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Payments</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
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
                        <th class="py-3.5 px-4">Total</th>
                        <th class="py-3.5 px-4">Paid</th>
                        <th class="py-3.5 px-4">Due</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Created By</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.sales.show', $sale->id) }}" class="hover:underline">
                                {{ $sale->invoice_number }}
                            </a>
                            @if($sale->hold_reference)
                            <span class="block text-[10px] text-amber-600 font-semibold">{{ $sale->hold_reference }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $sale->sale_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">
                            @if($sale->customer)
                            <a href="{{ route('store.customers.show', $sale->customer_id) }}" class="hover:underline hover:text-[#4b55c8]">
                                {{ $sale->customer_name }}
                            </a>
                            <span class="block text-[11px] text-slate-400 font-normal">{{ $sale->customer->customer_code }} • {{ $sale->customer_phone ?: 'No phone' }}</span>
                            @else
                            <span>{{ $sale->customer_name ?: 'Walk-in Customer' }}</span>
                            @if($sale->customer_phone)
                            <span class="block text-[11px] text-slate-400 font-normal">{{ $sale->customer_phone }}</span>
                            @endif
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $sale->items->count() }} items</td>
                        <td class="py-3 px-4 font-black text-slate-900 whitespace-nowrap">₹{{ number_format($sale->grand_total, 2) }}</td>
                        <td class="py-3 px-4 font-semibold text-emerald-600 whitespace-nowrap">₹{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="py-3 px-4 whitespace-nowrap {{ $sale->outstandingAmount() > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                            ₹{{ number_format($sale->outstandingAmount(), 2) }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @php
                                $payBadge = match($sale->payment_status?->value) {
                                    'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'partial' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-rose-50 text-rose-700 border-rose-200',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $payBadge }}">
                                {{ ucfirst($sale->payment_status?->value ?? 'unpaid') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sale->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($sale->isDraft() ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($sale->status->value) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap text-slate-500 text-xs">
                            {{ $sale->creator?->name ?? 'System' }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('store.sales.show', $sale->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">View</a>
                                <a href="{{ route('store.sales.invoice', $sale->id) }}" target="_blank" class="px-2.5 py-1 text-xs font-semibold text-[#4b55c8] hover:bg-[#eef2fd] rounded-lg transition">Invoice</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">
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
