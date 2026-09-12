<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Store Dashboard') | {{ auth()->user()->store->name ?? config('app.name', 'MediStore') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2fd',
                            100: '#e0e8fc',
                            500: '#5c67e8',
                            600: '#4b55c8',
                            700: '#3f49b8',
                        },
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-screen w-screen bg-[#f8faff] text-slate-800 antialiased flex flex-col overflow-hidden selection:bg-[#4b55c8] selection:text-white">

    <!-- Full Screen Edge-to-Edge Dashboard Container -->
    <div class="w-full h-full flex overflow-hidden relative">

        <!-- Mobile Sidebar Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

        <!-- Left Sidebar Navigation -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto">
            
            <!-- Sidebar Header / Store Logo & Name -->
            <div>
                <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white shadow-md shadow-[#4b55c8]/25 flex-shrink-0">
                            <!-- Store SVG Icon -->
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="overflow-hidden">
                            <span class="font-extrabold text-[#1e2746] tracking-tight text-sm block leading-tight truncate">
                                {{ auth()->user()->store->name ?? 'Medical Store' }}
                            </span>
                            <span class="text-[10px] font-mono font-bold text-[#4b55c8] block mt-0.5">
                                {{ auth()->user()->store->code ?? 'STORE' }}
                            </span>
                        </div>
                    </div>

                    <!-- Mobile Close button -->
                    <button id="closeSidebarBtn" class="lg:hidden text-slate-400 hover:text-slate-700 p-1 focus:outline-none cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links List -->
                <nav class="px-4 py-4 space-y-1 overflow-y-auto max-h-[calc(100vh-210px)]">
                    
                    <!-- 1. Dashboard (Active) -->
                    <!-- 1. Dashboard (Active) -->
                    @if (auth()->user()->hasPermission('dashboard.view'))
                    <a href="{{ route('store.dashboard') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.dashboard') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.dashboard') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>Dashboard</span>
                        </div>
                    </a>
                    @endif

                    <!-- 2. Medicines (Phase 16 - Active Module) -->
                    @hasFeature('medicine_management')
                    @if (auth()->user()->hasPermission('medicines.view'))
                    <a href="{{ route('store.medicines.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.medicines.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.medicines.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            <span>Medicines</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- 3. Inventory (Phase 17) -->
                    @hasFeature('inventory_management')
                    @if (auth()->user()->hasPermission('inventory.view'))
                    <a href="{{ route('store.inventory.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.inventory.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.inventory.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Inventory & Batches</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- 4. Purchases (Phase 18) -->
                    @hasFeature('purchase_management')
                    @if (auth()->user()->hasPermission('purchases.view'))
                    <a href="{{ route('store.purchases.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.purchases.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.purchases.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Purchases</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- 5. Suppliers (Phase 18) -->
                    @hasFeature('supplier_management')
                    @if (auth()->user()->hasPermission('suppliers.view'))
                    <a href="{{ route('store.suppliers.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.suppliers.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.suppliers.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Suppliers</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- POS Terminal (Phase 19) -->
                    @hasFeature('pos')
                    @if (auth()->user()->hasPermission('sales.create'))
                    <a href="{{ route('store.pos.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition {{ request()->routeIs('store.pos.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25' : 'text-emerald-700 bg-emerald-50/70 hover:bg-emerald-100/70' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.pos.*') ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>POS Billing</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('store.pos.*') ? 'bg-white/25 text-white' : 'bg-emerald-200/70 text-emerald-800' }}">Quick</span>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Sales / Invoices (Phase 19) -->
                    @hasFeature('sales_management')
                    @if (auth()->user()->hasPermission('sales.view'))
                    <a href="{{ route('store.sales.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.sales.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.sales.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span>Sales Invoices</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Customers (Phase 19) -->
                    @hasFeature('customer_management')
                    @if (auth()->user()->hasPermission('customers.view'))
                    <a href="{{ route('store.customers.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.customers.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.customers.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Customers</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Sales Returns (Phase 20) -->
                    @hasFeature('sales_return')
                    @if (auth()->user()->hasPermission('sales.return'))
                    <a href="{{ route('store.sales-returns.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.sales-returns.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.sales-returns.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <span>Sales Returns</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Purchase Returns (Phase 20) -->
                    @hasFeature('purchase_return')
                    @if (auth()->user()->hasPermission('purchases.return'))
                    <a href="{{ route('store.purchase-returns.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.purchase-returns.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.purchase-returns.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/>
                            </svg>
                            <span>Purchase Returns</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Expenses (Phase 21) -->
                    @hasFeature('expense_management')
                    @if (auth()->user()->hasPermission('expenses.view'))
                    <a href="{{ route('store.expenses.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.expenses.*') || request()->routeIs('store.expense-categories.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.expenses.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Expenses</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Payments & Settlements (Phase 21) -->
                    @if (auth()->user()->hasPermission('customer_payments.view'))
                    <a href="{{ route('store.payments.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.payments.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.payments.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Payments & Ledger</span>
                        </div>
                    </a>
                    @endif

                    <!-- Outstanding Receivables / Payables (Phase 21) -->
                    @if (auth()->user()->hasPermission('reports.view'))
                    <a href="{{ route('store.outstanding.customers') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.outstanding.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.outstanding.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                            </svg>
                            <span>Outstanding Dues</span>
                        </div>
                    </a>
                    @endif

                    <!-- Reports (Phase 22) -->
                    @hasFeature('reports')
                    @if (auth()->user()->hasPermission('reports.view'))
                    <a href="{{ route('store.reports.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.reports.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.reports.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Business Reports</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- Notifications (Phase 23) -->
                    @if (auth()->user()->hasPermission('notifications.view'))
                    <a href="{{ route('store.notifications.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.notifications.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.notifications.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span>Notifications</span>
                        </div>
                        <span id="sidebarNotificationBadge" class="hidden text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-500 text-white"></span>
                    </a>
                    @endif

                    <!-- 11. Staff Management (Phase 26) -->
                    @hasFeature('staff_management')
                    @if (auth()->user()->hasPermission('staff.view'))
                    <a href="{{ route('store.staff.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.staff.*') || request()->routeIs('store.roles.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.staff.*') || request()->routeIs('store.roles.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Staff & Roles</span>
                        </div>
                    </a>
                    @endif
                    @endhasFeature

                    <!-- 12. Store Settings (Phase 14) -->
                    @if (auth()->user()->hasPermission('settings.view'))
                    <a href="{{ route('store.settings.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium transition {{ request()->routeIs('store.settings.*') ? 'bg-[#4b55c8] text-white shadow-md shadow-indigo-200' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.settings.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Store Settings</span>
                        </div>
                    </a>
                    @endif

                    <!-- 13. Logout -->
                    <form action="{{ route('store.logout') }}" method="POST" class="pt-3 border-t border-slate-100">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-[#64748b] hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Sidebar Bottom Profile Section -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#eef2fd] border border-[#d8e0ec] flex items-center justify-center text-[#4b55c8] font-bold text-xs flex-shrink-0">
                            {{ substr(auth()->user()->name ?? 'Owner', 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-[#1e2746] truncate">{{ auth()->user()->name ?? 'Store Owner' }}</p>
                            <p class="text-[11px] text-[#64748b] truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Right Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f8faff]">
            
            <!-- Topbar Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 flex items-center justify-between px-6 sm:px-8 z-30">
                
                <!-- Left Title & Store Context -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Drawer Toggle -->
                    <button id="openSidebarBtn" class="lg:hidden text-slate-500 hover:text-slate-800 p-2 rounded-xl bg-slate-100 focus:outline-none cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight leading-tight">@yield('page-title', 'Store Dashboard')</h1>
                        <div class="flex items-center gap-2 text-xs text-[#64748b] mt-0.5">
                            <span class="font-semibold text-[#1e2746]">{{ auth()->user()->store->name ?? 'Pharmacy Store' }}</span>
                            <span>•</span>
                            <span class="text-[#4b55c8] font-mono font-bold">{{ auth()->user()->store->code ?? '' }}</span>
                            <span>•</span>
                            <span class="text-slate-500">{{ auth()->user()->store->city ?? '' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Header Profile / Store Badge / Notifications -->
                <div class="flex items-center gap-3 sm:gap-4">
                    
                    <!-- Store Status Badge -->
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hidden sm:inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Active Pharmacy</span>
                    </span>

                    <!-- Notification Bell Dropdown (Phase 23) -->
                    <div class="relative" id="notificationDropdownContainer">
                        <button id="notificationBellBtn" type="button" class="relative p-2.5 rounded-2xl bg-slate-100/80 hover:bg-slate-200/70 text-slate-600 hover:text-slate-900 transition focus:outline-none cursor-pointer" aria-label="Notifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span id="navbarNotificationBadge" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">0</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="notificationDropdownMenu" class="hidden absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/80 z-50 overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
                            <!-- Dropdown Header -->
                            <div class="px-4 py-3 bg-gradient-to-r from-slate-50 to-indigo-50/30 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 text-sm">Notifications</span>
                                    <span id="dropdownUnreadCountBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">0 new</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="markAllReadBtn" type="button" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">Mark all read</button>
                                </div>
                            </div>

                            <!-- Dropdown List Container -->
                            <div id="dropdownNotificationsList" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                <div class="p-6 text-center text-xs text-slate-400">Loading notifications...</div>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                                <a href="{{ route('store.notifications.index') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                    <span>View all notifications</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="{{ route('store.notifications.preferences') }}" class="text-slate-500 hover:text-slate-800" title="Notification Preferences">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Top Profile Chip -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white font-bold text-xs shadow-sm">
                            {{ substr(auth()->user()->name ?? 'O', 0, 2) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <span class="text-xs font-bold text-[#1e2746] block leading-tight">{{ auth()->user()->name ?? 'User' }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">
                                {{ auth()->user()->isStoreOwner() ? 'Store Owner' : (auth()->user()->staffRole?->name ?? 'Staff') }}
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Scrollable Viewport -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7">
                
                <!-- Status Flash Alert -->
                @if (session('status'))
                    <div class="max-w-7xl mx-auto mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                <!-- Error Flash Alert -->
                @if (session('error'))
                    <div class="max-w-7xl mx-auto mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Drawer Script -->
    <script>
        const openBtn = document.getElementById('openSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar(open) {
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        openBtn?.addEventListener('click', () => toggleSidebar(true));
        closeBtn?.addEventListener('click', () => toggleSidebar(false));
        backdrop?.addEventListener('click', () => toggleSidebar(false));

        // In-App Notification Center & Dropdown Handler (Phase 23)
        (function() {
            const bellBtn = document.getElementById('notificationBellBtn');
            const dropdown = document.getElementById('notificationDropdownMenu');
            const container = document.getElementById('notificationDropdownContainer');
            const navbarBadge = document.getElementById('navbarNotificationBadge');
            const sidebarBadge = document.getElementById('sidebarNotificationBadge');
            const dropdownBadge = document.getElementById('dropdownUnreadCountBadge');
            const listContainer = document.getElementById('dropdownNotificationsList');
            const markAllBtn = document.getElementById('markAllReadBtn');

            let isOpen = false;

            function updateBadges(count) {
                if (count > 0) {
                    if (navbarBadge) {
                        navbarBadge.textContent = count > 99 ? '99+' : count;
                        navbarBadge.classList.remove('hidden');
                    }
                    if (sidebarBadge) {
                        sidebarBadge.textContent = count > 99 ? '99+' : count;
                        sidebarBadge.classList.remove('hidden');
                    }
                    if (dropdownBadge) {
                        dropdownBadge.textContent = `${count} new`;
                    }
                } else {
                    if (navbarBadge) navbarBadge.classList.add('hidden');
                    if (sidebarBadge) sidebarBadge.classList.add('hidden');
                    if (dropdownBadge) dropdownBadge.textContent = '0 new';
                }
            }

            function renderNotifications(items) {
                if (!items || items.length === 0) {
                    listContainer.innerHTML = `
                        <div class="p-8 text-center">
                            <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">All caught up!</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">No new notifications.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                items.forEach(item => {
                    const unreadDot = item.is_unread ? `<span class="w-2 h-2 rounded-full bg-indigo-600 flex-shrink-0"></span>` : '';
                    const bgClass = item.is_unread ? 'bg-indigo-50/20' : '';
                    html += `
                        <a href="${item.action_url}" class="block p-3.5 hover:bg-slate-50/80 transition ${bgClass} group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 ${item.badge_classes}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <p class="text-xs font-bold text-slate-900 truncate">${item.title}</p>
                                        ${unreadDot}
                                    </div>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">${item.message}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">${item.time_ago}</span>
                                </div>
                            </div>
                        </a>
                    `;
                });
                listContainer.innerHTML = html;
            }

            async function fetchDropdownData() {
                try {
                    const res = await fetch('{{ route("store.notifications.dropdown") }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        updateBadges(data.unread_count);
                        renderNotifications(data.notifications);
                    }
                } catch (e) {
                    console.error('Failed to load notifications dropdown', e);
                }
            }

            async function fetchUnreadCountOnly() {
                try {
                    const res = await fetch('{{ route("store.notifications.unread-count") }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        updateBadges(data.unread_count);
                    }
                } catch (e) {
                    // silently fail on background polling
                }
            }

            function toggleDropdown(show) {
                isOpen = (typeof show === 'boolean') ? show : !isOpen;
                if (isOpen) {
                    dropdown.classList.remove('hidden');
                    setTimeout(() => {
                        dropdown.classList.remove('opacity-0', 'scale-95');
                        dropdown.classList.add('opacity-100', 'scale-100');
                    }, 10);
                    fetchDropdownData();
                } else {
                    dropdown.classList.remove('opacity-100', 'scale-100');
                    dropdown.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => {
                        dropdown.classList.add('hidden');
                    }, 150);
                }
            }

            bellBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleDropdown();
            });

            document.addEventListener('click', (e) => {
                if (isOpen && container && !container.contains(e.target)) {
                    toggleDropdown(false);
                }
            });

            markAllBtn?.addEventListener('click', async (e) => {
                e.stopPropagation();
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const res = await fetch('{{ route("store.notifications.mark-all-read") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json'
                        }
                    });
                    if (res.ok) {
                        updateBadges(0);
                        fetchDropdownData();
                    }
                } catch (e) {
                    console.error('Failed to mark all as read', e);
                }
            });

            // Initial count fetch on page load
            fetchUnreadCountOnly();

            // Background polling every 60s
            setInterval(fetchUnreadCountOnly, 60000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
