@extends('store.layouts.app')

@section('title', 'Debit Note / Return #' . $purchaseReturn->return_number)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.purchase-returns.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Purchase Returns
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">
                    Debit Note #{{ $purchaseReturn->return_number }}
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ ucfirst($purchaseReturn->status->value) }}
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Processed on {{ $purchaseReturn->return_date->format('d M, Y') }} • Recorded by {{ $purchaseReturn->creator?->name ?? 'Store Staff' }}
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('store.purchase-returns.print', $purchaseReturn->id) }}" target="_blank" class="px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Debit Note</span>
            </a>
            @if($purchaseReturn->purchase)
            <a href="{{ route('store.purchases.show', $purchaseReturn->purchase_id) }}" class="px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                View Purchase Bill
            </a>
            @endif
        </div>
    </div>

    <!-- Alert Banners -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('status') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    <!-- Cards: Context & Financial Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Supplier Details -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Supplier / Vendor</div>
            <div class="text-base font-bold text-[#1e2746]">
                @if($purchaseReturn->supplier)
                    <a href="{{ route('store.suppliers.show', $purchaseReturn->supplier_id) }}" class="hover:text-[#4b55c8]">
                        {{ $purchaseReturn->supplier->name }}
                    </a>
                @else
                    <span>Supplier</span>
                @endif
            </div>
            @if($purchaseReturn->supplier?->company_name)
                <div class="text-xs text-slate-500">{{ $purchaseReturn->supplier->company_name }}</div>
            @endif
            <div class="pt-2 border-t border-slate-100 text-xs space-y-1">
                <div><span class="text-slate-400">Phone:</span> <span class="font-semibold text-[#1e2746]">{{ $purchaseReturn->supplier?->phone }}</span></div>
                @if($purchaseReturn->supplier?->gst_number)
                    <div><span class="text-slate-400">GSTIN:</span> <span class="font-mono font-bold text-[#1e2746]">{{ $purchaseReturn->supplier->gst_number }}</span></div>
                @endif
                <div class="pt-1">
                    <a href="{{ route('store.suppliers.ledger', $purchaseReturn->supplier_id) }}" class="text-xs font-bold text-[#4b55c8] hover:underline">
                        View Supplier Ledger Statement →
                    </a>
                </div>
            </div>
        </div>

        <!-- Return Details & Original Invoice -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Original Invoice & Timeline</div>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Purchase Bill:</span>
                    <span class="font-bold text-[#1e2746] font-mono">
                        @if($purchaseReturn->purchase)
                            <a href="{{ route('store.purchases.show', $purchaseReturn->purchase_id) }}" class="text-[#4b55c8] underline">
                                #{{ $purchaseReturn->purchase->invoice_number }}
                            </a>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Return Date:</span>
                    <span class="font-bold text-[#1e2746] font-mono">{{ $purchaseReturn->return_date->format('d M, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Total Items Returned:</span>
                    <span class="font-bold text-rose-600 font-mono">{{ $purchaseReturn->items->sum('quantity') }} units</span>
                </div>
            </div>
            @if($purchaseReturn->reason)
                <div class="pt-2 border-t border-slate-100 text-xs text-slate-600">
                    <span class="text-slate-400 font-bold block">Primary Reason:</span>
                    <p class="mt-0.5">{{ $purchaseReturn->reason }}</p>
                </div>
            @endif
        </div>

        <!-- Financial Impact Breakdown -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-2">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Debit Note Financials</div>
            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-sm font-black pb-1">
                    <span class="text-[#1e2746]">Gross Return Value:</span>
                    <span class="font-mono text-base text-rose-600">₹{{ number_format($purchaseReturn->grand_total, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500 pt-1 border-t border-slate-100">
                    <span>On-Account Invoice Adjustment:</span>
                    <span class="font-mono font-bold text-[#4b55c8]">₹{{ number_format($purchaseReturn->adjustment_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                    <span>Direct Cash/Bank Refund:</span>
                    <span class="font-mono font-bold text-emerald-600">₹{{ number_format($purchaseReturn->refund_amount, 2) }}</span>
                </div>
                @if($purchaseReturn->refund_method)
                <div class="flex items-center justify-between text-slate-400 text-[11px]">
                    <span>Refund Method:</span>
                    <span class="font-bold uppercase text-slate-600">{{ str_replace('_', ' ', $purchaseReturn->refund_method) }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between text-slate-500 pt-1 border-t border-dashed border-slate-200">
                    <span>Supplier Balance Impact:</span>
                    <span class="font-mono font-bold text-emerald-600">-₹{{ number_format($purchaseReturn->grand_total, 2) }} Credit</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Returned Line Items Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-base font-bold text-[#1e2746]">Returned Medicines & Stock Adjustments</h3>
            <p class="text-xs text-slate-400 mt-0.5">Quantities below have been returned to the supplier and deducted from store inventory.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3 px-6">Medicine & Batch</th>
                        <th class="py-3 px-6 text-center">Returned Qty</th>
                        <th class="py-3 px-6 text-right">Unit Rate</th>
                        <th class="py-3 px-6 text-right">Tax (GST)</th>
                        <th class="py-3 px-6 text-right">Line Total</th>
                        <th class="py-3 px-6">Return Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                    @foreach ($purchaseReturn->items as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-[#1e2746]">
                                    {{ $item->medicine?->displayName() }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[11px] font-bold text-[#4b55c8] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                        Batch: {{ $item->batch?->batch_number ?? 'N/A' }}
                                    </span>
                                    @if ($item->batch?->expiry_date)
                                        <span class="text-[10px] text-slate-400">
                                            Exp: {{ $item->batch->expiry_date->format('m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-black text-rose-600">
                                {{ $item->quantity }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono">
                                ₹{{ number_format($item->purchase_price, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono text-slate-500">
                                ₹{{ number_format($item->tax_amount, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-[#1e2746]">
                                ₹{{ number_format($item->line_total, 2) }}
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $item->reason ?: 'Damaged / Expired stock return' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchaseReturn->notes)
            <div class="p-6 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-600">
                <span class="font-bold text-[#1e2746]">Internal Notes:</span>
                <p class="mt-1 whitespace-pre-line">{{ $purchaseReturn->notes }}</p>
            </div>
        @endif
    </div>

</div>
@endsection
