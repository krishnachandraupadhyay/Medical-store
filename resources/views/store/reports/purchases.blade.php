@extends('store.layouts.app')

@section('title', 'Purchase Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Purchase Report</h1>
            <p class="text-sm text-slate-500 mt-1">Vendor bills, procurement costs, payment statuses, and supplier liabilities.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
                Reports Hub
            </a>
        </div>
    </div>

    <!-- Filters Bar (PART T) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.purchases') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Supplier</label>
                <select name="supplier_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ ($filters['supplier_id'] ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Medicine</label>
                <select name="medicine_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Medicines</option>
                    @foreach($medicines as $m)
                    <option value="{{ $m->id }}" {{ ($filters['medicine_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Payment Status</label>
                <select name="payment_status" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Statuses</option>
                    @foreach($paymentStatuses as $ps)
                    <option value="{{ $ps->value }}" {{ ($filters['payment_status'] ?? '') === $ps->value ? 'selected' : '' }}>{{ $ps->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.purchases') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Report Table (Columns from PART T) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Invoice #</th>
                        <th class="py-3 px-3.5">Date</th>
                        <th class="py-3 px-3.5">Supplier</th>
                        <th class="py-3 px-3.5 text-center">Items</th>
                        <th class="py-3 px-3.5 text-right">Subtotal</th>
                        <th class="py-3 px-3.5 text-right">Discount</th>
                        <th class="py-3 px-3.5 text-right">Tax (GST)</th>
                        <th class="py-3 px-3.5 text-right">Grand Total</th>
                        <th class="py-3 px-3.5 text-right">Paid</th>
                        <th class="py-3 px-3.5 text-right">Outstanding</th>
                        <th class="py-3 px-3.5 text-center">Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.purchases.show', $p->id) }}" class="hover:underline">
                                {{ $p->invoice_number }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3.5">{{ $p->purchase_date->format('d/m/Y') }}</td>
                        <td class="py-2.5 px-3.5 font-semibold text-slate-900">{{ $p->supplier?->name }}</td>
                        <td class="py-2.5 px-3.5 text-center font-bold text-slate-600">{{ $p->items->count() }}</td>
                        <td class="py-2.5 px-3.5 text-right">₹{{ number_format($p->subtotal, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-right text-emerald-600">₹{{ number_format($p->discount, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-right">₹{{ number_format($p->tax, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-right font-black text-slate-900">₹{{ number_format($p->grand_total, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-right text-emerald-700 font-bold">₹{{ number_format($p->paid_amount, 2) }}</td>
                        <td class="py-2.5 px-3.5 text-right font-bold {{ $p->outstandingAmount() > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            ₹{{ number_format($p->outstandingAmount(), 2) }}
                        </td>
                        <td class="py-2.5 px-3.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->payment_status?->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($p->payment_status?->value === 'partial' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ ucfirst($p->payment_status?->value ?? 'unpaid') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">No purchase records matching your filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
