@extends('store.layouts.app')

@section('title', 'Expense Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Expense Report</h1>
            <p class="text-sm text-slate-500 mt-1">Audit of store overheads, utilities, and petty cash disbursements.</p>
        </div>
        <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
            Reports Hub
        </a>
    </div>

    <!-- Filters Bar (PART V) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.expenses') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Category</label>
                <select name="category_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Method</label>
                <select name="payment_method" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Payment Modes</option>
                    <option value="cash" {{ ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="upi" {{ ($filters['payment_method'] ?? '') === 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="bank_transfer" {{ ($filters['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="card" {{ ($filters['payment_method'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.expenses') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table (Columns from PART V) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Expense #</th>
                        <th class="py-3 px-3.5">Date</th>
                        <th class="py-3 px-3.5">Category</th>
                        <th class="py-3 px-3.5">Title</th>
                        <th class="py-3 px-3.5 text-right">Amount</th>
                        <th class="py-3 px-3.5">Payment Method</th>
                        <th class="py-3 px-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($expenses as $e)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.expenses.show', $e->id) }}" class="hover:underline">
                                {{ $e->expense_number }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3.5">{{ $e->expense_date->format('d/m/Y') }}</td>
                        <td class="py-2.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                {{ $e->category?->name ?: 'General' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3.5 font-bold text-slate-900">{{ $e->title }}</td>
                        <td class="py-2.5 px-3.5 text-right font-black text-slate-900">₹{{ number_format($e->amount, 2) }}</td>
                        <td class="py-2.5 px-3.5 uppercase font-semibold text-slate-700">{{ $e->payment_method }}</td>
                        <td class="py-2.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $e->isPaid() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($e->status->value) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">No expense records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
