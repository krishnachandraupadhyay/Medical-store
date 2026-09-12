@extends('store.layouts.app')

@section('title', 'Sales Return: ' . $salesReturn->return_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Return #{{ $salesReturn->return_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ ucfirst($salesReturn->status->value) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Processed on {{ $salesReturn->return_date->format('d M Y') }} against Sale Invoice 
                <a href="{{ route('store.sales.show', $salesReturn->sale_id) }}" class="font-bold text-[#4b55c8] hover:underline">
                    #{{ $salesReturn->sale?->invoice_number }}
                </a>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.sales-returns.receipt', $salesReturn->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3f49b8] rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt / Credit Note
            </a>
            <a href="{{ route('store.sales.show', $salesReturn->sale_id) }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                View Original Sale
            </a>
            <a href="{{ route('store.sales-returns.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Back to Returns
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Total Returned Value</p>
            <p class="text-2xl font-black text-slate-900 mt-1">₹{{ number_format($salesReturn->grand_total, 2) }}</p>
            <span class="text-[11px] text-slate-400">Restocked medicine value</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Refund Amount Paid</p>
            <p class="text-2xl font-black text-amber-600 mt-1">₹{{ number_format($salesReturn->refund_amount, 2) }}</p>
            @if($salesReturn->refund_method)
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 uppercase">
                    Via {{ str_replace('_', ' ', $salesReturn->refund_method) }}
                </span>
            @else
                <span class="text-[11px] text-slate-400">No cash payout</span>
            @endif
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Adjusted Against Dues</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($salesReturn->adjustment_amount, 2) }}</p>
            <span class="text-[11px] text-slate-400">Reduced invoice pending balance</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Total Units Restocked</p>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ $salesReturn->items->sum('quantity') }}</p>
            <span class="text-[11px] text-slate-400">Across {{ $salesReturn->items->count() }} batch(es)</span>
        </div>
    </div>

    <!-- Details Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Return Info Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Return Information</h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Return Number:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $salesReturn->return_number }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Return Date:</span>
                    <span class="font-bold text-slate-800">{{ $salesReturn->return_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Primary Reason:</span>
                    <span class="font-semibold text-slate-800">{{ $salesReturn->reason }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Original Sale Date:</span>
                    <span class="font-medium text-slate-800">{{ $salesReturn->sale?->sale_date?->format('d M Y') ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Processed By:</span>
                    <span class="font-medium text-slate-800">{{ $salesReturn->creator?->name ?: 'Store Admin' }}</span>
                </div>
                @if($salesReturn->notes)
                <div class="pt-2">
                    <span class="text-slate-500 block mb-1">Notes:</span>
                    <p class="p-2 bg-slate-50 rounded-xl text-slate-700 italic">{{ $salesReturn->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Profile Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Customer Profile</h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Customer Name:</span>
                    <span class="font-bold text-slate-900">{{ $salesReturn->customer?->name ?: ($salesReturn->sale?->customer_name ?: 'Walk-in Customer') }}</span>
                </div>
                @if($salesReturn->customer)
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Customer Code:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $salesReturn->customer->customer_code }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Phone:</span>
                    <span class="font-medium text-slate-800">{{ $salesReturn->customer->phone ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Doctor Referred:</span>
                    <span class="font-medium text-slate-800">{{ $salesReturn->customer->doctor_name ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Current Outstanding:</span>
                    <span class="font-bold text-rose-600">₹{{ number_format($salesReturn->customer->outstandingAmount(), 2) }}</span>
                </div>
                @else
                <div class="text-slate-400 italic py-2">
                    Direct counter sale with no customer profile attached.
                </div>
                @endif
            </div>
        </div>

        <!-- Settlement & Refund Ledger Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Financial Breakdown</h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Subtotal:</span>
                    <span class="font-mono font-bold text-slate-800">₹{{ number_format($salesReturn->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">GST / Tax Restored:</span>
                    <span class="font-mono font-bold text-slate-800">₹{{ number_format($salesReturn->tax, 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-900 font-bold">Grand Total:</span>
                    <span class="font-mono font-black text-slate-900 text-sm">₹{{ number_format($salesReturn->grand_total, 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 text-emerald-700">
                    <span class="font-bold">Adjusted Towards Dues:</span>
                    <span class="font-mono font-bold">₹{{ number_format($salesReturn->adjustment_amount, 2) }}</span>
                </div>
                <div class="flex justify-between py-1 text-amber-700">
                    <span class="font-bold">Refund Paid to Customer:</span>
                    <span class="font-mono font-black text-sm">₹{{ number_format($salesReturn->refund_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Restocked Medicine Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Restocked Medicine Batches</h2>
            <span class="text-xs text-slate-400">Stock automatically restored to inventory ledger</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Expiry</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-center">Returned Qty</th>
                        <th class="py-3 px-4 text-right">Tax (GST)</th>
                        <th class="py-3 px-4 text-right">Line Total</th>
                        <th class="py-3 px-4">Item Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($salesReturn->items as $item)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $item->medicine?->displayName() }}
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            {{ $item->batch?->batch_number ?: 'N/A' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $item->batch?->expiry_date?->format('d M Y') ?: '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono">
                            ₹{{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                +{{ $item->quantity }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-600">
                            ₹{{ number_format($item->tax_amount, 2) }}
                            <span class="text-[10px] text-slate-400 block">({{ $item->gst_rate }}%)</span>
                        </td>
                        <td class="py-3 px-4 text-right font-black text-slate-900 font-mono">
                            ₹{{ number_format($item->line_total, 2) }}
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $item->reason ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
