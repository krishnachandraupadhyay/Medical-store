@extends('store.layouts.app')

@section('title', 'Batch: ' . $batch->batch_number . ' - ' . $batch->medicine->name)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-6xl mx-auto">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Inventory
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight">
                    {{ $batch->medicine->name }}
                </h1>
                <span class="font-mono text-sm font-bold text-[#4b55c8] bg-blue-50 px-3 py-1 rounded-xl border border-blue-100">
                    Batch: {{ $batch->batch_number }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $batch->expiryBadgeClasses() }}">
                    {{ $batch->expiryStatusLabel() }}
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                {{ $batch->medicine->generic_name ? 'Generic: ' . $batch->medicine->generic_name . ' • ' : '' }}
                {{ $batch->medicine->dosageForm?->name }} • {{ $batch->medicine->unit?->name }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.inventory.edit', $batch) }}" class="px-4 py-2 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit Batch</span>
            </a>
        </div>
    </div>

    <!-- Alert Banners -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('status') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
            <div class="font-bold mb-1">Adjustment failed:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3 Cards: Stock & Expiry, Pricing, Batch Dates -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Live Stock -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Available Stock</div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-[#1e2746] font-mono">{{ $batch->quantity }}</span>
                <span class="text-sm font-bold text-slate-500">{{ $batch->medicine->unit?->short_name ?? 'units' }}</span>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Reorder Level:</span>
                <span class="font-bold text-[#1e2746]">{{ $batch->medicine->reorder_level }} units</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-400">Batch Status:</span>
                <span class="font-bold uppercase text-xs {{ $batch->status === 'active' ? 'text-emerald-600' : 'text-slate-500' }}">
                    {{ $batch->status }}
                </span>
            </div>
        </div>

        <!-- Card 2: Dates & Expiry -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Dates & Lifespan</div>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Expiry Date:</span>
                    <span class="font-bold text-[#1e2746] font-mono">{{ $batch->expiry_date->format('d M, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Days to Expiry:</span>
                    <span class="font-bold {{ $batch->daysUntilExpiry() < 0 ? 'text-rose-600' : ($batch->daysUntilExpiry() <= 90 ? 'text-amber-600' : 'text-emerald-600') }}">
                        @if ($batch->daysUntilExpiry() < 0)
                            Expired {{ abs($batch->daysUntilExpiry()) }} days ago
                        @else
                            {{ $batch->daysUntilExpiry() }} days remaining
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Manufacturing Date:</span>
                    <span class="font-medium text-[#1e2746] font-mono">
                        {{ $batch->manufacturing_date ? $batch->manufacturing_date->format('d M, Y') : '—' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Pricing Structure -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-3">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Unit Pricing</div>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Purchase Price:</span>
                    <span class="font-bold font-mono text-[#1e2746]">₹{{ number_format($batch->purchase_price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Selling Price:</span>
                    <span class="font-bold font-mono text-[#4b55c8]">₹{{ number_format($batch->selling_price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">MRP:</span>
                    <span class="font-bold font-mono text-[#1e2746]">₹{{ number_format($batch->mrp, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Batch Quarantine & Status Controls (Phase 24) -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-[#1e2746]">Batch Quarantine & Status Control</h3>
                <p class="text-xs text-[#64748b] mt-0.5">
                    Quarantine or block defective/recalled batches to prevent POS sales immediately.
                </p>
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider border {{ $batch->status === 'blocked' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($batch->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                    Current Status: {{ strtoupper($batch->status) }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('store.inventory.batches.status', $batch) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">New Status <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-semibold focus:border-[#4b55c8] outline-none">
                    <option value="active" {{ $batch->status === 'active' ? 'selected' : '' }}>Active (Available for Sale)</option>
                    <option value="blocked" {{ $batch->status === 'blocked' ? 'selected' : '' }}>Blocked / Quarantined (Hold from Sale)</option>
                    <option value="inactive" {{ $batch->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Reason for Status Change <span class="text-rose-500">*</span></label>
                <input type="text" name="reason" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Quality complaint investigation, manufacturer recall, inspection cleared">
            </div>

            <div>
                <button type="submit" class="w-full px-5 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm transition shadow-sm">
                    Update Batch Status
                </button>
            </div>
        </form>
    </div>

    <!-- Stock Adjustment Section -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-sm font-extrabold text-[#1e2746] mb-1">Atomic Stock Adjustment</h3>
        <p class="text-xs text-[#64748b] mb-4">
            Manually increase or decrease available inventory for damage, discrepancy, write-off, or correction. Each adjustment is logged in the movement ledger.
        </p>

        <form method="POST" action="{{ route('store.inventory.adjust', $batch) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Adjustment Action <span class="text-rose-500">*</span></label>
                <select name="type" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-semibold focus:border-[#4b55c8] outline-none">
                    <option value="ADJUSTMENT_IN">Stock IN (+ Increase Stock)</option>
                    <option value="ADJUSTMENT_OUT">Stock OUT (- Decrease Stock)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Quantity <span class="text-rose-500">*</span></label>
                <input type="number" min="1" name="quantity" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="Units count">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Reason <span class="text-rose-500">*</span></label>
                <input type="text" name="reason" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="e.g. Broken bottle, audit count, sample">
            </div>

            <div>
                <button type="submit" class="w-full px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition">
                    Apply Adjustment
                </button>
            </div>
        </form>
    </div>

    <!-- Batch Stock Movement History Ledger -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-[#1e2746]">Stock Movement History</h3>
                <p class="text-xs text-slate-400 mt-0.5">Chronological audit ledger of all stock transactions for this batch.</p>
            </div>
        </div>

        @if ($movements->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400">
                No movements recorded yet for this batch.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3 px-6">Timestamp</th>
                            <th class="py-3 px-6">Type</th>
                            <th class="py-3 px-6 text-center">Change Qty</th>
                            <th class="py-3 px-6 text-center">Before → After</th>
                            <th class="py-3 px-6">Reason / Notes</th>
                            <th class="py-3 px-6 text-right">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm font-medium text-[#1e2746]">
                        @foreach ($movements as $m)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-6 text-xs text-slate-500 font-mono">
                                    {{ $m->created_at->format('d M, Y H:i') }}
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
