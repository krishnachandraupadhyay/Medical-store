@extends('store.layouts.app')

@section('title', 'Payments & Transactions Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Payments Report</h1>
            <p class="text-sm text-slate-500 mt-1">Audit of customer collections, vendor payments, and operational cash-flows.</p>
        </div>
        <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
            Reports Hub
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.payments') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Payment Type</label>
                <select name="payment_type" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Types</option>
                    @foreach($paymentTypes as $pt)
                    <option value="{{ $pt->value }}" {{ ($filters['payment_type'] ?? '') === $pt->value ? 'selected' : '' }}>{{ $pt->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Method</label>
                <select name="payment_method" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Modes</option>
                    @foreach($paymentMethods as $pm)
                    <option value="{{ $pm->value }}" {{ ($filters['payment_method'] ?? '') === $pm->value ? 'selected' : '' }}>{{ $pm->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.payments') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Receipt #</th>
                        <th class="py-3 px-3.5">Date</th>
                        <th class="py-3 px-3.5">Type</th>
                        <th class="py-3 px-3.5">Party / Reference</th>
                        <th class="py-3 px-3.5">Method</th>
                        <th class="py-3 px-3.5 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.payments.show', $p->id) }}" class="hover:underline">
                                {{ $p->payment_number }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3.5">{{ $p->payment_date->format('d/m/Y') }}</td>
                        <td class="py-2.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->isSalePayment() ? 'bg-emerald-50 text-emerald-700' : ($p->isPurchasePayment() ? 'bg-purple-50 text-purple-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ $p->payment_type->label() }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3.5">
                            @if($p->isSalePayment())
                                <span class="font-bold text-slate-900 block">{{ $p->sale?->customer_name ?: 'Walk-in' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">Invoice #{{ $p->sale?->invoice_number }}</span>
                            @elseif($p->isPurchasePayment())
                                <span class="font-bold text-slate-900 block">{{ $p->purchase?->supplier?->name ?: 'Supplier' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">Purchase #{{ $p->purchase?->invoice_number }}</span>
                            @else
                                <span class="font-bold text-slate-900 block">{{ $p->expense?->title }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">Expense #{{ $p->expense?->expense_number }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3.5 uppercase font-semibold text-slate-700">{{ $p->payment_method }}</td>
                        <td class="py-2.5 px-3.5 text-right font-black {{ $p->isSalePayment() ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $p->isSalePayment() ? '+' : '-' }}₹{{ number_format($p->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">No payment transaction records found.</td>
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
