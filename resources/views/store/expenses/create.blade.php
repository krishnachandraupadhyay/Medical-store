@extends('store.layouts.app')

@section('title', 'Record Expense')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Record Operating Expense</h1>
            <p class="text-sm text-slate-500 mt-1">Record utility, rent, maintenance or petty cash disbursements.</p>
        </div>
        <a href="{{ route('store.expenses.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('store.expenses.store') }}" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Expense Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]" placeholder="e.g. Pharmacy Electricity Bill - September">
                @error('title') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category</label>
                    <select name="category_id" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]">
                        <option value="">General Expense</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount (₹) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required class="w-full text-sm font-bold rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none focus:border-[#4b55c8]" placeholder="0.00">
                    @error('amount') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Expense Date *</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                        @foreach($paymentMethods as $m)
                        <option value="{{ $m->value }}">{{ $m->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none">
                        <option value="paid" selected>Paid</option>
                        <option value="draft">Draft / Pending</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reference / Bill #</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none" placeholder="Voucher / Cheque / UTR #">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 outline-none" placeholder="Details about this expense...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
            <a href="{{ route('store.expenses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/25 transition">Record Expense</button>
        </div>
    </form>
</div>
@endsection
