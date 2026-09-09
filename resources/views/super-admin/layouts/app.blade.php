<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Super Admin Dashboard') | {{ config('app.name', 'MediStore') }}</title>

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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-app-shell {
            background: linear-gradient(135deg, #a4b5f9 0%, #8ca1f4 50%, #7d91ec 100%);
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
            
            <!-- Sidebar Header / Logo -->
            <div>
                <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white shadow-md shadow-[#4b55c8]/25">
                            <!-- Medical Cross SVG -->
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-extrabold text-[#1e2746] tracking-tight text-lg block leading-none">MEDISTORE</span>
                            <span class="text-[11px] font-semibold text-[#627094] block mt-1">Admin Platform</span>
                        </div>
                    </div>

                    <!-- Mobile Close button -->
                    <button id="closeSidebarBtn" class="lg:hidden text-slate-400 hover:text-slate-700 p-1 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links List -->
                <nav class="px-4 py-5 space-y-1.5 overflow-y-auto max-h-[calc(100vh-210px)]">
                    
                    <!-- Dashboard -->
                    <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('super-admin.dashboard') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.dashboard') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Stores -->
                    <a href="{{ route('super-admin.stores.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('super-admin.stores.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('super-admin.stores.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Stores</span>
                        </div>
                    </a>

                    <!-- Store Owners -->
                    <a href="{{ route('super-admin.store-owners.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('super-admin.store-owners.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('super-admin.store-owners.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Store Owners</span>
                        </div>
                    </a>

                    <!-- Subscriptions Section -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between px-3.5 py-1.5 rounded-2xl text-xs sm:text-sm font-semibold {{ request()->routeIs('super-admin.subscriptions.*') ? 'text-[#4b55c8]' : 'text-[#64748b]' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 {{ request()->routeIs('super-admin.subscriptions.*') ? 'text-[#4b55c8]' : 'text-[#94a3b8]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Subscriptions</span>
                            </div>
                        </div>
                        <div class="pl-8 pr-2 space-y-1">
                            <a href="{{ route('super-admin.subscriptions.plans.index') }}" class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('super-admin.subscriptions.plans.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                                <span>Plans</span>
                            </a>
                            <a href="{{ route('super-admin.subscriptions.stores.index') }}" class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('super-admin.subscriptions.stores.*') ? 'bg-[#eef2fd] text-[#4b55c8]' : 'text-[#64748b] hover:text-[#1e2746] hover:bg-slate-50' }}">
                                <span>Store Subscriptions</span>
                            </a>
                        </div>
                    </div>

                    <!-- Payments -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-[#94a3b8] hover:bg-slate-50 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Payments</span>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-[#94a3b8] hover:bg-slate-50 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Reports</span>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-[#94a3b8] hover:bg-slate-50 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span>Notifications</span>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-[#94a3b8] hover:bg-slate-50 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Settings</span>
                        </div>
                    </div>

                    <!-- Audit Logs -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-[#94a3b8] hover:bg-slate-50 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Audit Logs</span>
                        </div>
                    </div>

                    <!-- Logout -->
                    <form action="{{ route('super-admin.logout') }}" method="POST" class="pt-2">
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
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#eef2fd] border border-[#d8e0ec] flex items-center justify-center text-[#4b55c8] font-bold text-xs">
                            <svg class="w-5 h-5 text-[#4b55c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-[#1e2746] truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                            <p class="text-[11px] text-[#64748b]">System Administrator <span class="text-[10px] font-mono text-[#4b55c8] font-bold">SUPER_ADMIN</span></p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </aside>

        <!-- Right Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f8faff]">
            
            <!-- Topbar Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 flex items-center justify-between px-6 sm:px-8 z-30">
                
                <!-- Left Title & Breadcrumbs -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Drawer Toggle -->
                    <button id="openSidebarBtn" class="lg:hidden text-slate-500 hover:text-slate-800 p-2 rounded-xl bg-slate-100 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight leading-tight">@yield('page-title', 'Dashboard')</h1>
                        <div class="flex items-center gap-1.5 text-xs text-[#64748b] mt-0.5">
                            <span>Home</span>
                            <span>/</span>
                            <span class="text-[#4b55c8] font-medium">@yield('page-title', 'Dashboard')</span>
                        </div>
                    </div>
                </div>

                <!-- Right Header Actions (Search, Notification, Help, Profile) -->
                <div class="flex items-center gap-3 sm:gap-4">
                    
                    <!-- Global Search Bar -->
                    <div class="relative hidden sm:block w-56 md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            placeholder="Global Search"
                            class="w-full pl-9 pr-3.5 py-2 bg-slate-100/80 border border-slate-200/60 rounded-full text-xs text-[#1e2746] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#4b55c8]/20 focus:bg-white transition"
                        >
                    </div>

                    <!-- Notification Bell with Pill Badge -->
                    <button class="relative p-2.5 rounded-full text-slate-500 hover:text-[#4b55c8] bg-slate-100/70 hover:bg-slate-100 transition cursor-pointer" title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute -top-0.5 -right-0.5 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#4b55c8] text-white">5</span>
                    </button>

                    <!-- Help Icon -->
                    <button class="p-2.5 rounded-full text-slate-500 hover:text-[#4b55c8] bg-slate-100/70 hover:bg-slate-100 transition cursor-pointer" title="Help & Support">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>

                    <!-- Top Profile Chip -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white font-bold text-xs shadow-sm">
                            SA
                        </div>
                        <span class="text-xs font-bold text-[#1e2746] hidden md:inline">{{ auth()->user()->name ?? 'Super Admin' }}</span>
                        <svg class="w-4 h-4 text-slate-400 hidden md:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
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
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
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
                        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
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
