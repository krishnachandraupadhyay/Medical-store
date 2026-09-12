@extends('store.layouts.app')

@section('title', 'Sale Invoice ' . $sale->invoice_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Invoice #{{ $sale->invoice_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $sale->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($sale->isDraft() ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                    {{ ucfirst($sale->status->value) }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $sale->payment_status?->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($sale->payment_status?->value === 'partial' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                    Payment: {{ ucfirst($sale->payment_status?->value ?? 'unpaid') }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Sale Date: {{ $sale->sale_date->format('d M Y') }} • Customer: {{ $sale->customer_name ?: 'Walk-in' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('store.sales.invoice', $sale->id) }}" target="_blank" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Invoice</span>
            </a>

            @if($sale->isCompleted())
            @hasFeature('sales_return')
            <a href="{{ route('store.sales-returns.create', ['sale_id' => $sale->id]) }}" class="px-4 py-2 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition shadow-xs">
                Initiate Return
            </a>
            @endhasFeature
            @endif

            <a href="{{ route('store.sales.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Back to Invoices
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Subtotal</p>
            <p class="text-xl font-black text-slate-900 mt-1">₹{{ number_format($sale->subtotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Grand Total</p>
            <p class="text-xl font-black text-[#4b55c8] mt-1">₹{{ number_format($sale->grand_total, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Paid Amount</p>
            <p class="text-xl font-black text-emerald-600 mt-1">₹{{ number_format($sale->paid_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Outstanding Balance</p>
            <p class="text-xl font-black {{ $sale->outstandingAmount() > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">
                ₹{{ number_format($sale->outstandingAmount(), 2) }}
            </p>
        </div>
    </div>

    <!-- Sale Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Dispensed Medicines & Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Expiry</th>
                        <th class="py-3 px-4">Unit Price</th>
                        <th class="py-3 px-4">Qty</th>
                        <th class="py-3 px-4">Tax Amount</th>
                        <th class="py-3 px-4 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900">{{ $item->medicine?->displayName() }}</span>
                            <span class="text-xs text-slate-400 block">{{ $item->medicine?->generic_name }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $item->batch?->batch_number }}</td>
                        <td class="py-3 px-4">{{ $item->batch?->expiry_date?->format('d M Y') }}</td>
                        <td class="py-3 px-4">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 px-4 font-black text-slate-900">{{ $item->quantity }}</td>
                        <td class="py-3 px-4">₹{{ number_format($item->tax_amount, 2) }}</td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- If Outstanding > 0 and Completed: Record Payment Form -->
    @if($sale->isCompleted() && $sale->outstandingAmount() > 0)
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Record Customer Payment</h2>
        <form method="POST" action="{{ route('store.sales.record-payment', $sale->id) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount (₹) *</label>
                <input type="number" name="amount" value="{{ $sale->outstandingAmount() }}" step="0.01" min="0.01" max="{{ $sale->outstandingAmount() }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none font-bold text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Date *</label>
                <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Method *</label>
                <select name="payment_method" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none">
                    @foreach($paymentMethods as $m)
                    <option value="{{ $m->value }}">{{ $m->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Payment Receipts Ledger -->
    @if($sale->payments->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Payment Transaction History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Reference</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($sale->payments as $p)
                    <tr>
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">{{ $p->payment_number }}</td>
                        <td class="py-3 px-4">{{ $p->payment_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 font-bold">{{ strtoupper($p->payment_method) }}</td>
                        <td class="py-3 px-4">{{ $p->reference_number ?: '—' }}</td>
                        <td class="py-3 px-4 text-right font-black text-emerald-600">₹{{ number_format($p->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
