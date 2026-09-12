@extends('store.layouts.app')

@section('title', 'Sales Returns')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sales Returns</h1>
            <p class="text-sm text-slate-500 mt-1">Customer medicine returns, batch stock replenishment, and refund notes.</p>
        </div>
        <a href="{{ route('store.sales.index') }}" class="px-4 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/20 transition">
            Initiate Return from Sale
        </a>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Return #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Original Sale</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Returned Items</th>
                        <th class="py-3.5 px-4">Total Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($returns as $ret)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            <a href="{{ route('store.sales-returns.show', $ret->id) }}" class="hover:underline">
                                {{ $ret->return_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $ret->return_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            <a href="{{ route('store.sales.show', $ret->sale_id) }}" class="hover:underline text-slate-700">
                                {{ $ret->sale?->invoice_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $ret->customer?->name ?: 'Walk-in Customer' }}</td>
                        <td class="py-3 px-4">{{ $ret->items->sum('quantity') }} units</td>
                        <td class="py-3 px-4 font-black text-rose-600">₹{{ number_format($ret->grand_total, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ ucfirst($ret->status->value) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('store.sales-returns.show', $ret->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            No sales returns recorded.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
