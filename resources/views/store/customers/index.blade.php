@extends('store.layouts.app')

@section('title', 'Customer Master Directory')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Master Directory</h1>
            <p class="text-sm text-slate-500 mt-1">Customer Directory — Manage pharmacy patient profiles, demographics, account balances, and purchase records.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('store.customers.export', request()->all()) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-sm hover:bg-slate-50 transition shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('store.customers.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-sm shadow-md shadow-[#4b55c8]/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Customer</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-[#4b55c8] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Patients</p>
                <p class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($metrics['total']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Active Customers</p>
                <p class="text-xl font-black text-emerald-600 mt-0.5">{{ number_format($metrics['active']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">With Dues / Balance</p>
                <p class="text-xl font-black text-amber-700 mt-0.5">{{ number_format($metrics['has_due_count']) }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Store Outstanding</p>
                <p class="text-xl font-black font-mono text-rose-600 mt-0.5">₹{{ number_format($metrics['total_outstanding'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form method="GET" action="{{ route('store.customers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-3">
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Search Customer</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, code, doctor, emergency..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:border-[#4b55c8] outline-none">
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Status</label>
                <select name="status" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:border-[#4b55c8] outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Gender</label>
                <select name="gender" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:border-[#4b55c8] outline-none">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ request('gender') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Blood Group</label>
                <select name="blood_group" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:border-[#4b55c8] outline-none">
                    <option value="">All Blood Groups</option>
                    @foreach($bloodGroups as $bg)
                        <option value="{{ $bg }}" {{ request('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">City</label>
                <select name="city" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:border-[#4b55c8] outline-none">
                    <option value="">All Cities</option>
                    @foreach($cities as $cityItem)
                        <option value="{{ $cityItem }}" {{ request('city') === $cityItem ? 'selected' : '' }}>{{ $cityItem }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Balance Due</label>
                <select name="due" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:border-[#4b55c8] outline-none">
                    <option value="">All Balances</option>
                    <option value="has_due" {{ request('due') === 'has_due' ? 'selected' : '' }}>Has Pending Due</option>
                    <option value="cleared" {{ request('due') === 'cleared' ? 'selected' : '' }}>Zero / Cleared</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Filter</button>
                <a href="{{ route('store.customers.index') }}" class="px-2.5 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition" title="Reset Filters">Reset</a>
            </div>
        </form>
    </div>

    <!-- Customer Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-extrabold text-[11px] uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Customer / Patient</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Blood & Gender</th>
                        <th class="py-3.5 px-4">Prescribing Doctor</th>
                        <th class="py-3.5 px-4">Tier</th>
                        <th class="py-3.5 px-4 text-right">Outstanding</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($customers as $customer)
                    @php
                        $outstanding = $customer->outstandingAmount();
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- Code -->
                        <td class="py-3 px-4 font-mono font-bold text-[#4b55c8]">
                            {{ $customer->customer_code }}
                        </td>

                        <!-- Name & City -->
                        <td class="py-3 px-4">
                            <a href="{{ route('store.customers.show', $customer->id) }}" class="font-bold text-slate-900 hover:text-[#4b55c8] block leading-tight">
                                {{ $customer->name }}
                            </a>
                            <span class="text-[11px] text-slate-400 block">
                                {{ $customer->city ? $customer->city.($customer->state ? ', '.$customer->state : '') : 'Location not specified' }}
                            </span>
                        </td>

                        <!-- Contact -->
                        <td class="py-3 px-4 font-mono text-xs">
                            <span class="text-slate-800 font-semibold block">{{ $customer->phone ?: '—' }}</span>
                            @if($customer->alternate_phone)
                                <span class="text-[10px] text-slate-400 block">Alt: {{ $customer->alternate_phone }}</span>
                            @elseif($customer->email)
                                <span class="text-[10px] text-slate-400 block truncate max-w-[140px]">{{ $customer->email }}</span>
                            @endif
                        </td>

                        <!-- Blood Group & Gender -->
                        <td class="py-3 px-4 text-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if($customer->blood_group)
                                    <span class="px-2 py-0.5 rounded-md font-mono font-extrabold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $customer->blood_group }}
                                    </span>
                                @endif
                                <span class="text-slate-500 capitalize text-xs">
                                    {{ $customer->gender ?: '—' }}
                                </span>
                            </div>
                            @if($customer->emergency_contact_phone)
                                <span class="text-[10px] text-slate-400 block mt-0.5">Emerg: {{ $customer->emergency_contact_phone }}</span>
                            @endif
                        </td>

                        <!-- Doctor -->
                        <td class="py-3 px-4 text-xs">
                            @if($customer->doctor_name)
                                <span class="font-bold text-slate-800 flex items-center gap-1">
                                    <span class="text-emerald-600 font-black text-[10px]">Dr.</span>
                                    <span>{{ $customer->doctor_name }}</span>
                                </span>
                            @else
                                <span class="text-slate-400 italic text-[11px]">Direct Walk-in</span>
                            @endif
                        </td>

                        <!-- Tier -->
                        <td class="py-3 px-4">
                            @php
                                $tierClasses = [
                                    'regular'  => 'bg-slate-100 text-slate-700',
                                    'silver'   => 'bg-slate-200 text-slate-800',
                                    'gold'     => 'bg-amber-100 text-amber-900 border border-amber-200',
                                    'platinum' => 'bg-purple-100 text-purple-900 border border-purple-200',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $tierClasses[$customer->loyalty_tier ?? 'regular'] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $customer->loyaltyTierLabel() }}
                            </span>
                        </td>

                        <!-- Outstanding Balance -->
                        <td class="py-3 px-4 text-right font-mono">
                            @if($outstanding > 0)
                                <span class="font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200 inline-block text-xs">
                                    ₹{{ number_format($outstanding, 2) }}
                                </span>
                            @else
                                <span class="text-emerald-700 font-semibold text-xs">₹0.00</span>
                            @endif
                        </td>

                        <!-- Status & Toggle -->
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('store.customers.toggle-status', $customer->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Click to {{ $customer->isActive() ? 'Deactivate' : 'Activate' }}" class="cursor-pointer transition">
                                    @if($customer->isActive())
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100">
                                            ● Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200">
                                            ○ Inactive
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('store.customers.show', $customer->id) }}" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition" title="View Customer Profile & History">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('store.customers.edit', $customer->id) }}" class="p-1.5 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition" title="Edit Customer Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button" onclick="confirmDeleteCustomer({{ $customer->id }}, '{{ addslashes($customer->name) }}')" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Delete Customer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if(request()->hasAny(['search', 'status', 'gender', 'blood_group', 'city', 'tier', 'due']))
                                <p class="font-bold text-sm text-slate-700">No customers match your search filters</p>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting the filter criteria or clear your current filters.</p>
                                <a href="{{ route('store.customers.index') }}" class="inline-block mt-3 px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    Clear Filters
                                </a>
                            @else
                                <p class="font-bold text-sm text-slate-700">No customers registered yet</p>
                                <p class="text-xs text-slate-400 mt-1">Start building your pharmacy patient directory by adding your first customer.</p>
                                <a href="{{ route('store.customers.create') }}" class="inline-block mt-3 px-4 py-1.5 rounded-xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white text-xs font-bold transition shadow-xs">
                                    + Add First Customer
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal Form -->
<form id="deleteCustomerForm" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDeleteCustomer(id, name) {
        if (confirm(`Are you sure you want to delete customer '${name}'?\n\nNote: If this customer has existing sales invoices, deletion will be blocked to preserve historical accounting.`)) {
            const form = document.getElementById('deleteCustomerForm');
            form.action = `{{ url('store/customers') }}/${id}`;
            form.submit();
        }
    }
</script>
@endsection
