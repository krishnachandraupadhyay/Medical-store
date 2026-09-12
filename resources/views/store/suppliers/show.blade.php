@extends('store.layouts.app')

@section('title', 'Supplier: ' . $supplier->name)

@section('content')
<div class="space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('store.suppliers.index') }}"
               class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                    @if($supplier->company_name)
                    <span class="text-sm text-gray-500 font-normal">· {{ $supplier->company_name }}</span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $supplier->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($supplier->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $supplier->phone }}
                    @if($supplier->contact_person) · Contact: {{ $supplier->contact_person }} @endif
                </p>
            </div>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('store.suppliers.edit', $supplier) }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="flex items-center gap-2 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 14l-4.121-4.121a1 1 0 011.414-1.414L8.414 11.172l6.879-6.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        {{ session('status') }}
    </div>
    @endif

    {{-- ── KPI Stats Row ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Total Purchased</p>
            <p class="stat-value text-indigo-600">₹{{ number_format($totalPurchased, 2) }}</p>
            <p class="stat-sub">{{ $purchasesCount }} completed orders</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Amount Paid</p>
            <p class="stat-value text-emerald-600">₹{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Outstanding</p>
            <p class="stat-value {{ $outstanding > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                ₹{{ number_format($outstanding, 2) }}
            </p>
            @if($supplier->credit_limit)
            <p class="stat-sub">Limit: ₹{{ number_format($supplier->credit_limit, 0) }}</p>
            @endif
        </div>
        <div class="stat-card">
            <p class="stat-label">Last Purchase</p>
            <p class="stat-value text-gray-700 text-lg">{{ $lastPurchase ?? '—' }}</p>
            @if($overdueFollowUps->count())
            <p class="stat-sub text-rose-600">{{ $overdueFollowUps->count() }} overdue follow-up(s)</p>
            @endif
        </div>
    </div>

    {{-- ── Tabs ────────────────────────────────────────────────────────────── --}}
    <div x-data="{ tab: '{{ request()->fragment ?? 'overview' }}' }">

        <div class="border-b border-gray-200 flex gap-0 overflow-x-auto">
            @foreach([['overview','Overview'], ['notes','Notes & Follow-ups'], ['purchases','Purchases']] as [$key,$label])
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'tab-active' : 'tab-inactive'"
                    class="tab-btn whitespace-nowrap">
                {{ $label }}
                @if($key === 'notes' && $pendingFollowUps->count())
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                    {{ $pendingFollowUps->count() }}
                </span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ── OVERVIEW TAB ──────────────────────────────────────────────── --}}
        <div x-show="tab === 'overview'" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Contact Info --}}
            <div class="card col-span-2">
                <h2 class="card-title mb-4">Contact & Business Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach([
                        ['Contact Person', $supplier->contact_person],
                        ['Phone', $supplier->phone],
                        ['Alt. Phone', $supplier->alternate_phone],
                        ['Email', $supplier->email],
                        ['GST Number', $supplier->gst_number],
                        ['Drug License No.', $supplier->drug_license_no],
                        ['City', $supplier->city],
                        ['State', $supplier->state],
                        ['Pincode', $supplier->pincode],
                        ['Payment Terms', $supplier->payment_terms],
                    ] as [$lbl,$val])
                    @if($val)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $lbl }}</dt>
                        <dd class="mt-0.5 text-sm text-gray-800 font-medium">{{ $val }}</dd>
                    </div>
                    @endif
                    @endforeach

                    @if($supplier->address)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Address</dt>
                        <dd class="mt-0.5 text-sm text-gray-800">{{ $supplier->address }}</dd>
                    </div>
                    @endif
                </dl>

                @if($supplier->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Internal Notes</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $supplier->notes }}</dd>
                </div>
                @endif
            </div>

            {{-- Relationship Summary --}}
            <div class="space-y-4">
                <div class="card">
                    <h2 class="card-title mb-3">Relationship</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Since</span>
                            <span class="text-sm font-medium text-gray-800">{{ $supplier->created_at->format('d M Y') }}</span>
                        </div>
                        @if($supplier->credit_limit)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Credit Limit</span>
                            <span class="text-sm font-semibold text-gray-800">₹{{ number_format($supplier->credit_limit, 0) }}</span>
                        </div>
                        @endif
                        @if($supplier->payment_terms)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Payment Terms</span>
                            <span class="text-sm font-medium text-gray-800">{{ $supplier->payment_terms }}</span>
                        </div>
                        @endif
                        @if($supplier->last_contacted_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Last Contacted</span>
                            <span class="text-sm font-medium text-gray-800">{{ $supplier->last_contacted_at->diffForHumans() }}</span>
                        </div>
                        @endif
                        @if($pendingFollowUps->count())
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Pending Follow-ups</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                {{ $pendingFollowUps->count() }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                @if($supplier->tags && count($supplier->tags))
                <div class="card">
                    <h2 class="card-title mb-3">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($supplier->tags as $tag)
                        <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-medium border border-amber-100">
                            {{ $tag }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($overdueFollowUps->count())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-sm font-semibold text-rose-800 mb-1">⚠️ Overdue Follow-ups</p>
                    <ul class="space-y-1">
                        @foreach($overdueFollowUps as $note)
                        <li class="text-xs text-rose-700">
                            {{ $note->follow_up_date->format('d M Y') }} — {{ Str::limit($note->body, 60) }}
                        </li>
                        @endforeach
                    </ul>
                    <button @click="tab = 'notes'" class="mt-2 text-xs font-semibold text-rose-700 underline">View notes →</button>
                </div>
                @endif
            </div>
        </div>

        {{-- ── NOTES TAB ────────────────────────────────────────────────── --}}
        <div x-show="tab === 'notes'" class="mt-6" id="notes">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Add Note Form --}}
                <div class="card">
                    <h2 class="card-title mb-4">Add Interaction</h2>
                    <form action="{{ route('store.suppliers.notes.store', $supplier) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-input" required>
                                <option value="note">📝 Note</option>
                                <option value="call">📞 Phone Call</option>
                                <option value="meeting">🤝 Meeting</option>
                                <option value="email">📧 Email</option>
                                <option value="followup">📅 Follow-up</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Subject <span class="text-gray-400">(optional)</span></label>
                            <input type="text" name="subject" class="form-input" placeholder="Brief subject…" maxlength="255">
                        </div>
                        <div>
                            <label class="form-label">Note <span class="text-rose-500">*</span></label>
                            <textarea name="body" class="form-input" rows="4" placeholder="Write your note…" required maxlength="2000"></textarea>
                        </div>
                        <div>
                            <label class="form-label">Follow-up Date <span class="text-gray-400">(optional)</span></label>
                            <input type="date" name="follow_up_date" class="form-input"
                                   min="{{ now()->toDateString() }}">
                        </div>
                        <button type="submit" class="btn-primary w-full">Save Note</button>
                    </form>
                </div>

                {{-- Notes Timeline --}}
                <div class="lg:col-span-2">
                    @if($notes->isEmpty())
                    <div class="card text-center py-12">
                        <div class="text-4xl mb-3">📝</div>
                        <p class="text-gray-500">No notes yet. Add your first interaction above.</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($notes as $note)
                        <div class="card group relative border-l-4
                            {{ $note->isFollowUpOverdue() ? 'border-rose-400' : ($note->isFollowUpDueToday() ? 'border-amber-400' : 'border-amber-200') }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-lg">{{ $note->typeIcon() }}</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $note->typeLabel() }}</span>
                                    @if($note->subject)
                                    <span class="text-sm text-gray-500">· {{ $note->subject }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                                    <form action="{{ route('store.suppliers.notes.destroy', [$supplier, $note]) }}" method="POST"
                                          onsubmit="return confirm('Delete this note?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-rose-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <p class="mt-2 text-sm text-gray-700 leading-relaxed">{{ $note->body }}</p>

                            @if($note->created_by && $note->creator)
                            <p class="mt-2 text-xs text-gray-400">By {{ $note->creator->name }}</p>
                            @endif

                            @if($note->hasFollowUp())
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="followup-badge followup-{{ $note->followUpStatusBadge() }}">
                                        📅 Follow-up: {{ $note->follow_up_date->format('d M Y') }}
                                    </span>
                                    @if($note->follow_up_done)
                                    <span class="text-xs text-emerald-600 font-medium">✓ Done</span>
                                    @endif
                                </div>
                                @if(! $note->follow_up_done)
                                <form action="{{ route('store.suppliers.notes.done', [$supplier, $note]) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 underline">
                                        Mark done
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── PURCHASES TAB ───────────────────────────────────────────── --}}
        <div x-show="tab === 'purchases'" class="mt-6">
            <div class="card overflow-hidden">
                <h2 class="card-title mb-4">Recent Purchases</h2>
                @if($recentPurchases->isEmpty())
                <p class="text-sm text-gray-400 py-6 text-center">No purchases from this supplier yet.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="table-th">Invoice</th>
                                <th class="table-th">Date</th>
                                <th class="table-th">Items</th>
                                <th class="table-th text-right">Amount</th>
                                <th class="table-th text-right">Paid</th>
                                <th class="table-th">Status</th>
                                <th class="table-th"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentPurchases as $purchase)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="table-td font-mono font-semibold text-amber-700">{{ $purchase->invoice_number }}</td>
                                <td class="table-td text-gray-600">{{ $purchase->purchase_date->format('d M Y') }}</td>
                                <td class="table-td text-gray-600">{{ $purchase->items->count() }}</td>
                                <td class="table-td text-right font-semibold text-gray-900">₹{{ number_format($purchase->grand_total, 2) }}</td>
                                <td class="table-td text-right text-emerald-700">₹{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="table-td">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $purchase->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($purchase->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                                <td class="table-td">
                                    <a href="{{ route('store.purchases.show', $purchase) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View →</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- end x-data tabs --}}
</div>

@push('styles')
<style>
.stat-card { @apply bg-white rounded-xl border border-gray-100 shadow-sm p-4; }
.stat-label { @apply text-xs font-medium text-gray-400 uppercase tracking-wide; }
.stat-value { @apply text-2xl font-bold mt-1; }
.stat-sub { @apply text-xs text-gray-400 mt-0.5; }

.card { @apply bg-white rounded-xl border border-gray-100 shadow-sm p-5; }
.card-title { @apply text-base font-semibold text-gray-800; }

.tab-btn { @apply px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors; }
.tab-active { @apply border-indigo-600 text-indigo-700; }
.tab-inactive { @apply border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300; }

.table-th { @apply px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide; }
.table-td { @apply px-4 py-3; }

.followup-badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }
.followup-done { @apply bg-emerald-100 text-emerald-700; }
.followup-overdue { @apply bg-rose-100 text-rose-700; }
.followup-today { @apply bg-amber-100 text-amber-700; }
.followup-upcoming { @apply bg-blue-100 text-blue-700; }

.form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
.form-input { @apply block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none; }
.btn-primary { @apply inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors; }
.btn-secondary { @apply inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors; }
</style>
@endpush
@endsection
