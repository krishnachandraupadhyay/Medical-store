@extends('store.layouts.app')

@section('title', 'Payments & Transactions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Payments & Settlement Ledger</h1>
            <p class="text-sm text-slate-500 mt-1">Audit customer receipts, supplier disbursements, and operating expense payments.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <select name="payment_type" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Payment Types</option>
                    @foreach($paymentTypes as $t)
                    <option value="{{ $t->value }}" {{ request('payment_type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="payment_method" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Payment Methods</option>
                    @foreach($paymentMethods as $m)
                    <option value="{{ $m->value }}" {{ request('payment_method') === $m->value ? 'selected' : '' }}>{{ $m->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.payments.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Receipt #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Party / Reference</th>
                        <th class="py-3.5 px-4">Method</th>
                        <th class="py-3.5 px-4 text-right">Amount</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.payments.show', $p->id) }}" class="hover:underline">
                                {{ $p->payment_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $p->payment_date->format('d M Y') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->isSalePayment() ? 'bg-emerald-50 text-emerald-700' : ($p->isPurchasePayment() ? 'bg-purple-50 text-purple-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ $p->payment_type->label() }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($p->isSalePayment())
                                <span class="font-bold text-slate-900 block">{{ $p->sale?->customer_name ?: 'Walk-in' }}</span>
                                <span class="text-xs text-slate-400 font-mono">Invoice #{{ $p->sale?->invoice_number }}</span>
                            @elseif($p->isPurchasePayment())
                                <span class="font-bold text-slate-900 block">{{ $p->purchase?->supplier?->name ?: 'Supplier' }}</span>
                                <span class="text-xs text-slate-400 font-mono">Purchase #{{ $p->purchase?->invoice_number }}</span>
                            @else
                                <span class="font-bold text-slate-900 block">{{ $p->expense?->title }}</span>
                                <span class="text-xs text-slate-400 font-mono">Expense #{{ $p->expense?->expense_number }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 uppercase font-semibold text-slate-700">{{ $p->payment_method }}</td>
                        <td class="py-3 px-4 text-right font-black {{ $p->isSalePayment() ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $p->isSalePayment() ? '+' : '-' }}₹{{ number_format($p->amount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('store.payments.show', $p->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">No payment transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
