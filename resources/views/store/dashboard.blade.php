@extends('store.layouts.app')

@section('title', 'Store Dashboard')
@section('page-title', 'Store Overview')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- 1. Welcome Greeting Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#eef3fe] via-[#eaf0fc] to-[#e4ecfb] border border-[#dce5f8] p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
        <div class="z-10 max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-[#4b55c8] text-xs font-bold mb-3">
                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                <span>Active Pharmacy Store</span>
            </div>
            <h2 class="text-xl sm:text-3xl font-black text-[#1e2746] tracking-tight">
                Welcome to your Store Dashboard, {{ $user->name }} 👋
            </h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-2 font-medium leading-relaxed">
                You are currently managing <strong>{{ $store->name }}</strong> (Code: <span class="font-mono text-[#4b55c8] font-bold">{{ $store->code }}</span>).
            </p>
        </div>

        <!-- Store Badge Graphic -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-white/90 border border-white shadow-lg flex items-center justify-center text-[#4b55c8] flex-shrink-0">
            <svg class="w-10 h-10 sm:w-12 sm:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
    </div>

    <!-- 2. Store Profile & Context Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Store Details Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold">
                        🏪
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[#1e2746]">Store Profile</h3>
                        <p class="text-xs text-slate-400">Registered Pharmacy</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Store Name:</span>
                        <span class="font-bold text-[#1e2746]">{{ $store->name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Store Code:</span>
                        <span class="font-mono font-bold text-[#4b55c8]">{{ $store->code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Location:</span>
                        <span class="font-semibold text-slate-700">{{ $store->city }}, {{ $store->state }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Drug License:</span>
                        <span class="font-mono text-slate-700">{{ $store->drug_license_no ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">GSTIN:</span>
                        <span class="font-mono text-slate-700">{{ $store->gstin ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Details Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#4b55c8] flex items-center justify-center font-bold">
                        👤
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[#1e2746]">Owner Profile</h3>
                        <p class="text-xs text-slate-400">Authenticated Administrator</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Owner Name:</span>
                        <span class="font-bold text-[#1e2746]">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Email:</span>
                        <span class="font-semibold text-slate-700">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Mobile:</span>
                        <span class="font-semibold text-slate-700">{{ $user->mobile ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Account Status:</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Role:</span>
                        <span class="font-mono font-bold text-[#4b55c8]">STORE_OWNER</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Phases Info Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        🚀
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[#1e2746]">Store Modules</h3>
                        <p class="text-xs text-slate-400">Upcoming Features</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    Your store owner portal is active and authenticated. Store operational modules (Medicine Inventory, Batch/Expiry Management, POS Billing, and Supplier Purchases) will be unlocked in upcoming phases.
                </p>

                <form action="{{ route('store.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs rounded-xl transition cursor-pointer">
                        Sign Out from Store
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
