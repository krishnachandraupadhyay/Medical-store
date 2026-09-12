@extends('store.layouts.app')

@section('title', 'Purchase Invoice #' . $purchase->invoice_number)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-6xl mx-auto">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.purchases.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Purchases
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">
                    Invoice #{{ $purchase->invoice_number }}
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $purchase->status->badgeClasses() }}">
                    {{ $purchase->status->label() }}
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Billed on {{ $purchase->purchase_date->format('d M, Y') }} • Recorded by {{ $purchase->creator?->name ?? 'User' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($purchase->isDraft())
                <a href="{{ route('store.purchases.edit', $purchase) }}" class="px-4 py-2 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit Draft</span>
                </a>

                <form method="POST" action="{{ route('store.purchases.complete', $purchase) }}" onsubmit="return confirm('Completing this purchase will immediately add all medicine batches and stock quantities into your active inventory. Proceed?');">
                    @csrf
                    <button type="submit" class="px-5 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Complete & Ingest Stock</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('store.purchases.cancel', $purchase) }}" onsubmit="return confirm('Are you sure you want to cancel this draft purchase?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs sm:text-sm transition">
                        Cancel Invoice
                    </button>
                </form>
            @elseif ($purchase->status === \App\Enums\PurchaseStatus::COMPLETED && $purchase->isReturnable())
                <a href="{{ route('store.purchase-returns.create', ['purchase_id' => $purchase->id]) }}" class="px-4 py-2 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-amber-600/25 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4h5a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v2"/>
                    </svg>
                    <span>Return Items</span>
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

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Invoice Details & Supplier Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Supplier Details -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Supplier / Vendor</div>
            <div class="text-base font-bold text-[#1e2746]">
                <a href="{{ route('store.suppliers.show', $purchase->supplier) }}" class="hover:text-[#4b55c8]">
                    {{ $purchase->supplier->name }}
                </a>
            </div>
            @if ($purchase->supplier->company_name)
                <div class="text-xs text-slate-500">{{ $purchase->supplier->company_name }}</div>
            @endif
            <div class="pt-2 border-t border-slate-100 text-xs space-y-1">
                <div><span class="text-slate-400">Phone:</span> <span class="font-semibold text-[#1e2746]">{{ $purchase->supplier->phone }}</span></div>
                @if ($purchase->supplier->gst_number)
                    <div><span class="text-slate-400">GSTIN:</span> <span class="font-mono font-bold text-[#1e2746]">{{ $purchase->supplier->gst_number }}</span></div>
                @endif
            </div>
        </div>

        <!-- Invoice Dates & Lifecycle -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Invoice Timeline</div>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Invoice Date:</span>
                    <span class="font-bold text-[#1e2746] font-mono">{{ $purchase->purchase_date->format('d M, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Status:</span>
                    <span class="font-bold uppercase text-xs {{ $purchase->status === \App\Enums\PurchaseStatus::COMPLETED ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $purchase->status->value }}
                    </span>
                </div>
                @if ($purchase->completed_at)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Stock Ingested:</span>
                        <span class="font-medium text-emerald-700">{{ $purchase->completed_at->format('d M, Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-2">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Invoice Totals</div>
            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-mono font-bold text-[#1e2746]">₹{{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                    <span>Tax (GST):</span>
                    <span class="font-mono font-bold text-[#1e2746]">₹{{ number_format($purchase->tax, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                    <span>Discount:</span>
                    <span class="font-mono font-bold text-rose-600">-₹{{ number_format($purchase->discount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm font-black pt-2 border-t border-slate-100">
                    <span class="text-[#1e2746]">Grand Total:</span>
                    <span class="font-mono text-base text-[#4b55c8]">₹{{ number_format($purchase->grand_total, 2) }}</span>
                </div>
                @if ($purchase->totalReturned() > 0)
                <div class="flex items-center justify-between text-xs text-rose-600 pt-1">
                    <span>Total Returned:</span>
                    <span class="font-mono font-bold">-₹{{ number_format($purchase->totalReturned(), 2) }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between text-xs text-emerald-600 pt-1">
                    <span>Paid Amount:</span>
                    <span class="font-mono font-bold">₹{{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-xs font-bold {{ $purchase->outstandingAmount() > 0 ? 'text-amber-600' : 'text-slate-600' }} pt-1 border-t border-dashed border-slate-200">
                    <span>Outstanding Due:</span>
                    <span class="font-mono font-black">₹{{ number_format($purchase->outstandingAmount(), 2) }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-base font-bold text-[#1e2746]">Purchased Items & Batches</h3>
            <p class="text-xs text-slate-400 mt-0.5">List of pharmaceutical products received in this consignment.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3 px-6">Medicine & Batch</th>
                        <th class="py-3 px-6">Expiry</th>
                        <th class="py-3 px-6 text-center">Paid Qty</th>
                        <th class="py-3 px-6 text-center">Free Qty</th>
                        <th class="py-3 px-6 text-center">Total Ingested</th>
                        <th class="py-3 px-6 text-right">Cost Price</th>
                        <th class="py-3 px-6 text-right">MRP</th>
                        <th class="py-3 px-6 text-right">GST %</th>
                        <th class="py-3 px-6 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                    @foreach ($purchase->items as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-[#1e2746]">
                                    {{ $item->medicine->name }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[11px] font-bold text-[#4b55c8] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                        Batch: {{ $item->batch_number }}
                                    </span>
                                    @if ($item->batch)
                                        <a href="{{ route('store.inventory.show', $item->batch) }}" class="text-[10px] text-slate-400 hover:text-[#4b55c8] underline" title="View batch inventory">
                                            [View Inventory Batch]
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-xs font-mono">
                                {{ $item->expiry_date->format('d M, Y') }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-bold">
                                {{ $item->quantity }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono text-slate-500">
                                {{ $item->free_quantity }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-bold text-[#4b55c8]">
                                {{ $item->totalQuantity() }} {{ $item->medicine->unit?->short_name ?? 'units' }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono">
                                ₹{{ number_format($item->purchase_price, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono text-slate-500">
                                ₹{{ number_format($item->mrp, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono text-xs">
                                {{ $item->gst_rate }}% (₹{{ number_format($item->tax_amount, 2) }})
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-[#1e2746]">
                                ₹{{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchase->notes)
            <div class="p-6 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-600">
                <span class="font-bold text-[#1e2746]">Invoice Notes:</span>
                <p class="mt-1 whitespace-pre-line">{{ $purchase->notes }}</p>
            </div>
        @endif
    </div>

    @if ($purchase->returns->isNotEmpty())
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#1e2746]">Linked Purchase Returns (Debit Notes)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Stock returns and debit note adjustments linked to this purchase invoice.</p>
                </div>
                <span class="text-xs font-bold text-[#4b55c8] bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                    {{ $purchase->returns->count() }} {{ Str::plural('Return', $purchase->returns->count()) }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3 px-6">Return #</th>
                            <th class="py-3 px-6">Date</th>
                            <th class="py-3 px-6 text-center">Items</th>
                            <th class="py-3 px-6 text-right">Debit Note Total</th>
                            <th class="py-3 px-6 text-right">Adjustment</th>
                            <th class="py-3 px-6 text-right">Refund</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-[#1e2746]">
                        @foreach ($purchase->returns as $ret)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-6 font-mono font-bold text-[#4b55c8]">
                                    <a href="{{ route('store.purchase-returns.show', $ret) }}" class="hover:underline">
                                        {{ $ret->return_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-6">{{ $ret->return_date->format('d M, Y') }}</td>
                                <td class="py-3 px-6 text-center">{{ $ret->items->sum('quantity') }} units</td>
                                <td class="py-3 px-6 text-right font-mono font-bold text-rose-600">₹{{ number_format($ret->grand_total, 2) }}</td>
                                <td class="py-3 px-6 text-right font-mono text-slate-700">₹{{ number_format($ret->adjustment_amount, 2) }}</td>
                                <td class="py-3 px-6 text-right font-mono text-emerald-600">₹{{ number_format($ret->refund_amount, 2) }}</td>
                                <td class="py-3 px-6 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ ucfirst($ret->status->value) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-right">
                                    <a href="{{ route('store.purchase-returns.show', $ret) }}" class="px-3 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
