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
                    <a href="{{ route('store.dashboard') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('store.dashboard') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.dashboard') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>Dashboard</span>
                        </div>
                    </a>

                    <!-- 2. Medicines (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            <span>Medicines</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Phase 14</span>
                    </div>

                    <!-- 3. Inventory (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Inventory</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Phase 14</span>
                    </div>

                    <!-- 4. Purchases (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Purchases</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 5. Sales / Billing (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span>Sales & Billing</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 6. Customers (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Customers</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 7. Suppliers (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Suppliers</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 8. Returns (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Returns</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 9. Expenses (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Expenses</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 10. Reports (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Reports</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Soon</span>
                    </div>

                    <!-- 11. Staff (Placeholder) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-medium text-slate-400 hover:bg-slate-50/60 transition cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Staff Management</span>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">Optional</span>
                    </div>

                    <!-- 12. Store Settings (Phase 14) -->
                    <a href="{{ route('store.settings.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium transition {{ request()->routeIs('store.settings.*') ? 'bg-[#4b55c8] text-white shadow-md shadow-indigo-200' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('store.settings.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Store Settings</span>
                        </div>
                    </a>

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

                    <!-- Top Profile Chip -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white font-bold text-xs shadow-sm">
                            {{ substr(auth()->user()->name ?? 'O', 0, 2) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <span class="text-xs font-bold text-[#1e2746] block leading-tight">{{ auth()->user()->name ?? 'Store Owner' }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">Store Owner</span>
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
    </script>
    @stack('scripts')
</body>
</html>
