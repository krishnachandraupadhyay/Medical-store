@extends('store.layouts.app')

@section('title', 'Supplier Ledger: ' . $supplier->name)

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.suppliers.show', $supplier) }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to {{ $supplier->name }}
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-mono font-semibold text-slate-400">Ledger Statement</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1 flex items-center gap-3">
                <span>{{ $supplier->name }}</span>
                @if($supplier->supplier_code)
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $supplier->supplier_code }}
                    </span>
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Vendor Statement of Accounts · GSTIN: {{ $supplier->gst_number ?: 'N/A' }} · Phone: {{ $supplier->phone }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('store.suppliers.ledger.print', array_merge(['supplier' => $supplier], request()->query())) }}"
               target="_blank"
               class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Statement</span>
            </a>

            <a href="{{ route('store.suppliers.show', $supplier) }}" class="px-4 py-2.5 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                Supplier Profile
            </a>
        </div>
    </div>

    <!-- Date Filter Card -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <form method="GET" action="{{ route('store.suppliers.ledger', $supplier) }}" class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="w-full md:w-44">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs shadow-sm transition">
                    Apply Filter
                </button>
                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('store.suppliers.ledger', $supplier) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>

            <div class="md:ml-auto text-xs text-slate-400 self-center">
                Statement Period: <span class="font-bold text-slate-700">{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Inception' }}</span> to <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Opening Balance</span>
            <div class="text-2xl font-black {{ $periodOpeningBalance > 0 ? 'text-rose-600' : ($periodOpeningBalance < 0 ? 'text-emerald-600' : 'text-slate-800') }} mt-2">
                ₹{{ number_format(abs($periodOpeningBalance), 2) }}
            </div>
            <div class="text-xs font-semibold {{ $periodOpeningBalance > 0 ? 'text-rose-400' : ($periodOpeningBalance < 0 ? 'text-emerald-400' : 'text-slate-400') }} mt-0.5">
                {{ $periodOpeningBalance > 0 ? 'Payable (Brought Forward)' : ($periodOpeningBalance < 0 ? 'Advance (Prepaid)' : 'Nil Balance') }}
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Purchases (Debit)</span>
            <div class="text-2xl font-black text-slate-900 mt-2 font-mono">
                ₹{{ number_format($totalDebits, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-0.5">Invoices in period</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Payments (Credit)</span>
            <div class="text-2xl font-black text-emerald-600 mt-2 font-mono">
                ₹{{ number_format($totalCredits, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-0.5">Payments cleared in period</div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Closing Balance</span>
            <div class="text-2xl font-black {{ $closingBalance > 0 ? 'text-rose-600' : ($closingBalance < 0 ? 'text-emerald-600' : 'text-slate-800') }} mt-2 font-mono">
                ₹{{ number_format(abs($closingBalance), 2) }}
            </div>
            <div class="text-xs font-bold {{ $closingBalance > 0 ? 'text-rose-500' : ($closingBalance < 0 ? 'text-emerald-500' : 'text-slate-400') }} mt-0.5">
                {{ $closingBalance > 0 ? 'Net Payable (Outstanding)' : ($closingBalance < 0 ? 'Net Advance (Store Credit)' : 'Settled') }}
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-[#1e2746]">Chronological Ledger Transactions</h3>
            <span class="text-xs text-slate-400">{{ count($ledgerEntries) }} entries</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider bg-slate-50/60">
                        <th class="py-3.5 px-5">Date</th>
                        <th class="py-3.5 px-5">Type</th>
                        <th class="py-3.5 px-5">Reference / Bill #</th>
                        <th class="py-3.5 px-5">Particulars / Description</th>
                        <th class="py-3.5 px-5 text-right">Debit (+)</th>
                        <th class="py-3.5 px-5 text-right">Credit (-)</th>
                        <th class="py-3.5 px-5 text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <!-- Opening Balance Row -->
                    <tr class="bg-slate-50/40 italic font-semibold">
                        <td class="py-3.5 px-5 text-slate-500">
                            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Initial' }}
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-700">OPENING</span>
                        </td>
                        <td class="py-3.5 px-5 text-slate-400">—</td>
                        <td class="py-3.5 px-5 text-slate-600">Balance Brought Forward</td>
                        <td class="py-3.5 px-5 text-right font-mono text-slate-400">—</td>
                        <td class="py-3.5 px-5 text-right font-mono text-slate-400">—</td>
                        <td class="py-3.5 px-5 text-right font-mono font-bold {{ $periodOpeningBalance > 0 ? 'text-rose-600' : ($periodOpeningBalance < 0 ? 'text-emerald-600' : 'text-slate-600') }}">
                            ₹{{ number_format(abs($periodOpeningBalance), 2) }}
                            <span class="text-[10px] font-normal font-sans">{{ $periodOpeningBalance > 0 ? 'Dr (Payable)' : ($periodOpeningBalance < 0 ? 'Cr (Adv)' : '') }}</span>
                        </td>
                    </tr>

                    @forelse($ledgerEntries as $entry)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 text-slate-800 font-medium">
                                {{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-5">
                                @if($entry['type'] === 'PURCHASE')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        PURCHASE
                                    </span>
                                @elseif($entry['type'] === 'PURCHASE_RETURN')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        PURCHASE RETURN
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        PAYMENT
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                @if($entry['type'] === 'PURCHASE')
                                    <a href="{{ route('store.purchases.show', $entry['model']) }}" class="text-[#4b55c8] hover:underline">
                                        {{ $entry['reference'] }}
                                    </a>
                                @elseif($entry['type'] === 'PURCHASE_RETURN')
                                    <a href="{{ route('store.purchase-returns.show', $entry['model']) }}" class="text-amber-700 hover:underline">
                                        {{ $entry['reference'] }}
                                    </a>
                                @else
                                    <span>{{ $entry['reference'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-slate-600">
                                {{ $entry['description'] }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-mono font-bold text-slate-900">
                                @if($entry['debit'] > 0)
                                    ₹{{ number_format($entry['debit'], 2) }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right font-mono font-bold text-emerald-600">
                                @if($entry['credit'] > 0)
                                    ₹{{ number_format($entry['credit'], 2) }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right font-mono font-black {{ $entry['balance'] > 0 ? 'text-rose-600' : ($entry['balance'] < 0 ? 'text-emerald-600' : 'text-slate-600') }}">
                                ₹{{ number_format(abs($entry['balance']), 2) }}
                                <span class="text-[10px] font-bold font-sans">{{ $entry['balance'] > 0 ? 'Dr' : ($entry['balance'] < 0 ? 'Cr' : '') }}</span>
                            </td>
                        </tr>
                    @empty
                        @if($periodOpeningBalance == 0)
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                    No ledger transactions found for this supplier within the selected period.
                                </td>
                            </tr>
                        @endif
                    @endforelse

                    <!-- Closing Balance Row -->
                    <tr class="bg-slate-100/70 font-extrabold text-xs border-t-2 border-slate-200">
                        <td colspan="4" class="py-4 px-5 text-[#1e2746] uppercase tracking-wider text-right">
                            Period Totals & Net Closing Balance:
                        </td>
                        <td class="py-4 px-5 text-right font-mono text-slate-900">
                            ₹{{ number_format($totalDebits, 2) }}
                        </td>
                        <td class="py-4 px-5 text-right font-mono text-emerald-700">
                            ₹{{ number_format($totalCredits, 2) }}
                        </td>
                        <td class="py-4 px-5 text-right font-mono text-sm {{ $closingBalance > 0 ? 'text-rose-600' : ($closingBalance < 0 ? 'text-emerald-600' : 'text-slate-700') }}">
                            ₹{{ number_format(abs($closingBalance), 2) }}
                            <span class="text-xs font-sans">{{ $closingBalance > 0 ? 'Dr (Payable)' : ($closingBalance < 0 ? 'Cr (Advance)' : 'Settled') }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
