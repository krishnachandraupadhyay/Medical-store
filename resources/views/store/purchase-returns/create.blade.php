@extends('store.layouts.app')

@section('title', 'Process Purchase Return - #' . $purchase->invoice_number)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.purchases.show', $purchase->id) }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Purchase #{{ $purchase->invoice_number }}
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Initiate Purchase Return / Debit Note</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">Return items from Purchase Invoice <b>#{{ $purchase->invoice_number }}</b> to <b>{{ $purchase->supplier?->name }}</b>.</p>
        </div>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold shadow-sm space-y-1">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="returnForm" method="POST" action="{{ route('store.purchase-returns.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

        <!-- Purchase & Supplier Context Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm space-y-1.5">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Supplier</span>
                <div class="text-base font-bold text-[#1e2746]">{{ $purchase->supplier?->name }}</div>
                <div class="text-xs text-slate-500">{{ $purchase->supplier?->phone }}</div>
                @if($purchase->supplier?->gst_number)
                    <div class="text-xs font-mono text-slate-400">GSTIN: {{ $purchase->supplier?->gst_number }}</div>
                @endif
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm space-y-1.5">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Invoice Information</span>
                <div class="text-base font-mono font-bold text-[#1e2746]">#{{ $purchase->invoice_number }}</div>
                <div class="text-xs text-slate-500">Billed: {{ $purchase->purchase_date->format('d M, Y') }}</div>
                <div class="text-xs">
                    <span class="font-bold text-slate-400">Payment Status:</span>
                    <span class="font-bold text-emerald-600 uppercase">{{ $purchase->payment_status?->value ?? 'UNPAID' }}</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm space-y-1.5">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Financial Balance</span>
                <div class="text-xs flex justify-between text-slate-500">
                    <span>Invoice Total:</span>
                    <span class="font-mono font-bold text-[#1e2746]">₹{{ number_format($purchase->grand_total, 2) }}</span>
                </div>
                <div class="text-xs flex justify-between text-emerald-600">
                    <span>Paid to Date:</span>
                    <span class="font-mono font-bold">₹{{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="text-xs flex justify-between text-rose-600 font-bold border-t border-slate-100 pt-1">
                    <span>Current Outstanding:</span>
                    <span class="font-mono font-bold" id="invoiceOutstanding" data-amount="{{ $purchase->outstandingAmount() }}">
                        ₹{{ number_format($purchase->outstandingAmount(), 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Return Table -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-[#1e2746]">Select Items & Quantities to Return</h3>
                    <p class="text-xs text-[#64748b] mt-0.5">Specify return quantities. Quantities returned are instantly deducted from current batch inventory.</p>
                </div>
                <div class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full self-start sm:self-auto">
                    Stock will be decremented on confirmation
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-5">Medicine</th>
                            <th class="py-3.5 px-5">Batch & Expiry</th>
                            <th class="py-3.5 px-4 text-center">Purchased</th>
                            <th class="py-3.5 px-4 text-center">In Stock</th>
                            <th class="py-3.5 px-4 text-center">Max Returnable</th>
                            <th class="py-3.5 px-5 text-right">Unit Rate (₹)</th>
                            <th class="py-3.5 px-5 w-32 text-center">Return Qty</th>
                            <th class="py-3.5 px-5 text-right">Subtotal (₹)</th>
                            <th class="py-3.5 px-5">Line Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-[#1e2746]">
                        @foreach($purchase->items as $idx => $item)
                        @php
                            $alreadyReturned = $item->alreadyReturnedQuantity();
                            $returnable = $item->returnableQuantity();
                            $effectiveRate = $item->effectiveUnitPrice();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition line-row" data-idx="{{ $idx }}" data-rate="{{ $effectiveRate }}">
                            <td class="py-4 px-5">
                                <div class="font-bold text-[#1e2746]">{{ $item->medicine?->displayName() }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->medicine?->generic_name }}</div>
                                <input type="hidden" name="items[{{ $idx }}][purchase_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="py-4 px-5">
                                <div class="font-mono font-bold text-[#4b55c8]">{{ $item->batch_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->expiry_date?->format('m/Y') }}</div>
                            </td>
                            <td class="py-4 px-4 text-center font-mono">
                                {{ $item->totalQuantity() }}
                                @if($alreadyReturned > 0)
                                    <div class="text-[10px] text-amber-600 font-bold">({{ $alreadyReturned }} ret.)</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center font-mono font-bold text-slate-700">
                                {{ $item->batch ? $item->batch->quantity : 0 }}
                            </td>
                            <td class="py-4 px-4 text-center font-mono font-black text-rose-600">
                                {{ $returnable }}
                            </td>
                            <td class="py-4 px-5 text-right font-mono">
                                ₹{{ number_format($effectiveRate, 2) }}
                            </td>
                            <td class="py-4 px-5 text-center">
                                <input type="number"
                                       name="items[{{ $idx }}][quantity]"
                                       value="{{ old("items.$idx.quantity", 0) }}"
                                       min="0"
                                       max="{{ $returnable }}"
                                       class="qty-input w-24 text-center font-bold text-xs sm:text-sm rounded-xl border border-slate-200 px-2 py-1.5 outline-none focus:border-[#4b55c8] focus:ring-1 focus:ring-[#4b55c8] transition {{ $returnable <= 0 ? 'bg-slate-100 cursor-not-allowed text-slate-400' : '' }}"
                                       {{ $returnable <= 0 ? 'disabled' : '' }}>
                            </td>
                            <td class="py-4 px-5 text-right font-mono font-black text-[#1e2746] line-total">
                                ₹0.00
                            </td>
                            <td class="py-4 px-5">
                                <input type="text"
                                       name="items[{{ $idx }}][reason]"
                                       value="{{ old("items.$idx.reason") }}"
                                       placeholder="Damaged / Expired / Quality issue"
                                       class="w-full text-xs rounded-xl border border-slate-200 px-3 py-1.5 outline-none focus:border-[#4b55c8] transition">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Return Total Live Banner -->
            <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    * Line total is calculated based on effective unit cost plus proportional taxes from the original bill.
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-slate-700">Total Return / Debit Value:</span>
                    <span id="calculatedTotalDisplay" class="text-2xl font-mono font-black text-rose-600">₹0.00</span>
                </div>
            </div>
        </div>

        <!-- Settlement & Return Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Return Details Card -->
            <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-extrabold text-[#1e2746] uppercase tracking-wider">Debit Note & Return Details</h3>

                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Return Date *</label>
                    <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8] transition">
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Primary Reason / Remarks *</label>
                    <input type="text" name="reason" value="{{ old('reason', 'Return of goods to supplier') }}" required placeholder="e.g. Expired stock return, batch recall, broken seal" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8] transition">
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Internal Operational Notes</label>
                    <textarea name="notes" rows="3" placeholder="Optional notes for internal store records..." class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8] transition">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Financial Settlement Card -->
            <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-extrabold text-[#1e2746] uppercase tracking-wider">Supplier Settlement Mode</h3>

                <div class="space-y-3">
                    <label class="block p-3.5 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition" id="modeCreditLabel">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="settlement_mode" value="credit" id="modeCredit" class="mt-1" checked onchange="toggleSettlementMode()">
                            <div>
                                <div class="text-xs sm:text-sm font-bold text-[#1e2746]">On-Account Credit / Invoice Adjustment (Recommended)</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Deducts automatically from this invoice's outstanding due (up to ₹{{ number_format($purchase->outstandingAmount(), 2) }}). Any remainder becomes store credit against the supplier ledger.
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="block p-3.5 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition" id="modeRefundLabel">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="settlement_mode" value="refund" id="modeRefund" class="mt-1" onchange="toggleSettlementMode()">
                            <div>
                                <div class="text-xs sm:text-sm font-bold text-[#1e2746]">Immediate Cash / Bank Refund Received</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    The supplier paid back cash or transferred funds directly into your bank account.
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Refund Inputs Container (Visible only when refund mode selected) -->
                <div id="refundDetailsContainer" class="hidden space-y-4 pt-3 border-t border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Refund Method *</label>
                            <select name="refund_method" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none focus:border-[#4b55c8] transition">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer / NEFT / IMPS</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI / QR</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Transaction Ref / UTR #</label>
                            <input type="text" name="refund_reference" placeholder="e.g. UTR / Cheque / Txn ID" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 outline-none focus:border-[#4b55c8] transition">
                        </div>
                    </div>
                </div>

                <!-- Settlement Breakdown Preview -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                    <div class="font-bold text-[#1e2746] text-xs uppercase tracking-wider">Settlement Breakdown Preview</div>
                    <div class="flex justify-between text-slate-600">
                        <span>Invoice Outstanding Offset:</span>
                        <span class="font-mono font-bold text-[#4b55c8]" id="previewAdjustment">₹0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Supplier Credit / Refund Received:</span>
                        <span class="font-mono font-bold text-emerald-600" id="previewRemainder">₹0.00</span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <a href="{{ route('store.purchases.show', $purchase->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs sm:text-sm hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Confirm Return & Generate Debit Note</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = document.querySelectorAll('.qty-input');
    const invoiceOutstanding = parseFloat(document.getElementById('invoiceOutstanding')?.dataset.amount || 0);

    function recalculate() {
        let total = 0;
        let totalQty = 0;

        document.querySelectorAll('.line-row').forEach(row => {
            const qtyInput = row.querySelector('.qty-input');
            const rate = parseFloat(row.dataset.rate || 0);
            const qty = parseFloat(qtyInput?.value || 0);
            const lineTotal = rate * qty;

            row.querySelector('.line-total').textContent = '₹' + lineTotal.toFixed(2);
            total += lineTotal;
            totalQty += qty;
        });

        document.getElementById('calculatedTotalDisplay').textContent = '₹' + total.toFixed(2);

        // Update settlement preview
        const isRefund = document.getElementById('modeRefund')?.checked;
        if (isRefund) {
            document.getElementById('previewAdjustment').textContent = '₹0.00';
            document.getElementById('previewRemainder').textContent = '₹' + total.toFixed(2) + ' (Direct Refund)';
        } else {
            const adj = Math.min(total, invoiceOutstanding);
            const remainder = Math.max(0, total - adj);
            document.getElementById('previewAdjustment').textContent = '₹' + adj.toFixed(2);
            document.getElementById('previewRemainder').textContent = '₹' + remainder.toFixed(2) + ' (Supplier Ledger Credit)';
        }

        const submitBtn = document.getElementById('submitBtn');
        if (totalQty <= 0) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    qtyInputs.forEach(input => {
        input.addEventListener('input', recalculate);
        input.addEventListener('change', recalculate);
    });

    window.toggleSettlementMode = function () {
        const isRefund = document.getElementById('modeRefund')?.checked;
        const container = document.getElementById('refundDetailsContainer');
        if (isRefund) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
        recalculate();
    };

    recalculate();
});
</script>
@endsection
