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
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $sale->payment_status?->value === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($sale->payment_status?->value === 'partial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                    Payment: {{ ucfirst($sale->payment_status?->value ?? 'unpaid') }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Sale Date: {{ $sale->sale_date->format('d M Y') }} • Billed to: {{ $sale->customer_name ?: 'Walk-in' }}</p>
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

    <!-- Store & Customer Details Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Store Information -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-[#4b55c8] text-white flex items-center justify-center font-black text-xs">
                    Rx
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 leading-none">{{ $store->name }}</h3>
                    <span class="text-[10px] text-slate-400 font-mono">Store Code: {{ $store->code }}</span>
                </div>
            </div>
            <div class="text-xs text-slate-600 space-y-1">
                <p><span class="font-semibold text-slate-700">Address:</span> {{ $store->address }}, {{ $store->city }}, {{ $store->state }} - {{ $store->pincode ?? $store->postal_code }}</p>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    @if($store->mobile || $store->phone)
                    <p><span class="font-semibold text-slate-700">Phone:</span> {{ $store->mobile ?: $store->phone }}</p>
                    @endif
                    @if($store->email)
                    <p><span class="font-semibold text-slate-700">Email:</span> {{ $store->email }}</p>
                    @endif
                    @if($store->tax_number)
                    <p><span class="font-semibold text-slate-700">GSTIN:</span> <span class="font-mono font-bold text-[#4b55c8]">{{ $store->tax_number }}</span></p>
                    @endif
                    @if($store->dl_number ?? false)
                    <p><span class="font-semibold text-slate-700">DL:</span> <span class="font-mono">{{ $store->dl_number }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer & Billing Overview -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-[11px]">Billed Customer Information</h3>
                @if($sale->customer)
                <a href="{{ route('store.customers.show', $sale->customer_id) }}" class="text-xs font-bold text-[#4b55c8] hover:underline">
                    View Profile →
                </a>
                @endif
            </div>
            <div class="text-xs text-slate-600 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-900">{{ $sale->customer_name ?: 'Walk-in Customer' }}</span>
                    @if($sale->customer)
                    <span class="font-mono text-xs text-slate-500 font-bold">{{ $sale->customer->customer_code }}</span>
                    @endif
                </div>
                <p><span class="font-semibold text-slate-700">Phone:</span> {{ $sale->customer_phone ?: ($sale->customer?->phone ?: 'Not provided') }}</p>
                @if($sale->customer?->email)
                <p><span class="font-semibold text-slate-700">Email:</span> {{ $sale->customer->email }}</p>
                @endif
                @if($sale->customer?->address)
                <p><span class="font-semibold text-slate-700">Address:</span> {{ $sale->customer->address }}, {{ $sale->customer->city }}</p>
                @endif
                @if($sale->customer?->doctor_name)
                <p><span class="font-semibold text-slate-700">Consulting Doctor:</span> Dr. {{ $sale->customer->doctor_name }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Overview Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Subtotal</p>
            <p class="text-xl font-black text-slate-900 mt-1">₹{{ number_format($sale->subtotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Discount</p>
            <p class="text-xl font-black text-amber-600 mt-1">₹{{ number_format($sale->discount, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Tax / GST</p>
            <p class="text-xl font-black text-slate-700 mt-1">₹{{ number_format($sale->tax, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Grand Total</p>
            <p class="text-xl font-black text-[#4b55c8] mt-1">₹{{ number_format($sale->grand_total, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Outstanding Due</p>
            <p class="text-xl font-black {{ $sale->outstandingAmount() > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                ₹{{ number_format($sale->outstandingAmount(), 2) }}
            </p>
        </div>
    </div>

    <!-- Sale Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Dispensed Medicines & Items ({{ $sale->items->count() }})</h2>
            <span class="text-xs text-slate-400 font-semibold">Tender Method: <b class="text-slate-800">{{ strtoupper($sale->payment_method instanceof \App\Enums\PaymentMethod ? $sale->payment_method->value : ($sale->payment_method ?: 'Cash')) }}</b></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Expiry</th>
                        <th class="py-3 px-4">Unit Price</th>
                        <th class="py-3 px-4">Qty</th>
                        <th class="py-3 px-4">Discount</th>
                        <th class="py-3 px-4">GST Rate</th>
                        <th class="py-3 px-4">Tax Amount</th>
                        <th class="py-3 px-4 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td class="py-3 px-4 text-slate-400">{{ $index + 1 }}</td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900">{{ $item->medicine?->displayName() }}</span>
                            @if($item->medicine?->generic_name)
                            <span class="text-xs text-slate-400 block">{{ $item->medicine->generic_name }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $item->batch?->batch_number }}</td>
                        <td class="py-3 px-4">{{ $item->batch?->expiry_date?->format('d M Y') }}</td>
                        <td class="py-3 px-4">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 px-4 font-black text-slate-900">{{ $item->quantity }}</td>
                        <td class="py-3 px-4 text-slate-500">₹{{ number_format($item->discount, 2) }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $item->gst_rate > 0 ? $item->gst_rate.'%' : '0%' }}</td>
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
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Record Customer Payment</h2>
            <span class="text-xs font-bold text-rose-600">Pending Amount: ₹{{ number_format($sale->outstandingAmount(), 2) }}</span>
        </div>
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
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Payment Transaction Receipts (Ledger)</h2>
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
                        <td class="py-3 px-4 font-bold">{{ strtoupper($p->payment_method instanceof \App\Enums\PaymentMethod ? $p->payment_method->value : ($p->payment_method ?: 'Cash')) }}</td>
                        <td class="py-3 px-4">{{ $p->reference_number ?: '—' }}</td>
                        <td class="py-3 px-4 text-right font-black text-emerald-600">₹{{ number_format($p->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Sales Returns against this Invoice (if any) -->
    @if($sale->returns && $sale->returns->count() > 0)
    <div class="bg-white rounded-2xl border border-rose-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-rose-100 bg-rose-50/40 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Processed Returns against this Sale ({{ $sale->returns->count() }})</h2>
                <span class="text-xs text-slate-500">Restocked batch medicines, customer refunds, and dues adjusted.</span>
            </div>
            <span class="font-bold text-xs text-rose-700 font-mono">Total Returned: ₹{{ number_format($sale->totalReturned(), 2) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Return #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4">Returned Items</th>
                        <th class="py-3 px-4">Return Value</th>
                        <th class="py-3 px-4">Refund Paid</th>
                        <th class="py-3 px-4">Due Adjusted</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($sale->returns as $ret)
                    <tr>
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.sales-returns.show', $ret->id) }}" class="hover:underline">
                                {{ $ret->return_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $ret->return_date->format('d M Y') }}</td>
                        <td class="py-3 px-4">{{ $ret->reason }}</td>
                        <td class="py-3 px-4 font-bold">{{ $ret->items->sum('quantity') }} units</td>
                        <td class="py-3 px-4 font-black text-slate-900">₹{{ number_format($ret->grand_total, 2) }}</td>
                        <td class="py-3 px-4 font-black text-amber-600">₹{{ number_format($ret->refund_amount, 2) }}</td>
                        <td class="py-3 px-4 font-black text-emerald-600">₹{{ number_format($ret->adjustment_amount, 2) }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('store.sales-returns.receipt', $ret->id) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-[#4b55c8] hover:bg-slate-100 rounded-lg transition" title="Print Credit Note">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <a href="{{ route('store.sales-returns.show', $ret->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- System Audit Information Card -->
    <div class="bg-slate-50 rounded-2xl border border-slate-200/80 p-4 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span>Created by: <b class="text-slate-800">{{ $sale->creator?->name ?? 'System' }}</b></span>
            @if($sale->created_at)
            <span class="ml-3">Created at: <b class="text-slate-800">{{ $sale->created_at->format('d M Y, h:i A') }}</b></span>
            @endif
        </div>
        <div>
            @if($sale->completed_at)
            <span>Completed at: <b class="text-slate-800">{{ $sale->completed_at->format('d M Y, h:i A') }}</b></span>
            @endif
            @if($sale->updated_by && $sale->updater)
            <span class="ml-3">Last updated by: <b class="text-slate-800">{{ $sale->updater->name }}</b></span>
            @endif
        </div>
    </div>
</div>
@endsection
