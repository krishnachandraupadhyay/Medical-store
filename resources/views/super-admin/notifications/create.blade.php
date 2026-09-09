@extends('super-admin.layouts.app')

@section('title', 'Create Notification')
@section('page-title', 'Create Notification')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.notifications.index') }}" class="hover:text-[#4b55c8] transition">Notifications</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Create</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Create Platform Notification</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Compose broadcast messages, maintenance warnings, or targeted alerts for stores.</p>
        </div>

        <a href="{{ route('super-admin.notifications.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
            &larr; Back to List
        </a>
    </div>

    <!-- Error Validation Banner -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1 shadow-sm">
            <div class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Please fix the following validation errors:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('super-admin.notifications.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Core Notification Content -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-[#1e2746] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    Message Content & Type
                </h3>

                <!-- Title -->
                <div class="space-y-1.5">
                    <label for="title" class="block text-xs font-bold text-slate-700">
                        Notification Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           placeholder="e.g. Scheduled System Maintenance on Sunday Midnight"
                           required
                           class="w-full px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#1e2746] rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('title') border-rose-400 bg-rose-50/30 @enderror" />
                    @error('title')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Type -->
                    <div class="space-y-1.5">
                        <label for="type" class="block text-xs font-bold text-slate-700">
                            Notification Type <span class="text-rose-500">*</span>
                        </label>
                        <select id="type" name="type" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('type') border-rose-400 @enderror">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}" {{ old('type', 'general') === $t->value ? 'selected' : '' }}>
                                    {{ $t->icon() }} {{ $t->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="space-y-1.5">
                        <label for="priority" class="block text-xs font-bold text-slate-700">
                            Priority Level <span class="text-rose-500">*</span>
                        </label>
                        <select id="priority" name="priority" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('priority') border-rose-400 @enderror">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}" {{ old('priority', 'normal') === $p->value ? 'selected' : '' }}>
                                    {{ $p->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Message Body -->
                <div class="space-y-1.5">
                    <label for="message" class="block text-xs font-bold text-slate-700">
                        Message Body <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="message"
                              name="message"
                              rows="5"
                              required
                              placeholder="Type the full notification announcement or instructions for store owners here..."
                              class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('message') border-rose-400 bg-rose-50/30 @enderror">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 2: Target Audience -->
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-bold text-[#1e2746] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    Audience Targeting
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Target Type -->
                    <div class="space-y-1.5">
                        <label for="target_type" class="block text-xs font-bold text-slate-700">
                            Target Audience <span class="text-rose-500">*</span>
                        </label>
                        <select id="target_type" name="target_type" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                            <option value="all_stores" {{ old('target_type', 'all_stores') === 'all_stores' ? 'selected' : '' }}>
                                All Stores (Platform-wide broadcast)
                            </option>
                            <option value="specific_store" {{ old('target_type', $selectedStoreId ? 'specific_store' : '') === 'specific_store' ? 'selected' : '' }}>
                                Specific Store
                            </option>
                        </select>
                    </div>

                    <!-- Specific Store Selector (Conditional) -->
                    <div id="storeSelectorContainer" class="space-y-1.5 {{ old('target_type', $selectedStoreId ? 'specific_store' : '') === 'specific_store' ? '' : 'hidden' }}">
                        <label for="store_id" class="block text-xs font-bold text-slate-700">
                            Select Medical Store <span class="text-rose-500">*</span>
                        </label>
                        <select id="store_id" name="store_id" class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('store_id') border-rose-400 @enderror">
                            <option value="">-- Choose Target Store --</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string)old('store_id', $selectedStoreId) === (string)$store->id ? 'selected' : '' }}>
                                    {{ $store->name }} ({{ $store->code }}) — {{ $store->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Delivery Options -->
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-bold text-[#1e2746] uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2 h-2 rounded-full bg-[#4b55c8]"></span>
                    Delivery & Scheduling
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Delivery Mode -->
                    <div class="space-y-1.5">
                        <label for="delivery_mode" class="block text-xs font-bold text-slate-700">
                            Delivery Action <span class="text-rose-500">*</span>
                        </label>
                        <select id="delivery_mode" name="delivery_mode" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">
                            <option value="now" {{ old('delivery_mode', 'now') === 'now' ? 'selected' : '' }}>
                                🚀 Send Immediately (Publish Now)
                            </option>
                            <option value="schedule" {{ old('delivery_mode') === 'schedule' ? 'selected' : '' }}>
                                ⏰ Schedule for Later
                            </option>
                            <option value="draft" {{ old('delivery_mode') === 'draft' ? 'selected' : '' }}>
                                📝 Save as Draft
                            </option>
                        </select>
                    </div>

                    <!-- Scheduled DateTime (Conditional) -->
                    <div id="scheduledTimeContainer" class="space-y-1.5 {{ old('delivery_mode') === 'schedule' ? '' : 'hidden' }}">
                        <label for="scheduled_at" class="block text-xs font-bold text-slate-700">
                            Scheduled Date & Time <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local"
                               id="scheduled_at"
                               name="scheduled_at"
                               value="{{ old('scheduled_at', now()->addHours(1)->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('scheduled_at') border-rose-400 @enderror" />
                        @error('scheduled_at')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('super-admin.notifications.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition active:scale-[0.99] cursor-pointer">
                    Submit Notification
                </button>
            </div>

        </form>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const targetTypeSelect = document.getElementById('target_type');
        const storeSelectorContainer = document.getElementById('storeSelectorContainer');
        const deliveryModeSelect = document.getElementById('delivery_mode');
        const scheduledTimeContainer = document.getElementById('scheduledTimeContainer');

        targetTypeSelect?.addEventListener('change', function () {
            if (this.value === 'specific_store') {
                storeSelectorContainer.classList.remove('hidden');
            } else {
                storeSelectorContainer.classList.add('hidden');
            }
        });

        deliveryModeSelect?.addEventListener('change', function () {
            if (this.value === 'schedule') {
                scheduledTimeContainer.classList.remove('hidden');
            } else {
                scheduledTimeContainer.classList.add('hidden');
            }
        });
    });
</script>
@endpush
@endsection
