@extends('store.layouts.app')

@section('title', 'Business Reports Hub')

@section('content')
<div class="space-y-6 max-w-full mx-auto">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Business Reports & Analytics</h1>
        <p class="text-sm text-slate-500 mt-1">Audit-ready, store-scoped reporting foundation for operational clarity.</p>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- 1. Sales Report -->
        <a href="{{ route('store.reports.sales') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-[#4b55c8]/40 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    📊
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-[#4b55c8] transition">Sales & Revenue Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Filter by date range, customer, payment status, and medicine items. Includes subtotal, taxes, discounts, and CSV export.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-[#4b55c8]">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 2. Purchase Report -->
        <a href="{{ route('store.reports.purchases') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-purple-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    📦
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-purple-600 transition">Purchase & Procurement Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Vendor orders, invoiced totals, payment tracking, and supplier outstanding balances with CSV export.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-purple-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 3. Sales Return Report -->
        <a href="{{ route('store.reports.sales-returns') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-amber-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    ↺
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-amber-600 transition">Sales Return Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Customer medicine returns, batch re-stocking history, return reasons, and credit notes issued.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 4. Purchase Return Report -->
        <a href="{{ route('store.reports.purchase-returns') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-rose-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    ↩
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-rose-600 transition">Purchase Return Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Damaged and expired medicine returns to suppliers with debit notes and batch deductions.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 5. Expense Report -->
        <a href="{{ route('store.reports.expenses') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-rose-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    ⚡
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-rose-600 transition">Expense Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Operational disbursements categorized by category, payment method, and transaction date.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 6. Payments & Settlement Report -->
        <a href="{{ route('store.reports.payments') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-emerald-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    💳
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition">Payments & Ledger Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Combined ledger of customer sales receipts, supplier payment disbursements, and expense payouts.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 7. Inventory & Valuation Report -->
        <a href="{{ route('store.reports.inventory') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-blue-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    📋
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-[#4b55c8] transition">Inventory & Valuation Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Active batches, shelf quantities, purchase vs MRP valuation, and near-expiry status tracking.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-[#4b55c8]">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 8. Stock Movement Audit Report -->
        <a href="{{ route('store.reports.stock-movements') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-slate-400 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    🔍
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-slate-900 transition">Stock Movement History</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Immutable ledger audit of all inventory movements: purchases IN, sales OUT, returns, and adjustments.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-700">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 9. Medicine Sales & Returns Report -->
        <a href="{{ route('store.reports.medicine-sales') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-emerald-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    💊
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition">Medicine Sales Analysis</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Net units sold per medicine (Gross Sold minus Returned), sales turnover value, and volume ranking.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 10. Customer Receivables Report -->
        <a href="{{ route('store.outstanding.customers') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-amber-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    👥
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-amber-600 transition">Customer Receivables Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Customer outstanding balances from unpaid completed sales invoices with contact and billing summary.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>

        <!-- 11. Supplier Payables Report -->
        <a href="{{ route('store.outstanding.suppliers') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:border-rose-300 hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition">
                    🏢
                </div>
                <h2 class="text-base font-bold text-slate-900 group-hover:text-rose-600 transition">Supplier Payables Report</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Vendor bill balances due, purchase history, and supplier outstanding payment ledger.
                </p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                <span>Generate Report</span>
                <span>→</span>
            </div>
        </a>
    </div>
</div>
@endsection
