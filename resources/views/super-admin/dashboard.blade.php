@extends('super-admin.layouts.app')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'System Dashboard')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Welcome / Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900/95 to-teal-950/40 border border-slate-800 p-5 sm:p-6 shadow-xl">
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-500/15 text-teal-300 border border-teal-500/30 uppercase tracking-widest mb-2">
                    Global System Governance
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Welcome back, {{ $admin->name }}</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Multi-store Medical Store SaaS platform system metrics and infrastructure overview.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('super-admin.stores.create') }}" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold text-xs shadow-lg shadow-teal-500/20 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Store</span>
                </a>
                <div class="px-3.5 py-2 rounded-xl bg-slate-950/70 border border-slate-800 text-xs text-slate-300 font-mono">
                    {{ now()->format('l, d M Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Stores -->
        <a href="{{ route('super-admin.stores.index') }}" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-teal-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-teal-300 transition">Total Stores</span>
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">{{ $stats['total_stores'] }}</span>
                <span class="text-xs text-slate-500 font-medium">registered</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>View Directory</span>
                <span class="font-mono text-teal-400 group-hover:translate-x-0.5 transition-transform">Browse &rarr;</span>
            </div>
        </a>

        <!-- Active Stores -->
        <a href="{{ route('super-admin.stores.index', ['status' => 'active']) }}" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-emerald-300 transition">Active Stores</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-400 tracking-tight">{{ $stats['active_stores'] }}</span>
                <span class="text-xs text-slate-500 font-medium">operational</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Status</span>
                <span class="text-emerald-400 font-medium">Filter Active &rarr;</span>
            </div>
        </a>

        <!-- Inactive Stores -->
        <a href="{{ route('super-admin.stores.index', ['status' => 'inactive']) }}" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Inactive Stores</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-200 tracking-tight">{{ $stats['inactive_stores'] }}</span>
                <span class="text-xs text-slate-500 font-medium">suspended/pending</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Suspended</span>
                <span class="text-amber-400 font-medium">{{ $stats['inactive_stores'] }} Stores</span>
            </div>
        </a>

        <!-- Total Store Owners -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Store Owners</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">{{ $stats['total_store_owners'] }}</span>
                <span class="text-xs text-slate-500 font-medium">owners</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Active / Inactive</span>
                <span class="font-mono text-indigo-300">{{ $stats['active_store_owners'] }} / {{ $stats['inactive_store_owners'] }}</span>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Subscriptions</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                @if ($stats['active_subscriptions'] !== null)
                    <span class="text-3xl font-extrabold text-white tracking-tight">{{ $stats['active_subscriptions'] }}</span>
                @else
                    <span class="text-lg font-bold text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded-lg">Coming Soon</span>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Module Status</span>
                <span class="text-purple-400 font-medium">Subscription Module</span>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expiring Soon</span>
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                @if ($stats['expiring_subscriptions'] !== null)
                    <span class="text-3xl font-extrabold text-rose-400 tracking-tight">{{ $stats['expiring_subscriptions'] }}</span>
                @else
                    <span class="text-lg font-bold text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded-lg">Coming Soon</span>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Renewal Alert</span>
                <span class="text-rose-400 font-medium">30 Days Window</span>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Revenue</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                @if ($stats['total_revenue'] !== null)
                    <span class="text-3xl font-extrabold text-emerald-400 tracking-tight">${{ number_format($stats['total_revenue'], 2) }}</span>
                @else
                    <span class="text-lg font-bold text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded-lg">Coming Soon</span>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Payments</span>
                <span class="text-emerald-400 font-medium">System Billing</span>
            </div>
        </div>

        <!-- System Administrators -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Super Admins</span>
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-teal-300 tracking-tight">{{ $stats['total_super_admins'] }}</span>
                <span class="text-xs text-slate-500 font-medium">accounts</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Access Level</span>
                <span class="text-teal-400 font-medium">Full Authority</span>
            </div>
        </div>

    </div>

    <!-- Quick Actions Section -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-white tracking-tight">Quick System Actions</h3>
                <p class="text-xs text-slate-400 mt-0.5">Shortcuts for store administration and SaaS operations.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 border border-slate-700/60">Module Actions</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Add Store (Active Link) -->
            <a href="{{ route('super-admin.stores.create') }}" class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-teal-500/50 hover:bg-slate-900 transition flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-teal-500/10 text-teal-400 border border-teal-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white group-hover:text-teal-300 transition">Add Store</p>
                        <p class="text-[10px] text-slate-500">Register new tenant</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-wider text-teal-400 bg-teal-500/10 px-1.5 py-0.5 rounded border border-teal-500/20">Active</span>
            </a>

            <!-- View Stores (Active Link) -->
            <a href="{{ route('super-admin.stores.index') }}" class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-purple-500/50 hover:bg-slate-900 transition flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white group-hover:text-purple-300 transition">View Stores</p>
                        <p class="text-[10px] text-slate-500">Manage tenant accounts</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-wider text-purple-400 bg-purple-500/10 px-1.5 py-0.5 rounded border border-purple-500/20">Active</span>
            </a>

            <!-- Add Store Owner (Coming Soon) -->
            <button type="button" disabled class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-left opacity-75 cursor-not-allowed flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Add Store Owner</p>
                        <p class="text-[10px] text-slate-500">Assign store admin</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded border border-indigo-500/20">Soon</span>
            </button>

            <!-- View Subscriptions (Coming Soon) -->
            <button type="button" disabled class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-left opacity-75 cursor-not-allowed flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Subscriptions</p>
                        <p class="text-[10px] text-slate-500">Manage SaaS plans</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">Soon</span>
            </button>

        </div>
    </div>

    <!-- Analytics & Store Growth Section -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h3 class="text-base font-bold text-white tracking-tight">Store Registration & SaaS Growth</h3>
                <p class="text-xs text-slate-400 mt-0.5">Timeline trends for newly registered medical stores.</p>
            </div>
            
            <!-- Time Filter Buttons -->
            <div class="inline-flex rounded-xl bg-slate-950/80 border border-slate-800 p-1 text-xs">
                <button type="button" class="px-2.5 py-1 rounded-lg bg-teal-500/20 text-teal-300 font-semibold border border-teal-500/30">7 Days</button>
                <button type="button" class="px-2.5 py-1 rounded-lg text-slate-400 hover:text-white transition">30 Days</button>
                <button type="button" class="px-2.5 py-1 rounded-lg text-slate-400 hover:text-white transition">6 Months</button>
                <button type="button" class="px-2.5 py-1 rounded-lg text-slate-400 hover:text-white transition">1 Year</button>
            </div>
        </div>

        <!-- Chart Container / Empty State -->
        @if ($stats['total_stores'] > 0)
            <div class="relative h-64 w-full">
                <canvas id="growthChart"></canvas>
            </div>
        @else
            <div class="h-60 rounded-xl bg-slate-950/40 border border-slate-800/60 flex flex-col items-center justify-center p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-white">No Growth Data Recorded</h4>
                <p class="text-xs text-slate-500 max-w-sm mt-1">Real-time registration growth graphs will render dynamically once medical stores are created in the Store Management phase.</p>
            </div>
        @endif
    </div>

    <!-- Data Tables & Timeline Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Recent Stores Table (2 cols on lg) -->
        <div class="lg:col-span-2 bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight">Recent Stores</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Recently onboarded medical stores.</p>
                    </div>
                    <a href="{{ route('super-admin.stores.index') }}" class="text-xs font-semibold text-teal-400 hover:text-teal-300">
                        View All ({{ $stats['total_stores'] }}) &rarr;
                    </a>
                </div>

                @if ($stats['recent_stores']->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950/60 uppercase text-[10px] font-bold text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Store Code</th>
                                    <th class="px-4 py-3">Store Name</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Created</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach ($stats['recent_stores'] as $store)
                                    <tr class="hover:bg-slate-800/30 transition">
                                        <td class="px-4 py-3 font-mono font-bold text-teal-400">{{ $store->code }}</td>
                                        <td class="px-4 py-3 font-semibold text-white">{{ $store->name }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $store->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($store->status === 'SUSPENDED' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20') }}">
                                                {{ $store->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 font-mono">{{ \Carbon\Carbon::parse($store->created_at)->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('super-admin.stores.show', $store->id) }}" class="text-teal-400 hover:text-teal-300 font-semibold">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Proper Empty State -->
                    <div class="py-12 rounded-xl bg-slate-950/40 border border-slate-800/50 flex flex-col items-center justify-center text-center p-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-white">No stores available yet</h4>
                        <p class="text-xs text-slate-500 max-w-sm mt-1 mb-4">Medical stores will be listed here once registered in the system.</p>
                        <a href="{{ route('super-admin.stores.create') }}" class="px-3 py-1.5 rounded-lg bg-teal-500/20 text-teal-300 font-semibold text-xs border border-teal-500/30 hover:bg-teal-500/30 transition">
                            + Add Store
                        </a>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-500">
                <span>Phase 3 Scope: Store Management Active</span>
                <a href="{{ route('super-admin.stores.index') }}" class="text-teal-400 hover:underline">Manage Stores &rarr;</a>
            </div>
        </div>

        <!-- Right: Recent System Activity Timeline (1 col on lg) -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight">System Activity</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Audit trail & security events.</p>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                </div>

                @if ($stats['recent_activities']->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($stats['recent_activities'] as $activity)
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full bg-teal-400 mt-1.5 flex-shrink-0"></div>
                                <div class="text-xs">
                                    <p class="font-semibold text-slate-200">{{ $activity->description ?? 'System Event' }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $activity->created_at }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Proper Empty State -->
                    <div class="py-12 rounded-xl bg-slate-950/40 border border-slate-800/50 flex flex-col items-center justify-center text-center p-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-white">No system activity logged yet</h4>
                        <p class="text-xs text-slate-500 max-w-xs mt-1">Audit and security events will automatically stream here when Audit Logging is configured.</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800/60 text-xs text-slate-500 text-center">
                <span>Security logs encrypted & retained for 90 days</span>
            </div>
        </div>

    </div>

</div>
@endsection
