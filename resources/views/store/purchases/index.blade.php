@extends('store.layouts.app')

@section('title', 'Purchase Management')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Top Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#4b55c8] uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Procurement
                </span>
                <span class="text-xs font-mono font-semibold text-slate-400">Phase 18</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Purchase Invoices</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Record supplier purchase invoices, batches, GST, discounts, and auto-ingest inventory stock.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.purchases.create') }}" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Purchase Invoice</span>
            </a>
        </div>
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

    <!-- Filters -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.purchases.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice number, supplier..." class="w-full pl-9 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div>
                <select name="supplier_id" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Stock Ingested)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="From Date">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'supplier_id', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('store.purchases.index') }}" class="px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        @if ($purchases->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1e2746]">No Purchases Found</h3>
                <p class="text-xs text-[#64748b] mt-1 max-w-sm mx-auto">
                    No purchase invoices have been recorded yet. Create an invoice to add stock to inventory.
                </p>
                <div class="mt-4">
                    <a href="{{ route('store.purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-[#4b55c8] text-white text-xs font-bold shadow-md shadow-[#4b55c8]/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Create First Purchase</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">Invoice #</th>
                            <th class="py-3.5 px-6">Supplier</th>
                            <th class="py-3.5 px-6">Invoice Date</th>
                            <th class="py-3.5 px-6 text-center">Items</th>
                            <th class="py-3.5 px-6 text-right">Grand Total</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                        @foreach ($purchases as $purchase)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div class="font-mono font-bold text-[#4b55c8]">
                                        <a href="{{ route('store.purchases.show', $purchase) }}" class="hover:underline">
                                            {{ $purchase->invoice_number }}
                                        </a>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#1e2746]">
                                        <a href="{{ route('store.suppliers.show', $purchase->supplier) }}" class="hover:text-[#4b55c8]">
                                            {{ $purchase->supplier->name }}
                                        </a>
                                    </div>
                                    @if ($purchase->supplier->company_name)
                                        <div class="text-[10px] text-slate-400">{{ $purchase->supplier->company_name }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-600 font-mono">
                                    {{ $purchase->purchase_date->format('d M, Y') }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ $purchase->items->count() }} items
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-bold text-[#1e2746]">
                                    ₹{{ number_format($purchase->grand_total, 2) }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $purchase->status->badgeClasses() }}">
                                        {{ $purchase->status->label() }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('store.purchases.show', $purchase) }}" class="p-2 rounded-xl text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 transition" title="View details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        @if ($purchase->isDraft())
                                            <a href="{{ route('store.purchases.edit', $purchase) }}" class="p-2 rounded-xl text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 transition" title="Edit draft">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
