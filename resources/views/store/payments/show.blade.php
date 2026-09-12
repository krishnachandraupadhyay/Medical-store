@extends('store.layouts.app')

@section('title', 'Payment Receipt: ' . $payment->payment_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Receipt #{{ $payment->payment_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">Transaction recorded on {{ $payment->payment_date->format('d M Y') }}</p>
        </div>
        <a href="{{ route('store.payments.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Back to Payments
        </a>
    </div>

    <!-- Receipt Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-6">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4">
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Transaction Type</span>
                <p class="text-base font-black text-slate-900 mt-0.5">{{ $payment->payment_type->label() }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold uppercase text-slate-400">Amount</span>
                <p class="text-2xl font-black text-emerald-600 mt-0.5">₹{{ number_format($payment->amount, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold">Payment Mode</span>
                <span class="font-bold text-slate-800 uppercase">{{ $payment->payment_method }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold">Reference #</span>
                <span class="font-bold text-slate-800">{{ $payment->reference_number ?: 'N/A' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold">Related Invoice / Document</span>
                @if($payment->isSalePayment())
                    <a href="{{ route('store.sales.show', $payment->sale_id) }}" class="font-mono font-bold text-[#4b55c8] hover:underline">
                        Sale Invoice #{{ $payment->sale?->invoice_number }}
                    </a>
                @elseif($payment->isPurchasePayment())
                    <a href="{{ route('store.purchases.show', $payment->purchase_id) }}" class="font-mono font-bold text-[#4b55c8] hover:underline">
                        Purchase Invoice #{{ $payment->purchase?->invoice_number }}
                    </a>
                @else
                    <a href="{{ route('store.expenses.show', $payment->expense_id) }}" class="font-mono font-bold text-[#4b55c8] hover:underline">
                        Expense #{{ $payment->expense?->expense_number }}
                    </a>
                @endif
            </div>
            <div>
                <span class="text-slate-400 block font-semibold">Recorded By</span>
                <span class="font-bold text-slate-800">{{ $payment->creator?->name ?: 'Staff' }}</span>
            </div>
            @if($payment->notes)
            <div class="col-span-2">
                <span class="text-slate-400 block font-semibold">Notes</span>
                <p class="text-slate-700 mt-0.5 font-medium">{{ $payment->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
