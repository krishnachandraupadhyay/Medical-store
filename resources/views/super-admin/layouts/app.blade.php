<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Super Admin Dashboard') | {{ config('app.name', 'Medical Store SaaS') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        },
                        navy: {
                            800: '#0f172a',
                            900: '#0b1120',
                            950: '#060b13',
                        }
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
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0b1120;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="h-full bg-[#060b13] text-slate-200 antialiased flex flex-col selection:bg-teal-500 selection:text-white">

    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900/95 border-r border-slate-800/80 flex flex-col justify-between transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto">
            
            <!-- Sidebar Header / Branding -->
            <div>
                <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-400 flex items-center justify-center text-white shadow-md shadow-teal-500/20">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-white tracking-tight text-base block leading-none">MediStore</span>
                            <span class="text-[10px] font-bold text-teal-400 uppercase tracking-wider block mt-1">Super Admin SaaS</span>
                        </div>
                    </div>

                    <!-- Close button on mobile -->
                    <button id="closeSidebarBtn" class="lg:hidden text-slate-400 hover:text-white p-1 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="px-3 py-4 space-y-1 overflow-y-auto max-h-[calc(100vh-140px)]">
                    <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Core Management</div>

                    <!-- Dashboard -->
                    <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.dashboard') ? 'bg-gradient-to-r from-teal-500/15 to-emerald-500/10 text-teal-300 border border-teal-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.dashboard') ? 'text-teal-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Stores (Active Phase 3 Module) -->
                    <a href="{{ route('super-admin.stores.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.stores.*') ? 'bg-gradient-to-r from-teal-500/15 to-emerald-500/10 text-teal-300 border border-teal-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('super-admin.stores.*') ? 'text-teal-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Stores</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ request()->routeIs('super-admin.stores.*') ? 'bg-teal-500/20 text-teal-300' : 'bg-slate-800 text-slate-400' }}">Active</span>
                    </a>

                    <!-- Store Owners (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Store Owners</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>

                    <!-- Subscriptions (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            <span>Subscriptions</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>

                    <!-- Payments (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Payments</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>

                    <div class="pt-3 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">System & Governance</div>

                    <!-- Reports (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Reports</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>

                    <!-- Notifications (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span>Notifications</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>

                    <!-- Settings (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Settings</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>

                    <!-- Audit Logs (Placeholder) -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800/40 cursor-not-allowed group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Audit Logs</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700/50">Soon</span>
                    </div>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-teal-300 font-bold text-xs">
                        SA
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                        <p class="text-[11px] text-teal-400 font-mono">SUPER_ADMIN</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Topbar Header -->
            <header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30">
                
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Button -->
                    <button id="openSidebarBtn" class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg bg-slate-800/60 border border-slate-700/60 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-white tracking-tight leading-none">@yield('page-title', 'Dashboard Overview')</h1>
                        <span class="text-[11px] text-slate-400 hidden sm:inline">System-Level Overview & Metrics</span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- System Status Pill -->
                    <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>SaaS Engine Online</span>
                    </div>

                    <!-- Notification Icon Placeholder -->
                    <button class="relative p-2 rounded-xl text-slate-400 hover:text-slate-200 bg-slate-800/40 hover:bg-slate-800 border border-slate-700/60 transition cursor-pointer" title="Notifications (Coming Soon)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-teal-400 rounded-full"></span>
                    </button>

                    <!-- Super Admin Profile Dropdown & Logout -->
                    <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-3 border-l border-slate-800">
                        <div class="hidden md:flex flex-col text-right">
                            <span class="text-xs font-bold text-white">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ auth()->user()->email }}</span>
                        </div>

                        <!-- Sign Out Button -->
                        <form action="{{ route('super-admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-500/10 text-rose-300 hover:bg-rose-500 hover:text-white border border-rose-500/20 transition-all cursor-pointer flex items-center gap-1.5"
                                title="Sign Out"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="hidden sm:inline">Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Page Scrollable Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[#060b13]">
                <!-- Status Alerts -->
                @if (session('status'))
                    <div class="max-w-7xl mx-auto mb-6 p-4 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-300 text-sm flex items-center justify-between shadow-lg">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-teal-400 hover:text-teal-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="max-w-7xl mx-auto mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between shadow-lg">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">
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
