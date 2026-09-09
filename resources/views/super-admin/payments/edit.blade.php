@extends('super-admin.layouts.app')

@section('title', 'Edit Payment - ' . $payment->transaction_id)
@section('page-title', 'Edit Payment')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('super-admin.payments.index') }}" class="hover:text-[#4b55c8] transition">Payments</a>
                <span>/</span>
                <a href="{{ route('super-admin.payments.show', $payment) }}" class="hover:text-[#4b55c8] transition font-mono">{{ $payment->transaction_id }}</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Edit</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#1e2746] tracking-tight">Edit Payment Record</h2>
            <p class="text-xs sm:text-sm text-[#64748b] mt-1">Modify details for transaction #{{ $payment->transaction_id }}.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.payments.show', $payment) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                View Receipt
            </a>
            <a href="{{ route('super-admin.payments.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Back to List
            </a>
        </div>
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

    <!-- Linked Store & Plan Summary Card -->
    <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-center justify-between text-xs">
        <div>
            <span class="text-slate-400 block text-[11px]">Associated Store</span>
            <span class="font-bold text-[#1e2746]">{{ $payment->store?->name ?? 'N/A' }} ({{ $payment->store?->code ?? 'N/A' }})</span>
        </div>
        <div class="text-right">
            <span class="text-slate-400 block text-[11px]">Associated Plan</span>
            <span class="font-bold text-[#4b55c8]">{{ $payment->subscriptionPlan?->name ?? $payment->subscription?->plan?->name ?? 'Standard' }}</span>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('super-admin.payments.update', $payment) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                
                <!-- Transaction ID -->
                <div class="space-y-1.5">
                    <label for="transaction_id" class="block text-xs font-bold text-slate-700">
                        Transaction ID <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="transaction_id"
                           name="transaction_id"
                           value="{{ old('transaction_id', $payment->transaction_id) }}"
                           required
                           class="w-full px-4 py-2.5 text-xs sm:text-sm font-mono font-bold rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('transaction_id') border-rose-400 @enderror" />
                    @error('transaction_id')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="space-y-1.5">
                    <label for="amount" class="block text-xs font-bold text-slate-700">
                        Amount (₹) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-xs sm:text-sm">
                            ₹
                        </span>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               id="amount"
                               name="amount"
                               value="{{ old('amount', $payment->amount) }}"
                               required
                               class="w-full pl-8 pr-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('amount') border-rose-400 @enderror" />
                    </div>
                    @error('amount')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div class="space-y-1.5">
                    <label for="payment_method" class="block text-xs font-bold text-slate-700">
                        Payment Method <span class="text-rose-500">*</span>
                    </label>
                    <select id="payment_method" name="payment_method" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('payment_method') border-rose-400 @enderror">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" {{ old('payment_method', $payment->payment_method->value) === $method->value ? 'selected' : '' }}>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <label for="status" class="block text-xs font-bold text-slate-700">
                        Payment Status <span class="text-rose-500">*</span>
                    </label>
                    <select id="status" name="status" required class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('status') border-rose-400 @enderror">
                        @foreach ($paymentStatuses as $statusCase)
                            <option value="{{ $statusCase->value }}" {{ old('status', $payment->status->value) === $statusCase->value ? 'selected' : '' }}>
                                {{ $statusCase->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Date -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="payment_date" class="block text-xs font-bold text-slate-700">
                        Payment Date & Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local"
                           id="payment_date"
                           name="payment_date"
                           value="{{ old('payment_date', $payment->payment_date->format('Y-m-d\TH:i')) }}"
                           required
                           class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition @error('payment_date') border-rose-400 @enderror" />
                    @error('payment_date')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="notes" class="block text-xs font-bold text-slate-700">
                        Payment Notes / Remarks
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="4"
                              class="w-full px-4 py-2.5 text-xs sm:text-sm rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#4b55c8] transition">{{ old('notes', $payment->notes) }}</textarea>
                    @error('notes')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Form Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('super-admin.payments.show', $payment) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#5c67e8] hover:from-[#3f49b8] hover:to-[#4b55c8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition active:scale-[0.99] cursor-pointer">
                    Update Payment Record
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
