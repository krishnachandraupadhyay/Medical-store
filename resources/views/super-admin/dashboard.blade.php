<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Super Admin Dashboard (Placeholder) | {{ config('app.name', 'Medical Store SaaS') }}</title>

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
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-[#080d1a] text-slate-100 flex flex-col antialiased">

    <!-- Navigation Bar -->
    <header class="border-b border-slate-800 bg-slate-900/70 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-400 flex items-center justify-center text-white shadow-md shadow-teal-500/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-white leading-none">MediStore</h1>
                    <span class="text-[11px] font-semibold text-teal-400 tracking-wider uppercase">Super Admin Portal</span>
                </div>
            </div>

            <!-- Right Profile & Logout -->
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-sm font-semibold text-white leading-tight">{{ $admin->name }}</span>
                    <span class="text-xs text-slate-400 font-mono">{{ $admin->email }}</span>
                </div>

                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-500/10 text-teal-400 border border-teal-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 mr-1.5 animate-pulse"></span>
                    {{ $admin->role->value }}
                </span>

                <!-- Logout Form -->
                <form action="{{ route('super-admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-rose-500/10 text-rose-300 hover:bg-rose-500 hover:text-white border border-rose-500/20 transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col justify-center items-center">
        <div class="w-full max-w-3xl bg-slate-900/60 border border-slate-800 rounded-3xl p-8 sm:p-12 text-center relative overflow-hidden backdrop-blur-sm shadow-2xl">
            <!-- Top Gradient Accent -->
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-teal-500 via-emerald-400 to-teal-600"></div>

            <div class="inline-flex p-4 rounded-2xl bg-teal-500/10 text-teal-400 border border-teal-500/20 mb-6">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
            </div>

            <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Super Admin Authentication Active</h2>
            <p class="text-slate-400 text-sm sm:text-base mt-2 max-w-xl mx-auto">
                Phase 1 is complete. System-level authentication, session protection, role enforcement, and store isolation foundations are configured and operating securely.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 text-left">
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="text-xs font-semibold text-teal-400 uppercase tracking-wider block">Role Level</span>
                    <span class="text-sm font-bold text-white mt-1 block">Global Super Admin</span>
                    <span class="text-xs text-slate-500 mt-1 block">Independent of store IDs</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="text-xs font-semibold text-teal-400 uppercase tracking-wider block">Guard / Middleware</span>
                    <span class="text-sm font-bold text-white mt-1 block">Strict RBAC</span>
                    <span class="text-xs text-slate-500 mt-1 block">Server-side verified</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="text-xs font-semibold text-teal-400 uppercase tracking-wider block">Session State</span>
                    <span class="text-sm font-bold text-white mt-1 block">Active & Encrypted</span>
                    <span class="text-xs text-slate-500 mt-1 block">Auto regenerated on auth</span>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800/80 text-xs text-slate-500">
                <span>Phase 2+ modules (Store Management, Subscriptions, Inventory, etc.) will be implemented in subsequent phases.</span>
            </div>
        </div>
    </main>

</body>
</html>
