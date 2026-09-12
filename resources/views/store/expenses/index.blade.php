@extends('store.layouts.app')

@section('title', 'Operating Expenses')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Operating Expenses</h1>
            <p class="text-sm text-slate-500 mt-1">Track store overheads, utility bills, rent, and operational disbursements.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.expense-categories.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-xs transition">
                Manage Categories
            </a>
            <a href="{{ route('store.expenses.create') }}" class="px-4 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/20 transition">
                + Record Expense
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title or Expense #..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <select name="category_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.expenses.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Expense #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Title</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Method</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.expenses.show', $exp->id) }}" class="hover:underline">
                                {{ $exp->expense_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $exp->expense_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $exp->title }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                {{ $exp->category?->name ?: 'General' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">{{ strtoupper($exp->payment_method) }}</td>
                        <td class="py-3 px-4 font-black text-slate-900">₹{{ number_format($exp->amount, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $exp->isPaid() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($exp->isDraft() ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($exp->status->value) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('store.expenses.show', $exp->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            No expenses recorded.
                        </td>
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
