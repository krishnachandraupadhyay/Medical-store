@extends('store.layouts.app')

@section('title', 'Process Sales Return - ' . $sale->invoice_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Process Sales Return</h1>
            <p class="text-sm text-slate-500 mt-1">Select items to return from completed sale invoice <b>#{{ $sale->invoice_number }}</b></p>
        </div>
        <a href="{{ route('store.sales.show', $sale->id) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Back to Invoice
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc list-inside mt-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('store.sales-returns.store') }}" id="salesReturnForm" class="space-y-6">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

        <!-- Original Invoice Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Original Invoice Summary</h2>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Customer</span>
                    <span class="font-bold text-slate-900">{{ $sale->customer_name ?: ($sale->customer?->name ?: 'Walk-in Customer') }}</span>
                    @if($sale->customer?->customer_code)
                        <span class="text-[11px] text-slate-400 block">Code: {{ $sale->customer->customer_code }}</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 block">Sale Date</span>
                    <span class="font-bold text-slate-900">{{ $sale->sale_date->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Invoice Total</span>
                    <span class="font-bold text-slate-900">₹{{ number_format($sale->grand_total, 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Paid Amount</span>
                    <span class="font-bold text-emerald-600">₹{{ number_format($sale->paid_amount, 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Current Sale Due</span>
                    <span class="font-bold text-rose-600" id="currentSaleDue" data-due="{{ $sale->outstandingAmount() }}">
                        ₹{{ number_format($sale->outstandingAmount(), 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Return Items Selection -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Select Quantities to Return</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Quantities returned will be automatically replenished back into their respective inventory batches.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 font-medium">Estimated Return: </span>
                    <span class="text-base font-black text-slate-900" id="headerReturnTotal">₹0.00</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Medicine</th>
                            <th class="py-3 px-4">Batch</th>
                            <th class="py-3 px-4 text-center">Sold</th>
                            <th class="py-3 px-4 text-center">Already Ret.</th>
                            <th class="py-3 px-4 text-center">Max Returnable</th>
                            <th class="py-3 px-4 text-right">Unit Price</th>
                            <th class="py-3 px-4 w-32 text-center">Return Qty</th>
                            <th class="py-3 px-4 text-right">Est. Refund</th>
                            <th class="py-3 px-4">Item Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($sale->items as $idx => $item)
                        @php
                            $alreadyReturned = $item->returnedQuantity();
                            $returnable = $item->returnableQuantity();
                            $unitPriceWithTax = round((float)$item->unit_price * (1 + ((float)$item->gst_rate / 100)), 2);
                        @endphp
                        <tr class="item-row" data-unit-price="{{ $item->unit_price }}" data-gst-rate="{{ $item->gst_rate }}" data-returnable="{{ $returnable }}">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $item->medicine?->displayName() }}
                                <input type="hidden" name="items[{{ $idx }}][sale_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $item->batch?->batch_number ?: 'N/A' }}
                                @if($item->batch?->expiry_date)
                                    <span class="text-[10px] text-slate-400 block">Exp: {{ $item->batch->expiry_date->format('m/y') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-slate-600">{{ $item->quantity }}</td>
                            <td class="py-3 px-4 text-center text-slate-400">{{ $alreadyReturned }}</td>
                            <td class="py-3 px-4 text-center font-bold {{ $returnable > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                {{ $returnable }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700">
                                ₹{{ number_format($item->unit_price, 2) }}
                                @if($item->gst_rate > 0)
                                    <span class="text-[10px] text-slate-400 block">+{{ $item->gst_rate }}% GST</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="number" 
                                       name="items[{{ $idx }}][quantity]" 
                                       value="{{ old("items.{$idx}.quantity", 0) }}" 
                                       min="0" 
                                       max="{{ $returnable }}" 
                                       class="qty-input w-20 text-center font-bold text-xs rounded-xl border border-slate-200 px-2 py-1.5 outline-none focus:border-[#4b55c8] {{ $returnable <= 0 ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '' }}" 
                                       {{ $returnable <= 0 ? 'disabled' : '' }}
                                       oninput="recalculateTotals()">
                            </td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 line-total-cell font-mono">
                                ₹0.00
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="items[{{ $idx }}][reason]" value="{{ old("items.{$idx}.reason") }}" placeholder="e.g. Broken foil, unconsumed" class="w-full text-xs rounded-xl border border-slate-200 px-2.5 py-1.5 outline-none focus:border-[#4b55c8]">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Settlement & Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Details -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Return & Refund Details</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Return Date *</label>
                        <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Primary Reason *</label>
                        <input type="text" name="reason" value="{{ old('reason', 'Customer Return') }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]" placeholder="e.g. Doctor changed prescription, incorrect strength">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Refund Method</label>
                        <select name="refund_method" id="refundMethodSelect" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]">
                            <option value="cash" {{ old('refund_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="upi" {{ old('refund_method') == 'upi' ? 'selected' : '' }}>UPI / QR Code</option>
                            <option value="card" {{ old('refund_method') == 'card' ? 'selected' : '' }}>Card (Debit / Credit)</option>
                            <option value="bank_transfer" {{ old('refund_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / NEFT</option>
                            <option value="credit_note" {{ old('refund_method') == 'credit_note' ? 'selected' : '' }}>Store Credit Note</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Used if refund amount is paid out to customer.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Notes (Optional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Any additional audit notes..." class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]">
                    </div>
                </div>
            </div>

            <!-- Right Col: Settlement Calculation Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4 flex flex-col justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider mb-3">Settlement Summary</h2>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Total Return Value:</span>
                            <span class="font-black text-slate-900 font-mono text-sm" id="calcReturnTotal">₹0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-600">
                            <span>Adjust Against Sale Due:</span>
                            <div class="text-right">
                                <span class="font-bold text-emerald-600 font-mono" id="calcDueAdjusted">₹0.00</span>
                                <input type="hidden" name="adjustment_amount" id="inputAdjustmentAmount" value="0.00">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                            <span class="font-bold text-slate-800">Net Refund Payable:</span>
                            <div class="text-right">
                                <span class="font-black text-amber-600 font-mono text-base" id="calcRefundPayable">₹0.00</span>
                                <input type="hidden" name="refund_amount" id="inputRefundAmount" value="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-500 space-y-1">
                        <p class="font-semibold text-slate-700">Financial Policy:</p>
                        <p>1. If customer has pending dues on this sale, return value is adjusted first to clear dues.</p>
                        <p>2. Any remaining value is issued as a refund to customer via selected payment method.</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <a href="{{ route('store.sales.show', $sale->id) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">Cancel</a>
                    <button type="submit" id="btnSubmitReturn" class="px-5 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-md shadow-[#4b55c8]/25 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirm Return & Restock
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function recalculateTotals() {
    let grandReturnTotal = 0;
    const currentSaleDue = parseFloat(document.getElementById('currentSaleDue').dataset.due || 0);

    document.querySelectorAll('.item-row').forEach(row => {
        const qtyInput = row.querySelector('.qty-input');
        const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
        const unitPrice = parseFloat(row.dataset.unitPrice || 0);
        const gstRate = parseFloat(row.dataset.gstRate || 0);

        const subtotal = qty * unitPrice;
        const tax = subtotal * (gstRate / 100);
        const lineTotal = subtotal + tax;

        row.querySelector('.line-total-cell').textContent = '₹' + lineTotal.toFixed(2);
        grandReturnTotal += lineTotal;
    });

    grandReturnTotal = Math.round(grandReturnTotal * 100) / 100;

    // Allocate adjustment vs refund
    let dueAdjustment = Math.min(grandReturnTotal, currentSaleDue);
    dueAdjustment = Math.round(dueAdjustment * 100) / 100;

    let refundPayable = Math.max(0, grandReturnTotal - dueAdjustment);
    refundPayable = Math.round(refundPayable * 100) / 100;

    document.getElementById('headerReturnTotal').textContent = '₹' + grandReturnTotal.toFixed(2);
    document.getElementById('calcReturnTotal').textContent = '₹' + grandReturnTotal.toFixed(2);
    document.getElementById('calcDueAdjusted').textContent = '₹' + dueAdjustment.toFixed(2);
    document.getElementById('calcRefundPayable').textContent = '₹' + refundPayable.toFixed(2);

    document.getElementById('inputAdjustmentAmount').value = dueAdjustment.toFixed(2);
    document.getElementById('inputRefundAmount').value = refundPayable.toFixed(2);

    const btnSubmit = document.getElementById('btnSubmitReturn');
    if (grandReturnTotal <= 0) {
        btnSubmit.disabled = true;
    } else {
        btnSubmit.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateTotals();
});
</script>
@endsection
