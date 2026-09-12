@extends('store.layouts.app')

@section('title', 'Stock Movement History Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Stock Movement Audit Report</h1>
            <p class="text-sm text-slate-500 mt-1">Immutable ledger history of stock entries, sales, returns, and inventory adjustments.</p>
        </div>
        <a href="{{ route('store.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
            Reports Hub
        </a>
    </div>

    <!-- Filters Bar (PART X) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.reports.stock-movements') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Medicine</label>
                <select name="medicine_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Medicines</option>
                    @foreach($medicines as $m)
                    <option value="{{ $m->id }}" {{ ($filters['medicine_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Movement Type</label>
                <select name="movement_type" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    <option value="">All Movement Types</option>
                    @foreach(\App\Enums\MovementType::cases() as $mt)
                    <option value="{{ $mt->value }}" {{ ($filters['movement_type'] ?? '') === $mt->value ? 'selected' : '' }}>{{ $mt->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                <a href="{{ route('store.reports.stock-movements') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5">Timestamp</th>
                        <th class="py-3 px-3.5">Medicine</th>
                        <th class="py-3 px-3.5">Batch</th>
                        <th class="py-3 px-3.5">Type</th>
                        <th class="py-3 px-3.5 text-center">Movement Qty</th>
                        <th class="py-3 px-3.5 text-center">Before</th>
                        <th class="py-3 px-3.5 text-center">After</th>
                        <th class="py-3 px-3.5">Reason / Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3.5 text-slate-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2.5 px-3.5 font-bold text-slate-900">{{ $m->medicine?->displayName() }}</td>
                        <td class="py-2.5 px-3.5 font-mono font-bold text-slate-800">{{ $m->batch?->batch_number }}</td>
                        <td class="py-2.5 px-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $m->type->badgeClasses() }}">
                                {{ $m->type->label() }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3.5 text-center font-black {{ $m->type->isInflow() ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $m->type->isInflow() ? '+' : '-' }}{{ $m->quantity }}
                        </td>
                        <td class="py-2.5 px-3.5 text-center text-slate-500">{{ $m->before_quantity }}</td>
                        <td class="py-2.5 px-3.5 text-center font-bold text-slate-900">{{ $m->after_quantity }}</td>
                        <td class="py-2.5 px-3.5 text-slate-600 truncate max-w-xs">{{ $m->reason ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">No stock movements found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
