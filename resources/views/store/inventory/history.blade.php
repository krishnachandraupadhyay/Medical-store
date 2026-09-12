@extends('store.layouts.app')

@section('title', 'Stock Movement Ledger')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Top Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Batches
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Stock Movement Ledger</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Complete, immutable audit trail of all inventory movements across purchases, opening balances, and adjustments.
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.inventory.history') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <select name="type" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Movement Types</option>
                    <option value="OPENING_STOCK" {{ request('type') === 'OPENING_STOCK' ? 'selected' : '' }}>Opening Stock</option>
                    <option value="PURCHASE_IN" {{ request('type') === 'PURCHASE_IN' ? 'selected' : '' }}>Purchase In</option>
                    <option value="SALE_OUT" {{ request('type') === 'SALE_OUT' ? 'selected' : '' }}>Sale Out</option>
                    <option value="SALES_RETURN_IN" {{ request('type') === 'SALES_RETURN_IN' ? 'selected' : '' }}>Sales Return In</option>
                    <option value="PURCHASE_RETURN_OUT" {{ request('type') === 'PURCHASE_RETURN_OUT' ? 'selected' : '' }}>Purchase Return Out</option>
                    <option value="ADJUSTMENT_IN" {{ request('type') === 'ADJUSTMENT_IN' ? 'selected' : '' }}>Adjustment In</option>
                    <option value="ADJUSTMENT_OUT" {{ request('type') === 'ADJUSTMENT_OUT' ? 'selected' : '' }}>Adjustment Out</option>
                    <option value="STOCK_RECONCILIATION_IN" {{ request('type') === 'STOCK_RECONCILIATION_IN' ? 'selected' : '' }}>Stock Reconciliation In (Surplus)</option>
                    <option value="STOCK_RECONCILIATION_OUT" {{ request('type') === 'STOCK_RECONCILIATION_OUT' ? 'selected' : '' }}>Stock Reconciliation Out (Shortage)</option>
                    <option value="DAMAGED_STOCK_OUT" {{ request('type') === 'DAMAGED_STOCK_OUT' ? 'selected' : '' }}>Damaged Stock Out</option>
                    <option value="LOST_STOCK_OUT" {{ request('type') === 'LOST_STOCK_OUT' ? 'selected' : '' }}>Lost Stock Out</option>
                    <option value="EXPIRED_STOCK_OUT" {{ request('type') === 'EXPIRED_STOCK_OUT' ? 'selected' : '' }}>Expired Stock Out</option>
                </select>
            </div>

            <div>
                <select name="medicine_id" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                    <option value="">All Medicines</option>
                    @foreach ($medicines as $med)
                        <option value="{{ $med->id }}" {{ request('medicine_id') == $med->id ? 'selected' : '' }}>
                            {{ $med->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <input type="text" name="batch_id" value="{{ request('batch_id') }}" placeholder="Filter by Batch ID (optional)" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition">
                    Filter Ledger
                </button>
                @if (request()->hasAny(['type', 'medicine_id', 'batch_id']))
                    <a href="{{ route('store.inventory.history') }}" class="px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        @if ($movements->isEmpty())
            <div class="p-12 text-center text-xs text-slate-400">
                No stock movements match your criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3 px-6">Date & Time</th>
                            <th class="py-3 px-6">Medicine & Batch</th>
                            <th class="py-3 px-6">Movement Type</th>
                            <th class="py-3 px-6 text-center">Change Qty</th>
                            <th class="py-3 px-6 text-center">Balance Before → After</th>
                            <th class="py-3 px-6">Reason / Ref</th>
                            <th class="py-3 px-6 text-right">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                        @foreach ($movements as $m)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-6 text-xs text-slate-500 font-mono">
                                    {{ $m->created_at->format('d M, Y H:i:s') }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="font-bold text-[#1e2746]">
                                        {{ $m->medicine->name }}
                                    </div>
                                    <div class="font-mono text-[11px] text-[#4b55c8]">
                                        Batch: {{ $m->batch?->batch_number ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $m->type->badgeClasses() }}">
                                        {{ $m->type->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-center font-mono font-bold">
                                    <span class="{{ $m->type->isAddition() ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $m->type->isAddition() ? '+' : '-' }}{{ $m->quantity }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-center font-mono text-xs text-slate-600">
                                    {{ $m->before_quantity }} → <strong class="text-[#1e2746]">{{ $m->after_quantity }}</strong>
                                </td>
                                <td class="py-3.5 px-6 text-xs">
                                    <div class="font-semibold text-[#1e2746]">{{ $m->reason }}</div>
                                    @if ($m->notes)
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $m->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right text-xs text-slate-500">
                                    {{ $m->creator?->name ?? 'System' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
