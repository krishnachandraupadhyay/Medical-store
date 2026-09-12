@extends('store.layouts.app')

@section('title', 'Process Sales Return - ' . $sale->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Process Sales Return</h1>
            <p class="text-sm text-slate-500 mt-1">Returning items from completed invoice <b>#{{ $sale->invoice_number }}</b></p>
        </div>
        <a href="{{ route('store.sales.show', $sale->id) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Back to Invoice
        </a>
    </div>

    <form method="POST" action="{{ route('store.sales-returns.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <h2 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Original Invoice Details</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Customer</span>
                    <span class="font-bold text-slate-900">{{ $sale->customer_name ?: 'Walk-in Customer' }}</span>
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
                    <span class="text-slate-400 block">Payment Status</span>
                    <span class="font-bold text-emerald-600">{{ ucfirst($sale->payment_status?->value) }}</span>
                </div>
            </div>
        </div>

        <!-- Return Items Selection -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Select Quantities to Return</h2>
                <p class="text-xs text-slate-400 mt-0.5">Quantities returned will be automatically replenished back into their respective inventory batches.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Medicine</th>
                            <th class="py-3 px-4">Batch</th>
                            <th class="py-3 px-4">Sold Qty</th>
                            <th class="py-3 px-4">Already Returned</th>
                            <th class="py-3 px-4">Max Returnable</th>
                            <th class="py-3 px-4 w-32">Return Qty</th>
                            <th class="py-3 px-4">Item Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($sale->items as $idx => $item)
                        @php
                            $alreadyReturned = $item->alreadyReturnedQuantity();
                            $returnable = $item->returnableQuantity();
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $item->medicine?->displayName() }}
                                <input type="hidden" name="items[{{ $idx }}][sale_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $item->batch?->batch_number }}</td>
                            <td class="py-3 px-4">{{ $item->quantity }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $alreadyReturned }}</td>
                            <td class="py-3 px-4 font-black text-emerald-600">{{ $returnable }}</td>
                            <td class="py-3 px-4">
                                <input type="number" name="items[{{ $idx }}][quantity]" value="0" min="0" max="{{ $returnable }}" class="w-24 text-center font-bold text-xs rounded-xl border border-slate-200 px-2 py-1.5 outline-none focus:border-[#4b55c8]" {{ $returnable <= 0 ? 'disabled' : '' }}>
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="items[{{ $idx }}][reason]" placeholder="Optional reason" class="w-full text-xs rounded-xl border border-slate-200 px-2.5 py-1.5 outline-none">
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
                    <input type="text" name="reason" value="Customer Return" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none" placeholder="e.g. Doctor changed prescription, incorrect strength">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Refund Amount / Note</label>
                    <input type="number" name="refund_amount" step="0.01" min="0" placeholder="Leave empty to auto-calculate item refund" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <a href="{{ route('store.sales.show', $sale->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/25 transition">
                    Confirm Return & Restock
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
