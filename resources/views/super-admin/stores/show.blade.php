@extends('super-admin.layouts.app')

@section('title', $store->name . ' - Store Details')
@section('page-title', 'Store Overview')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super-admin.stores.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Stores Directory</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.stores.edit', $store) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Store</span>
            </a>
        </div>
    </div>

    <!-- Store Profile Hero Card -->
    <div class="bg-slate-900/90 border border-slate-800/80 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if ($store->logo)
                    <img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}" class="w-16 h-16 rounded-2xl object-cover bg-slate-800 border border-slate-700 shadow-md">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-teal-500/20 to-emerald-500/10 border border-teal-500/30 text-teal-300 font-extrabold flex items-center justify-center text-xl shadow-md">
                        {{ substr($store->name, 0, 2) }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">{{ $store->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $store->status->badgeClasses() }}">
                            {{ $store->status->label() }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-400 mt-1 font-mono">
                        <span class="text-teal-400 font-bold">{{ $store->code }}</span>
                        <span>•</span>
                        <span>{{ $store->store_type }}</span>
                        <span>•</span>
                        <span>{{ $store->city }}, {{ $store->state }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Transition Actions -->
            <div class="flex items-center gap-2 pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-800">
                @if ($store->status !== \App\Enums\StoreStatus::ACTIVE)
                    <form action="{{ route('super-admin.stores.update-status', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to ACTIVATE this store? It will be granted normal platform operation.');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="ACTIVE">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500 hover:text-slate-950 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Activate Store</span>
                        </button>
                    </form>
                @endif

                @if ($store->status !== \App\Enums\StoreStatus::INACTIVE)
                    <form action="{{ route('super-admin.stores.update-status', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to DEACTIVATE this store? Store users will not be able to operate.');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="INACTIVE">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Deactivate</span>
                        </button>
                    </form>
                @endif

                @if ($store->status !== \App\Enums\StoreStatus::SUSPENDED)
                    <form action="{{ route('super-admin.stores.update-status', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to SUSPEND this store? Store access will be completely blocked for compliance/payment.');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="SUSPENDED">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 border border-rose-500/30 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Suspend</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Information Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: Contact & Communication -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg space-y-4">
            <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Information
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Primary Mobile</span>
                    <span class="text-white font-mono font-semibold">{{ $store->mobile }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Alternate Contact</span>
                    <span class="text-slate-300 font-mono">{{ $store->alternate_mobile ?? '—' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Official Email</span>
                    <span class="text-slate-200">{{ $store->email ?? '—' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Store Code</span>
                    <span class="font-mono font-bold text-teal-400">{{ $store->code }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Physical Address -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg space-y-4">
            <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Location & Address
            </h3>

            <div class="space-y-3 text-xs">
                <div class="py-1 border-b border-slate-800/60">
                    <span class="text-slate-400 block mb-1">Street Address</span>
                    <span class="text-white font-medium block">{{ $store->address ?? 'No physical address provided' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">City / District</span>
                    <span class="text-white font-semibold">{{ $store->city }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">State</span>
                    <span class="text-white font-semibold">{{ $store->state }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Pincode</span>
                    <span class="text-teal-400 font-mono font-bold">{{ $store->pincode }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Business & Licensing -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg space-y-4">
            <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Business & Licensing
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">GSTIN</span>
                    <span class="text-white font-mono font-semibold">{{ $store->gstin ?? 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Drug License No.</span>
                    <span class="text-white font-mono font-semibold">{{ $store->drug_license_no ?? 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">License Expiry</span>
                    <span class="font-mono {{ $store->license_expiry_date && $store->license_expiry_date->isPast() ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                        {{ $store->license_expiry_date ? $store->license_expiry_date->format('d M Y') : 'Not specified' }}
                    </span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Classification</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-teal-300 font-semibold">{{ $store->store_type }}</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Governance & System Info -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-lg space-y-4">
            <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Ownership & System Record
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Store Owner</span>
                    <span class="text-slate-400 italic">
                        {{ $store->owners->isNotEmpty() ? $store->owners->first()->name : 'Not assigned' }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Registered On</span>
                    <span class="text-slate-300 font-mono">{{ $store->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Last Profile Update</span>
                    <span class="text-slate-300 font-mono">{{ $store->updated_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Record ID</span>
                    <span class="text-slate-500 font-mono">#{{ $store->id }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
