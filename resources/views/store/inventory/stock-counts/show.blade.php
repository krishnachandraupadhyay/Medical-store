@extends('store.layouts.app')

@section('title', 'Stock Count #' . $stockCount->count_number)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header & Status -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.stock-counts.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Stock Counts
                </a>
                <span class="text-slate-300">/</span>
                <span class="font-mono text-xs font-bold text-slate-500">{{ $stockCount->count_number }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">Stock Count #{{ $stockCount->count_number }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider border {{ $stockCount->status->badgeClasses() }}">
                    {{ $stockCount->status->label() }}
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Conducted on {{ $stockCount->count_date->format('d F Y') }} • Scope: <span class="capitalize font-semibold text-slate-700">{{ $stockCount->scope }}</span> • Initiated by {{ $stockCount->creator?->name ?? 'System' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($stockCount->isDraft())
                <form method="POST" action="{{ route('store.inventory.stock-counts.cancel', $stockCount) }}" onsubmit="return confirm('Are you sure you want to cancel and discard this stock count session?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-2xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs sm:text-sm transition">
                        Cancel Session
                    </button>
                </form>

                @if ($stockCount->items->isNotEmpty())
                    <form method="POST" action="{{ route('store.inventory.stock-counts.complete', $stockCount) }}" onsubmit="return confirm('Complete reconciliation? This will atomically update batch quantities and record stock movements for all variances.');">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Complete & Reconcile</span>
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold ml-4">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Audit Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Items Counted</div>
            <div class="text-2xl font-black text-[#1e2746] mt-1">{{ $stockCount->items->count() }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Batches in this session</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Exact Matches</div>
            <div class="text-2xl font-black text-slate-700 mt-1">
                {{ $stockCount->items->filter(fn($i) => $i->variance == 0)->count() }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Physical = System quantity</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Net Variance Units</div>
            <div class="text-2xl font-black mt-1 font-mono {{ $stockCount->total_variance_units > 0 ? 'text-emerald-600' : ($stockCount->total_variance_units < 0 ? 'text-rose-600' : 'text-slate-700') }}">
                {{ $stockCount->total_variance_units > 0 ? '+' : '' }}{{ $stockCount->total_variance_units }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Surplus (+) or Shortage (-)</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Net Variance Cost</div>
            <div class="text-2xl font-black mt-1 font-mono {{ $stockCount->total_variance_cost > 0 ? 'text-emerald-600' : ($stockCount->total_variance_cost < 0 ? 'text-rose-600' : 'text-slate-700') }}">
                {{ $stockCount->total_variance_cost >= 0 ? '₹' : '-₹' }}{{ number_format(abs($stockCount->total_variance_cost), 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Based on purchase cost</div>
        </div>
    </div>

    @if ($stockCount->notes)
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700">
            <span class="font-bold text-slate-900">Session Notes:</span> {{ $stockCount->notes }}
        </div>
    @endif

    <!-- Add Item Form (If Draft) -->
    @if ($stockCount->isDraft() && count($availableBatches) > 0)
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm">
            <h2 class="text-base font-black text-[#1e2746] tracking-tight mb-4">Add Batch to Physical Count</h2>
            <form method="POST" action="{{ route('store.inventory.stock-counts.items.add', $stockCount) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="sm:col-span-6">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Select Medicine Batch *</label>
                    <select name="batch_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                        <option value="">-- Choose batch to audit --</option>
                        @foreach ($availableBatches as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->medicine?->name }} (Batch: {{ $b->batch_number }} | System Stock: {{ $b->quantity }} | Exp: {{ $b->expiry_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Physical Qty *</label>
                    <input type="number" name="physical_quantity" min="0" required placeholder="Counted"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-bold text-[#1e2746] focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Item Notes</label>
                    <input type="text" name="notes" placeholder="Optional audit remark..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                </div>

                <div class="sm:col-span-1">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow transition">
                        + Add
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Count Items Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-black text-[#1e2746]">Counted Items ({{ $stockCount->items->count() }})</h3>
            @if ($stockCount->isCompleted())
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Reconciled by {{ $stockCount->approver?->name ?? 'Store Owner' }} on {{ $stockCount->completed_at?->format('d M Y, h:i A') }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Medicine</th>
                        <th class="py-3.5 px-6">Batch #</th>
                        <th class="py-3.5 px-6 text-right">System Qty</th>
                        <th class="py-3.5 px-6 text-right">Counted Qty</th>
                        <th class="py-3.5 px-6 text-right">Variance Units</th>
                        <th class="py-3.5 px-6 text-right">Unit Cost</th>
                        <th class="py-3.5 px-6 text-right">Variance Cost</th>
                        <th class="py-3.5 px-6">Audit Status</th>
                        @if ($stockCount->isDraft())
                            <th class="py-3.5 px-6 text-right">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[#1e2746]">
                    @forelse ($stockCount->items as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 font-bold">
                                {{ $item->medicine?->name ?? 'Unknown Medicine' }}
                                @if ($item->notes)
                                    <div class="text-[11px] font-normal text-slate-400 mt-0.5">Note: {{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-mono font-bold text-slate-700">
                                {{ $item->batch?->batch_number ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-slate-600">
                                {{ $item->system_quantity }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-[#1e2746]">
                                {{ $item->physical_quantity }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold">
                                @if ($item->isSurplus())
                                    <span class="text-emerald-600">+{{ $item->variance }}</span>
                                @elseif ($item->isShortage())
                                    <span class="text-rose-600">{{ $item->variance }}</span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-mono text-slate-600">
                                ₹{{ number_format($item->unit_cost, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold">
                                @if ($item->variance_cost > 0)
                                    <span class="text-emerald-600">+₹{{ number_format($item->variance_cost, 2) }}</span>
                                @elseif ($item->variance_cost < 0)
                                    <span class="text-rose-600">-₹{{ number_format(abs($item->variance_cost), 2) }}</span>
                                @else
                                    <span class="text-slate-400">₹0.00</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if ($item->isMatch())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">Match</span>
                                @elseif ($item->isSurplus())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">Surplus (+{{ $item->variance }})</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">Shortage ({{ $item->variance }})</span>
                                @endif
                            </td>
                            @if ($stockCount->isDraft())
                                <td class="py-4 px-6 text-right">
                                    <form method="POST" action="{{ route('store.inventory.stock-counts.items.remove', [$stockCount, $item]) }}" onsubmit="return confirm('Remove this item from count?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $stockCount->isDraft() ? 9 : 8 }}" class="py-12 text-center text-slate-400">
                                No items added to this stock count session yet. Use the form above to add batches and physical counts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
