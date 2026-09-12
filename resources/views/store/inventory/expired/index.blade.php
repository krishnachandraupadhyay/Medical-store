@extends('store.layouts.app')

@section('title', 'Expired Stock Disposal')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.inventory.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Inventory
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">
                    Regulatory Disposal
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Expired Stock Disposal</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">
                Isolate and formally write off expired medicine batches in compliance with pharmacy regulations and drug control guidelines.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-rose-50 border border-rose-200 rounded-2xl px-4 py-2 text-right">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-rose-500">Total Disposed Units</div>
                <div class="text-xl font-black text-rose-700 font-mono">{{ number_format($totalDisposedUnits) }}</div>
            </div>
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

    <!-- Batches Requiring Disposal Notice & Form -->
    <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm">
        <h2 class="text-base font-black text-[#1e2746] tracking-tight mb-1">Process Expired Stock Disposal</h2>
        <p class="text-xs text-slate-500 mb-4">Only batches that have crossed their official expiry date can be processed through this regulatory write-off.</p>

        @if ($expiredBatches->isEmpty())
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 text-xs text-emerald-800">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>No expired stock currently awaiting disposal. Your active stock is clean!</span>
            </div>
        @else
            <form method="POST" action="{{ route('store.inventory.expired.process') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Expired Batch *</label>
                        <select name="batch_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                            <option value="">-- Choose expired batch to dispose --</option>
                            @foreach ($expiredBatches as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->medicine?->name }} (Batch: {{ $b->batch_number }} | Expired: {{ $b->expiry_date->format('d M Y') }} | Stock: {{ $b->quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Disposal Qty *</label>
                        <input type="number" name="quantity" min="1" required placeholder="Units"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-bold text-[#1e2746] focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Disposal Method / Reason *</label>
                        <input type="text" name="reason" required placeholder="e.g. Incineration, biomedical waste disposal, vendor return reject"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Disposal Certificate / Reference Notes</label>
                        <input type="text" name="notes" placeholder="e.g. Biomedical waste manifest #BMW-2026-88, supervisor signed"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:border-[#4b55c8] bg-slate-50/50">
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition whitespace-nowrap">
                        Process Expired Disposal
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Movement Ledger -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-base font-black text-[#1e2746]">Expired Stock Disposal Ledger</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Disposal Date</th>
                        <th class="py-3.5 px-6">Medicine</th>
                        <th class="py-3.5 px-6">Batch #</th>
                        <th class="py-3.5 px-6 text-right">Units Disposed</th>
                        <th class="py-3.5 px-6">Method & Remarks</th>
                        <th class="py-3.5 px-6">Authorized By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[#1e2746]">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 text-slate-600 font-mono">
                                {{ $m->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-4 px-6 font-bold">
                                {{ $m->medicine?->name ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6 font-mono font-bold text-slate-700">
                                {{ $m->batch?->batch_number ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-rose-600">
                                -{{ $m->quantity }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $m->reason }}</div>
                                @if ($m->notes)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $m->notes }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $m->creator?->name ?? 'Store Owner' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No expired stock write-offs recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
