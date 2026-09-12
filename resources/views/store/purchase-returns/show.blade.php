@extends('store.layouts.app')

@section('title', 'Purchase Return: ' . $purchaseReturn->return_number)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Return #{{ $purchaseReturn->return_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ ucfirst($purchaseReturn->status->value) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Returned on {{ $purchaseReturn->return_date->format('d M Y') }} against Purchase #{{ $purchaseReturn->purchase?->invoice_number }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.purchases.show', $purchaseReturn->purchase_id) }}" class="px-3.5 py-2 text-xs font-bold text-[#4b55c8] bg-[#eef2fd] rounded-xl hover:bg-[#e0e8fc] transition">
                View Original Purchase
            </a>
            <a href="{{ route('store.purchase-returns.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Back to Returns
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Total Returned Value</p>
            <p class="text-xl font-black text-slate-900 mt-1">₹{{ number_format($purchaseReturn->grand_total, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Debit Note / Refund</p>
            <p class="text-xl font-black text-rose-600 mt-1">₹{{ number_format($purchaseReturn->refund_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase text-slate-400">Supplier</p>
            <p class="text-base font-bold text-slate-900 mt-1">{{ $purchaseReturn->supplier?->name }}</p>
        </div>
    </div>

    <!-- Returned Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Returned Medicines to Supplier</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Purchase Price</th>
                        <th class="py-3 px-4">Returned Qty</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($purchaseReturn->items as $item)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $item->medicine?->displayName() }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $item->batch?->batch_number }}</td>
                        <td class="py-3 px-4">₹{{ number_format($item->purchase_price, 2) }}</td>
                        <td class="py-3 px-4 font-black text-rose-600">{{ $item->quantity }} units</td>
                        <td class="py-3 px-4 text-slate-500">{{ $item->reason ?: '—' }}</td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
