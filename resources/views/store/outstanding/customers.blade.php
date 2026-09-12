@extends('store.layouts.app')

@section('title', 'Customer Receivables (Outstanding)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Receivables</h1>
            <p class="text-sm text-slate-500 mt-1">Outstanding amounts due from customer medicine sales.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.outstanding.suppliers') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-xs transition">
                View Supplier Payables
            </a>
        </div>
    </div>

    <!-- Total Receivables Card -->
    <div class="bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] rounded-2xl p-6 text-white shadow-lg shadow-[#4b55c8]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-white/80">Total Customer Outstanding (Receivables)</span>
            <p class="text-3xl font-black mt-1">₹{{ number_format($totalOutstanding, 2) }}</p>
        </div>
        <div class="text-xs text-white/90 max-w-xs">
            Sum total of unpaid and partial invoice balances from completed sales.
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('store.outstanding.customers') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search customer by name or phone..." class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2 outline-none focus:border-[#4b55c8]">
            <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition flex-shrink-0">Search</button>
            @if($search)
            <a href="{{ route('store.outstanding.customers') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition flex-shrink-0">Reset</a>
            @endif
        </form>
    </div>

    <!-- Outstanding Customers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Phone</th>
                        <th class="py-3.5 px-4">Total Invoices</th>
                        <th class="py-3.5 px-4">Total Billed</th>
                        <th class="py-3.5 px-4">Total Paid</th>
                        <th class="py-3.5 px-4 text-right">Outstanding Balance</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('store.customers.show', $c->id) }}" class="hover:text-[#4b55c8]">
                                {{ $c->name }}
                            </a>
                        </td>
                        <td class="py-3 px-4">{{ $c->phone ?: '—' }}</td>
                        <td class="py-3 px-4">{{ $c->invoices_count }}</td>
                        <td class="py-3 px-4">₹{{ number_format($c->total_sales, 2) }}</td>
                        <td class="py-3 px-4 text-emerald-600 font-semibold">₹{{ number_format($c->total_paid, 2) }}</td>
                        <td class="py-3 px-4 text-right font-black text-rose-600">
                            ₹{{ number_format($c->outstanding, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('store.sales.index', ['customer_id' => $c->id, 'payment_status' => 'unpaid']) }}" class="px-2.5 py-1 text-xs font-semibold text-[#4b55c8] hover:bg-[#eef2fd] rounded-lg transition">
                                View Invoices
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">No outstanding customer receivables found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
