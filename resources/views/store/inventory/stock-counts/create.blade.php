@extends('store.layouts.app')

@section('title', 'Start Stock Count Reconciliation')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.stock-counts.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Stock Counts
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500">Initiate Session</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Start New Stock Count</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Create a draft physical audit session. You will be able to record actual counts on the next screen before finalizing reconciliation.
            </p>
        </div>

        <a href="{{ route('store.inventory.stock-counts.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            Cancel
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-sm max-w-3xl">
        <form method="POST" action="{{ route('store.inventory.stock-counts.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Count Date -->
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2">Count Date *</label>
                    <input type="date" name="count_date" value="{{ old('count_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                    <p class="text-[11px] text-slate-400 mt-1">Date when physical inventory was conducted.</p>
                </div>

                <!-- Scope -->
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2">Count Scope *</label>
                    <select name="scope" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                        <option value="full" {{ old('scope') === 'full' ? 'selected' : '' }}>Full Warehouse Audit</option>
                        <option value="category" {{ old('scope') === 'category' ? 'selected' : '' }}>Category Specific Count</option>
                        <option value="partial" {{ old('scope') === 'partial' ? 'selected' : '' }}>Partial / Spot Check</option>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Defines the coverage scope for this audit.</p>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2">Audit Notes / Location Reference</label>
                <textarea name="notes" rows="3" placeholder="e.g. Annual warehouse audit, Shelf A to C physical count..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">{{ old('notes') }}</textarea>
            </div>

            <!-- Notice -->
            <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-start gap-3 text-xs text-blue-800">
                <svg class="w-5 h-5 text-[#4b55c8] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <span class="font-bold">Workflow Information:</span>
                    Creating this session starts a draft stock count. You can add batches, enter physical shelf quantities, review variances and costs, and only when you click <strong>Complete & Reconcile</strong> will the system write off shortages or record surplus movements.
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('store.inventory.stock-counts.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-bold text-xs sm:text-sm hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                    Create Draft & Enter Items →
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
