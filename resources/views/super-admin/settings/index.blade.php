@extends('super-admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs font-semibold text-[#4b55c8] uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                System Administration
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1e2746] tracking-tight">System Settings</h1>
            <p class="text-xs sm:text-sm text-[#627094]">Configure global platform parameters, branding, security policies, and subscription rules.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Status: Active
            </span>
        </div>
    </div>

    <!-- Flash Messages & Validation Alert -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Settings Saved</h4>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold">Please correct the following errors:</h4>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 space-y-1 ml-11">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Settings Container (Grid with Left Tabs & Right Form) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Navigation Tabs -->
        <div class="lg:col-span-3 space-y-2">
            <div class="bg-white rounded-3xl p-3 border border-slate-200/80 shadow-sm space-y-1">
                @php
                    $tabs = [
                        'general' => [
                            'name' => 'General',
                            'desc' => 'Timezone, currency & name',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
                        ],
                        'branding' => [
                            'name' => 'Branding',
                            'desc' => 'Logo, favicon & visual assets',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>'
                        ],
                        'contact' => [
                            'name' => 'Contact Info',
                            'desc' => 'Corporate & support details',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'
                        ],
                        'system' => [
                            'name' => 'System & Ops',
                            'desc' => 'Maintenance mode & registrations',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>'
                        ],
                        'security' => [
                            'name' => 'Security',
                            'desc' => 'Authentication & audit logging',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'
                        ],
                        'notification' => [
                            'name' => 'Notifications',
                            'desc' => 'Channels & delivery readiness',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
                        ],
                        'subscription' => [
                            'name' => 'Subscriptions',
                            'desc' => 'Trials & expiration policies',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'
                        ],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabInfo)
                    <a href="{{ route('super-admin.settings.index', ['tab' => $tabKey]) }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-xs font-semibold transition-all {{ $activeTab === $tabKey ? 'bg-[#4b55c8] text-white shadow-md shadow-[#4b55c8]/20' : 'text-[#627094] hover:text-[#1e2746] hover:bg-slate-50' }}">
                        <svg class="w-5 h-5 shrink-0 {{ $activeTab === $tabKey ? 'text-white' : 'text-[#627094]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $tabInfo['icon'] !!}
                        </svg>
                        <div>
                            <span class="block font-bold text-sm leading-tight">{{ $tabInfo['name'] }}</span>
                            <span class="block text-[11px] opacity-80 font-normal mt-0.5">{{ $tabInfo['desc'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Quick Info Card -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-3xl p-5 text-xs text-slate-600 space-y-2">
                <div class="flex items-center gap-2 font-bold text-[#4b55c8]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Instant Cache Sync</span>
                </div>
                <p class="text-[11px] leading-relaxed">Changes to system settings immediately clear the global settings cache and apply to all platform modules without server restart.</p>
            </div>
        </div>

        <!-- Right Content Section -->
        <div class="lg:col-span-9">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">

                {{-- ================= TAB 1: GENERAL SETTINGS ================= --}}
                @if($activeTab === 'general')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="general">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">General Settings</h2>
                            <p class="text-xs text-[#627094]">Platform identification, timezone, and global formatting standards.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Application Name -->
                                <div class="space-y-2 sm:col-span-2">
                                    <label for="app_name" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Application Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $values['app_name'] ?? 'MEDISTORE') }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                    <p class="text-[11px] text-slate-400">Displayed on titles, header bars, and transactional emails.</p>
                                </div>

                                <!-- Application Tagline -->
                                <div class="space-y-2 sm:col-span-2">
                                    <label for="app_tagline" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Platform Tagline</label>
                                    <input type="text" name="app_tagline" id="app_tagline" value="{{ old('app_tagline', $values['app_tagline'] ?? '') }}"
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                </div>

                                <!-- Default Timezone -->
                                <div class="space-y-2">
                                    <label for="default_timezone" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Default Timezone <span class="text-rose-500">*</span></label>
                                    <select name="default_timezone" id="default_timezone" required
                                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white">
                                        @php
                                            $timezones = [
                                                'Asia/Kolkata' => '(GMT+05:30) Asia/Kolkata (IST)',
                                                'UTC' => '(GMT+00:00) UTC',
                                                'America/New_York' => '(GMT-05:00) Eastern Time (US & Canada)',
                                                'America/Chicago' => '(GMT-06:00) Central Time (US & Canada)',
                                                'America/Los_Angeles' => '(GMT-08:00) Pacific Time (US & Canada)',
                                                'Europe/London' => '(GMT+00:00) London, Edinburgh',
                                                'Europe/Paris' => '(GMT+01:00) Paris, Berlin, Rome',
                                                'Asia/Dubai' => '(GMT+04:00) Dubai, Abu Dhabi',
                                                'Asia/Singapore' => '(GMT+08:00) Singapore, Kuala Lumpur',
                                                'Asia/Tokyo' => '(GMT+09:00) Tokyo, Osaka',
                                                'Australia/Sydney' => '(GMT+10:00) Sydney, Melbourne',
                                            ];
                                            $selectedTz = old('default_timezone', $values['default_timezone'] ?? 'Asia/Kolkata');
                                        @endphp
                                        @foreach($timezones as $tzCode => $tzLabel)
                                            <option value="{{ $tzCode }}" {{ $selectedTz === $tzCode ? 'selected' : '' }}>{{ $tzLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Default Currency -->
                                <div class="space-y-2">
                                    <label for="default_currency" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Default Currency <span class="text-rose-500">*</span></label>
                                    <select name="default_currency" id="default_currency" required
                                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white">
                                        @php
                                            $currencies = [
                                                'INR' => 'INR - Indian Rupee (₹)',
                                                'USD' => 'USD - US Dollar ($)',
                                                'EUR' => 'EUR - Euro (€)',
                                                'GBP' => 'GBP - British Pound (£)',
                                                'AED' => 'AED - UAE Dirham (د.إ)',
                                                'CAD' => 'CAD - Canadian Dollar (C$)',
                                                'AUD' => 'AUD - Australian Dollar (A$)',
                                                'SGD' => 'SGD - Singapore Dollar (S$)',
                                            ];
                                            $selectedCurr = old('default_currency', $values['default_currency'] ?? 'INR');
                                        @endphp
                                        @foreach($currencies as $currCode => $currLabel)
                                            <option value="{{ $currCode }}" {{ $selectedCurr === $currCode ? 'selected' : '' }}>{{ $currLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Currency Symbol -->
                                <div class="space-y-2">
                                    <label for="currency_symbol" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Currency Symbol <span class="text-rose-500">*</span></label>
                                    <input type="text" name="currency_symbol" id="currency_symbol" value="{{ old('currency_symbol', $values['currency_symbol'] ?? '₹') }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                </div>

                                <!-- Date Format -->
                                <div class="space-y-2">
                                    <label for="date_format" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Date Format <span class="text-rose-500">*</span></label>
                                    <select name="date_format" id="date_format" required
                                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white">
                                        @php
                                            $dateFormats = [
                                                'Y-m-d' => 'YYYY-MM-DD (e.g. '.now()->format('Y-m-d').')',
                                                'd-m-Y' => 'DD-MM-YYYY (e.g. '.now()->format('d-m-Y').')',
                                                'd/m/Y' => 'DD/MM/YYYY (e.g. '.now()->format('d/m/Y').')',
                                                'm/d/Y' => 'MM/DD/YYYY (e.g. '.now()->format('m/d/Y').')',
                                                'd M Y' => 'DD Mon YYYY (e.g. '.now()->format('d M Y').')',
                                                'M d, Y' => 'Mon DD, YYYY (e.g. '.now()->format('M d, Y').')',
                                            ];
                                            $selectedDateFormat = old('date_format', $values['date_format'] ?? 'Y-m-d');
                                        @endphp
                                        @foreach($dateFormats as $dfCode => $dfLabel)
                                            <option value="{{ $dfCode }}" {{ $selectedDateFormat === $dfCode ? 'selected' : '' }}>{{ $dfLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Time Format -->
                                <div class="space-y-2">
                                    <label for="time_format" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Time Format <span class="text-rose-500">*</span></label>
                                    <select name="time_format" id="time_format" required
                                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition bg-white">
                                        @php
                                            $timeFormats = [
                                                'h:i A' => '12-Hour AM/PM (e.g. '.now()->format('h:i A').')',
                                                'H:i' => '24-Hour (e.g. '.now()->format('H:i').')',
                                                'h:i:s A' => '12-Hour with Seconds (e.g. '.now()->format('h:i:s A').')',
                                                'H:i:s' => '24-Hour with Seconds (e.g. '.now()->format('H:i:s').')',
                                            ];
                                            $selectedTimeFormat = old('time_format', $values['time_format'] ?? 'h:i A');
                                        @endphp
                                        @foreach($timeFormats as $tfCode => $tfLabel)
                                            <option value="{{ $tfCode }}" {{ $selectedTimeFormat === $tfCode ? 'selected' : '' }}>{{ $tfLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Last updated: {{ optional($allSettings['app_name'] ?? null)->updated_at?->diffForHumans() ?? 'Default' }}</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                @endif


                {{-- ================= TAB 2: BRANDING SETTINGS ================= --}}
                @if($activeTab === 'branding')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="branding">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">Branding & Visual Assets</h2>
                            <p class="text-xs text-[#627094]">Upload brand logos, favicon, and login screen imagery.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-8">
                            <!-- Application Logo -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-sm">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Application Header Logo</h4>
                                    <p class="text-xs text-[#627094]">Primary logo rendered in the navigation sidebar and top header bar. Recommended dimensions: 240x60px (PNG, SVG, WEBP).</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    @if(!empty($values['app_logo']))
                                        <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 flex items-center justify-center p-2 shadow-sm shrink-0">
                                            <img src="{{ asset('storage/' . $values['app_logo']) }}" alt="App Logo" class="max-h-full max-w-full object-contain">
                                        </div>
                                    @else
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#4b55c8] to-[#7482f0] flex items-center justify-center text-white shadow-md shadow-[#4b55c8]/25 shrink-0">
                                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                    @endif
                                    <input type="file" name="app_logo" id="app_logo" accept="image/*"
                                           class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#4b55c8] file:text-white hover:file:bg-[#3d46a8] cursor-pointer">
                                </div>
                            </div>

                            <!-- Favicon -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-sm">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Browser Favicon</h4>
                                    <p class="text-xs text-[#627094]">Icon displayed on browser tabs. Recommended dimensions: 32x32px or 64x64px (ICO, PNG, SVG).</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    @if(!empty($values['app_favicon']))
                                        <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center p-1 shadow-sm shrink-0">
                                            <img src="{{ asset('storage/' . $values['app_favicon']) }}" alt="Favicon" class="max-h-full max-w-full object-contain">
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 shrink-0 font-bold text-xs">
                                            ICO
                                        </div>
                                    @endif
                                    <input type="file" name="app_favicon" id="app_favicon" accept=".ico,.png,.svg"
                                           class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#4b55c8] file:text-white hover:file:bg-[#3d46a8] cursor-pointer">
                                </div>
                            </div>

                            <!-- Login Page Logo -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-sm">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Login Portal Logo</h4>
                                    <p class="text-xs text-[#627094]">Hero branding emblem shown on the primary authentication login screen. (PNG, JPG, SVG).</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    @if(!empty($values['login_logo']))
                                        <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 flex items-center justify-center p-2 shadow-sm shrink-0">
                                            <img src="{{ asset('storage/' . $values['login_logo']) }}" alt="Login Logo" class="max-h-full max-w-full object-contain">
                                        </div>
                                    @else
                                        <div class="w-16 h-16 rounded-2xl bg-[#eef2fd] border border-[#d5ddfd] text-[#4b55c8] flex items-center justify-center shrink-0">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <input type="file" name="login_logo" id="login_logo" accept="image/*"
                                           class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#4b55c8] file:text-white hover:file:bg-[#3d46a8] cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Supported types: PNG, JPG, WEBP, SVG, ICO (Max 2MB)</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Upload & Save Branding
                            </button>
                        </div>
                    </form>
                @endif


                {{-- ================= TAB 3: CONTACT INFORMATION ================= --}}
                @if($activeTab === 'contact')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="contact">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">SaaS Platform Contact Information</h2>
                            <p class="text-xs text-[#627094]">Corporate vendor entity and public customer support desk contact details.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Company Name -->
                                <div class="space-y-2">
                                    <label for="company_name" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Company Legal Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $values['company_name'] ?? '') }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                </div>

                                <!-- Website -->
                                <div class="space-y-2">
                                    <label for="company_website" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Website URL</label>
                                    <input type="url" name="company_website" id="company_website" value="{{ old('company_website', $values['company_website'] ?? '') }}" placeholder="https://..."
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                </div>

                                <!-- Support Email -->
                                <div class="space-y-2">
                                    <label for="support_email" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Support Email <span class="text-rose-500">*</span></label>
                                    <input type="email" name="support_email" id="support_email" value="{{ old('support_email', $values['support_email'] ?? '') }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                </div>

                                <!-- Support Phone -->
                                <div class="space-y-2">
                                    <label for="support_phone" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Support Phone</label>
                                    <input type="text" name="support_phone" id="support_phone" value="{{ old('support_phone', $values['support_phone'] ?? '') }}"
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                </div>

                                <!-- Corporate Address -->
                                <div class="space-y-2 sm:col-span-2">
                                    <label for="company_address" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Corporate Physical Address</label>
                                    <textarea name="company_address" id="company_address" rows="3"
                                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">{{ old('company_address', $values['company_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Platform corporate footprint</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Contact Info
                            </button>
                        </div>
                    </form>
                @endif


                {{-- ================= TAB 4: SYSTEM & OPS ================= --}}
                @if($activeTab === 'system')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="system">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">System Operations & Provisioning</h2>
                            <p class="text-xs text-[#627094]">Control maintenance status, customer signups, and store creation.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <!-- Maintenance Mode Toggle -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-amber-50/50 border border-amber-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-[#1e2746]">Maintenance Mode</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ !empty($values['maintenance_mode']) ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ !empty($values['maintenance_mode']) ? 'ENABLED' : 'OFF' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600">When enabled, tenant store users and guests will receive the maintenance screen. Super Admins retain full administrative bypass access.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" {{ !empty($values['maintenance_mode']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                                </label>
                            </div>

                            <!-- Maintenance Message -->
                            <div class="space-y-2">
                                <label for="maintenance_message" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Maintenance Notice Message</label>
                                <textarea name="maintenance_message" id="maintenance_message" rows="3"
                                          class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">{{ old('maintenance_message', $values['maintenance_message'] ?? '') }}</textarea>
                                <p class="text-[11px] text-slate-400">Custom message displayed to tenants during scheduled downtime.</p>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Enable New Store Registration -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Enable New Store Registration</h4>
                                    <p class="text-xs text-[#627094]">Allow public self-service medical store onboarding and provisioning.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_store_registration" value="1" class="sr-only peer" {{ !empty($values['enable_store_registration']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- Enable Store Owner Creation -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Enable Store Owner Creation</h4>
                                    <p class="text-xs text-[#627094]">Allow adding new store owner manager accounts in the administrative console.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_store_owner_creation" value="1" class="sr-only peer" {{ !empty($values['enable_store_owner_creation']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Super Admin retains full system control at all times.</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save System Settings
                            </button>
                        </div>
                    </form>
                @endif


                {{-- ================= TAB 5: SECURITY SETTINGS ================= --}}
                @if($activeTab === 'security')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="security">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">Platform Security & Access Policies</h2>
                            <p class="text-xs text-[#627094]">Configure session longevity, password strength policies, and threat protection.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Session Timeout -->
                                <div class="space-y-2">
                                    <label for="session_timeout" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Session Timeout (Minutes) <span class="text-rose-500">*</span></label>
                                    <input type="number" name="session_timeout" id="session_timeout" min="5" max="1440" value="{{ old('session_timeout', $values['session_timeout'] ?? 120) }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                    <p class="text-[11px] text-slate-400">Duration of user inactivity before auto-logout (5 to 1440 mins).</p>
                                </div>

                                <!-- Password Min Length -->
                                <div class="space-y-2">
                                    <label for="password_min_length" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Password Minimum Length <span class="text-rose-500">*</span></label>
                                    <input type="number" name="password_min_length" id="password_min_length" min="6" max="64" value="{{ old('password_min_length', $values['password_min_length'] ?? 8) }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                    <p class="text-[11px] text-slate-400">Enforced character threshold across user registrations.</p>
                                </div>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Require Strong Password -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Require Complex Password</h4>
                                    <p class="text-xs text-[#627094]">Enforce combination of uppercase letters, numbers, and special symbols.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="require_strong_password" value="1" class="sr-only peer" {{ !empty($values['require_strong_password']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- Login Rate Limiting -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Login Attempt Protection (Rate Limiting)</h4>
                                    <p class="text-xs text-[#627094]">Throttle repeated failed login attempts to prevent brute-force attacks.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="login_attempt_protection" value="1" class="sr-only peer" {{ !empty($values['login_attempt_protection']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- Enable Audit Logging -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Enable Platform Audit Logging</h4>
                                    <p class="text-xs text-[#627094]">Record administrative activities, configuration changes, and sensitive events.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_audit_logging" value="1" class="sr-only peer" {{ !empty($values['enable_audit_logging']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Security safeguards are strictly enforced server-side.</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Security Settings
                            </button>
                        </div>
                    </form>
                @endif


                {{-- ================= TAB 6: NOTIFICATION CHANNELS ================= --}}
                @if($activeTab === 'notification')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="notification">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">Notification Channels & Delivery Readiness</h2>
                            <p class="text-xs text-[#627094]">Enable global notification channels across the platform.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <!-- In-App Notifications -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-[#1e2746]">In-App Notification Center</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Operational</span>
                                    </div>
                                    <p class="text-xs text-[#627094]">Delivers alerts, announcements, and system notices inside tenant dashboards.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_in_app_notifications" value="1" class="sr-only peer" {{ !empty($values['enable_in_app_notifications']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- Email Notifications -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-[#1e2746]">Email Notification Channel</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Configurable</span>
                                    </div>
                                    <p class="text-xs text-[#627094]">Enable availability of transactional and broadcast email delivery to users.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_email_notifications" value="1" class="sr-only peer" {{ !empty($values['enable_email_notifications']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- SMS Notifications -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-[#1e2746]">SMS Notification Channel</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">Planned</span>
                                    </div>
                                    <p class="text-xs text-[#627094]">Enable availability of SMS dispatch for critical account alerts.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_sms_notifications" value="1" class="sr-only peer" {{ !empty($values['enable_sms_notifications']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- WhatsApp Notifications -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-[#1e2746]">WhatsApp Notification Channel</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">Planned</span>
                                    </div>
                                    <p class="text-xs text-[#627094]">Enable availability of WhatsApp messaging for store owners.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="enable_whatsapp_notifications" value="1" class="sr-only peer" {{ !empty($values['enable_whatsapp_notifications']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Third-party provider API keys will be configured in integration phase.</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Notification Settings
                            </button>
                        </div>
                    </form>
                @endif


                {{-- ================= TAB 7: SUBSCRIPTION SETTINGS ================= --}}
                @if($activeTab === 'subscription')
                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="subscription">

                        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-1">
                            <h2 class="text-xl font-extrabold text-[#1e2746]">SaaS Subscription & Trial Rules</h2>
                            <p class="text-xs text-[#627094]">Global trial limits, new assignment policies, and expiry warning intervals.</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Default Trial Days -->
                                <div class="space-y-2">
                                    <label for="default_trial_days" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Default Trial Duration (Days) <span class="text-rose-500">*</span></label>
                                    <input type="number" name="default_trial_days" id="default_trial_days" min="0" max="365" value="{{ old('default_trial_days', $values['default_trial_days'] ?? 14) }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                    <p class="text-[11px] text-slate-400">Standard trial duration applied to newly created stores.</p>
                                </div>

                                <!-- Expiry Warning Days -->
                                <div class="space-y-2">
                                    <label for="subscription_expiry_warning_days" class="block text-xs font-bold text-[#1e2746] uppercase tracking-wider">Expiry Warning Threshold (Days) <span class="text-rose-500">*</span></label>
                                    <input type="number" name="subscription_expiry_warning_days" id="subscription_expiry_warning_days" min="1" max="60" value="{{ old('subscription_expiry_warning_days', $values['subscription_expiry_warning_days'] ?? 7) }}" required
                                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#4b55c8] focus:border-transparent outline-none transition">
                                    <p class="text-[11px] text-slate-400">Days prior to expiration when alerts are triggered on reports & dashboards.</p>
                                </div>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Allow Free Trial -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Allow Free Trial Signups</h4>
                                    <p class="text-xs text-[#627094]">Enable granting trial subscriptions to new stores upon registration.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="allow_trial" value="1" class="sr-only peer" {{ !empty($values['allow_trial']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>

                            <!-- Allow New Subscriptions -->
                            <div class="flex items-start justify-between p-5 rounded-2xl bg-[#f8faff] border border-slate-200/80">
                                <div class="space-y-1 max-w-lg">
                                    <h4 class="text-sm font-bold text-[#1e2746]">Allow New Subscriptions</h4>
                                    <p class="text-xs text-[#627094]">Permit assigning new paid plan subscriptions to medical stores.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" name="allow_new_subscriptions" value="1" class="sr-only peer" {{ !empty($values['allow_new_subscriptions']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#4b55c8]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                            <span class="text-xs text-[#627094]">Seamlessly integrates with Subscription Plan and Store Assignment modules.</span>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#4b55c8] hover:bg-[#3d46a8] shadow-md shadow-[#4b55c8]/25 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Subscription Settings
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
