@extends('store.layouts.app')

@section('title', 'Process Purchase Return - ' . $purchase->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Process Purchase Return</h1>
            <p class="text-sm text-slate-500 mt-1">Return stock to supplier from purchase invoice <b>#{{ $purchase->invoice_number }}</b></p>
        </div>
        <a href="{{ route('store.purchases.show', $purchase->id) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Back to Purchase
        </a>
    </div>

    <form method="POST" action="{{ route('store.purchase-returns.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Purchase Invoice Summary</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Supplier</span>
                    <span class="font-bold text-slate-900">{{ $purchase->supplier?->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Purchase Date</span>
                    <span class="font-bold text-slate-900">{{ $purchase->purchase_date->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Invoice Total</span>
                    <span class="font-bold text-slate-900">₹{{ number_format($purchase->grand_total, 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Payment Status</span>
                    <span class="font-bold text-emerald-600">{{ ucfirst($purchase->payment_status?->value ?? 'unpaid') }}</span>
                </div>
            </div>
        </div>

        <!-- Return Items Selection -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Select Quantities to Return</h2>
                <p class="text-xs text-slate-400 mt-0.5">Quantities returned will be deducted from your active inventory batch.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Medicine</th>
                            <th class="py-3 px-4">Batch</th>
                            <th class="py-3 px-4">Purchased Qty</th>
                            <th class="py-3 px-4">Current Stock</th>
                            <th class="py-3 px-4">Max Returnable</th>
                            <th class="py-3 px-4 w-32">Return Qty</th>
                            <th class="py-3 px-4">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($purchase->items as $idx => $item)
                        @php
                            $alreadyReturned = $item->alreadyReturnedQuantity();
                            $returnable = $item->returnableQuantity();
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $item->medicine?->displayName() }}
                                <input type="hidden" name="items[{{ $idx }}][purchase_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $item->batch?->batch_number }}</td>
                            <td class="py-3 px-4">{{ $item->totalQuantity() }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $item->batch ? $item->batch->quantity : 0 }}</td>
                            <td class="py-3 px-4 font-black text-rose-600">{{ $returnable }}</td>
                            <td class="py-3 px-4">
                                <input type="number" name="items[{{ $idx }}][quantity]" value="0" min="0" max="{{ $returnable }}" class="w-24 text-center font-bold text-xs rounded-xl border border-slate-200 px-2 py-1.5 outline-none focus:border-[#4b55c8]" {{ $returnable <= 0 ? 'disabled' : '' }}>
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="items[{{ $idx }}][reason]" placeholder="Damaged/Expired" class="w-full text-xs rounded-xl border border-slate-200 px-2.5 py-1.5 outline-none">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Return Reason & Date -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Return Date *</label>
                    <input type="date" name="return_date" value="{{ now()->toDateString() }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Primary Reason *</label>
                    <input type="text" name="reason" value="Stock return to supplier" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Debit Note / Refund Amount</label>
                    <input type="number" name="refund_amount" step="0.01" min="0" placeholder="Leave blank to auto-calculate purchase value" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <a href="{{ route('store.purchases.show', $purchase->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/25 transition">
                    Confirm Return & Deduct Stock
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
