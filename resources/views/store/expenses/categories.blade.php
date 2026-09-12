@extends('store.layouts.app')

@section('title', 'Expense Categories')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Expense Categories</h1>
            <p class="text-sm text-slate-500 mt-1">Classify pharmacy operating expenditures into standardized ledger categories.</p>
        </div>
        <a href="{{ route('store.expenses.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            Back to Expenses
        </a>
    </div>

    <!-- Create Category Form & List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left: Add Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Add New Category</h2>
            <form method="POST" action="{{ route('store.expense-categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category Name *</label>
                    <input type="text" name="name" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]" placeholder="e.g. Utility Bills, Rent, Packaging">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]" placeholder="Optional notes"></textarea>
                </div>
                <button type="submit" class="w-full py-2 bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs rounded-xl shadow-sm transition">
                    Save Category
                </button>
            </form>
        </div>

        <!-- Right: Existing Categories List -->
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Existing Categories</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($categories as $cat)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 transition">
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">{{ $cat->name }}</span>
                        @if($cat->description)
                        <span class="text-xs text-slate-400 block mt-0.5">{{ $cat->description }}</span>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        {{ $cat->expenses_count }} expenses
                    </span>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    No expense categories configured yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
