@extends('store.layouts.app')

@section('title', 'Expense: ' . $expense->expense_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Expense #{{ $expense->expense_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $expense->isPaid() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($expense->isDraft() ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ ucfirst($expense->status->value) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Recorded on {{ $expense->expense_date->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(! $expense->isCancelled())
            <form method="POST" action="{{ route('store.expenses.cancel', $expense->id) }}" onsubmit="return confirm('Are you sure you want to cancel this expense?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition">
                    Cancel Expense
                </button>
            </form>
            @endif
            <a href="{{ route('store.expenses.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Back to Expenses
            </a>
        </div>
    </div>

    <!-- Details Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-6">
        <div class="flex justify-between items-start border-b border-slate-100 pb-4">
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Expense Title</span>
                <h2 class="text-lg font-black text-slate-900 mt-0.5">{{ $expense->title }}</h2>
                <span class="text-xs text-slate-500 block mt-1">Category: <b>{{ $expense->category?->name ?: 'General Expense' }}</b></span>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold uppercase text-slate-400">Amount</span>
                <p class="text-2xl font-black text-slate-900 mt-0.5">₹{{ number_format($expense->amount, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold">Payment Method</span>
                <span class="font-bold text-slate-800 uppercase">{{ $expense->payment_method }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold">Reference #</span>
                <span class="font-bold text-slate-800">{{ $expense->reference_number ?: 'None' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold">Recorded By</span>
                <span class="font-bold text-slate-800">{{ $expense->creator?->name ?: 'Store Staff' }}</span>
            </div>
            @if($expense->notes)
            <div class="col-span-2 sm:col-span-3">
                <span class="text-slate-400 block font-semibold">Notes / Remarks</span>
                <p class="text-slate-700 mt-0.5 font-medium whitespace-pre-line">{{ $expense->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
