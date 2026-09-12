@extends('store.layouts.app')

@section('title', 'New Purchase Invoice')

@section('content')
<div class="h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.purchases.index') }}" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                    ← Back to Purchases
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1e2746] tracking-tight mt-1">Record Purchase Invoice</h1>
            <p class="text-xs sm:text-sm text-[#64748b] mt-0.5">Enter vendor bill details, medicines, batch numbers, and stock received.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
            <div class="font-bold mb-1">Please fix the following validation errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="purchaseForm" method="POST" action="{{ route('store.purchases.store') }}" class="space-y-6">
        @csrf

        <!-- Invoice Header Information -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-[#1e2746] border-b border-slate-100 pb-2 mb-4">Invoice Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Supplier <span class="text-rose-500">*</span></label>
                    <select name="supplier_id" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                        <option value="">-- Choose Supplier --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }} {{ $sup->company_name ? "({$sup->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Supplier Invoice Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none" placeholder="e.g. INV-9982">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Purchase / Invoice Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Overall Bill Discount (₹)</label>
                    <input type="number" step="0.01" min="0" name="discount" id="overallDiscount" value="{{ old('discount', '0.00') }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm font-mono focus:border-[#4b55c8] outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Notes / Terms</label>
                <input type="text" name="notes" value="{{ old('notes') }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs sm:text-sm focus:border-[#4b55c8] outline-none" placeholder="Payment terms, transport details...">
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#1e2746]">Purchase Line Items</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Add medicine batches received under this invoice.</p>
                </div>
                <button type="button" id="addItemBtn" class="px-4 py-2 rounded-2xl bg-blue-50 text-[#4b55c8] hover:bg-blue-100 font-bold text-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Item</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="itemsTable">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-extrabold text-[#64748b] uppercase tracking-wider bg-slate-50/50">
                            <th class="py-2.5 px-3 min-w-[200px]">Medicine</th>
                            <th class="py-2.5 px-3 min-w-[140px]">Batch No.</th>
                            <th class="py-2.5 px-3 min-w-[130px]">Expiry</th>
                            <th class="py-2.5 px-3 w-20">Qty</th>
                            <th class="py-2.5 px-3 w-20">Free</th>
                            <th class="py-2.5 px-3 w-24">Cost (₹)</th>
                            <th class="py-2.5 px-3 w-24">MRP (₹)</th>
                            <th class="py-2.5 px-3 w-20">GST %</th>
                            <th class="py-2.5 px-3 w-24 text-right">Line Total</th>
                            <th class="py-2.5 px-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody" class="divide-y divide-slate-100 text-xs">
                        <!-- Javascript will dynamically add rows here -->
                    </tbody>
                </table>
            </div>

            <!-- Grand Totals Display -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-6 pt-4 border-t border-slate-100">
                <div class="w-full sm:w-72 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Items Subtotal:</span>
                        <span id="subtotalDisplay" class="font-mono font-bold text-[#1e2746]">₹0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Tax / GST:</span>
                        <span id="taxDisplay" class="font-mono font-bold text-[#1e2746]">₹0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Overall Discount:</span>
                        <span id="discountDisplay" class="font-mono font-bold text-rose-600">-₹0.00</span>
                    </div>
                    <div class="flex justify-between text-sm font-black pt-2 border-t border-slate-200">
                        <span class="text-[#1e2746]">Grand Total:</span>
                        <span id="grandTotalDisplay" class="font-mono text-base text-[#4b55c8]">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('store.purchases.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs sm:text-sm transition">
                Cancel
            </a>

            <!-- Save as Draft -->
            <button type="submit" name="status" value="draft" class="px-5 py-2.5 rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm transition shadow-sm">
                Save as Draft
            </button>

            <!-- Complete and Ingest Stock -->
            <button type="submit" name="status" value="completed" class="px-6 py-2.5 rounded-2xl bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#4b55c8]/25 transition">
                Complete Purchase & Update Stock
            </button>
        </div>
    </form>

</div>

<script>
    const medicines = @json($medicines);
    let itemIndex = 0;

    function addRow(data = {}) {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/50 transition item-row';
        tr.dataset.index = itemIndex;

        let medOptions = '<option value="">-- Select --</option>';
        medicines.forEach(m => {
            const selected = (data.medicine_id && data.medicine_id == m.id) ? 'selected' : '';
            medOptions += `<option value="${m.id}" data-gst="${m.gst_rate || 0}" ${selected}>${m.name}</option>`;
        });

        tr.innerHTML = `
            <td class="py-2 px-3">
                <select name="items[${itemIndex}][medicine_id]" required class="medicine-select w-full px-2 py-1.5 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
                    ${medOptions}
                </select>
            </td>
            <td class="py-2 px-3">
                <input type="text" name="items[${itemIndex}][batch_number]" value="${data.batch_number || ''}" required placeholder="Batch #" class="w-full px-2 py-1.5 rounded-xl border border-slate-200 font-mono text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3">
                <input type="date" name="items[${itemIndex}][expiry_date]" value="${data.expiry_date || ''}" required class="w-full px-2 py-1.5 rounded-xl border border-slate-200 text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3">
                <input type="number" min="1" name="items[${itemIndex}][quantity]" value="${data.quantity || 1}" required class="qty-input w-full px-2 py-1.5 rounded-xl border border-slate-200 font-mono text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3">
                <input type="number" min="0" name="items[${itemIndex}][free_quantity]" value="${data.free_quantity || 0}" class="free-qty-input w-full px-2 py-1.5 rounded-xl border border-slate-200 font-mono text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3">
                <input type="number" step="0.01" min="0" name="items[${itemIndex}][purchase_price]" value="${data.purchase_price || '0.00'}" required class="cost-input w-full px-2 py-1.5 rounded-xl border border-slate-200 font-mono text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3">
                <input type="number" step="0.01" min="0" name="items[${itemIndex}][mrp]" value="${data.mrp || '0.00'}" required class="mrp-input w-full px-2 py-1.5 rounded-xl border border-slate-200 font-mono text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3">
                <input type="number" step="0.01" min="0" max="100" name="items[${itemIndex}][gst_rate]" value="${data.gst_rate || 0}" class="gst-input w-full px-2 py-1.5 rounded-xl border border-slate-200 font-mono text-xs focus:border-[#4b55c8] outline-none">
            </td>
            <td class="py-2 px-3 text-right font-mono font-bold text-[#1e2746] line-total-cell">
                ₹0.00
            </td>
            <td class="py-2 px-3 text-center">
                <button type="button" class="remove-item-btn text-rose-500 hover:text-rose-700 font-bold p-1">✕</button>
            </td>
        `;

        tbody.appendChild(tr);

        // Auto populate GST rate on medicine change
        const medSelect = tr.querySelector('.medicine-select');
        medSelect.addEventListener('change', function() {
            const selectedOption = medSelect.options[medSelect.selectedIndex];
            const defaultGst = selectedOption.getAttribute('data-gst');
            if (defaultGst) {
                tr.querySelector('.gst-input').value = defaultGst;
            }
            recalculateTotals();
        });

        // Event listeners for recalculations
        tr.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', recalculateTotals);
        });

        tr.querySelector('.remove-item-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                tr.remove();
                recalculateTotals();
            } else {
                alert('At least one item is required in the purchase.');
            }
        });

        itemIndex++;
        recalculateTotals();
    }

    function recalculateTotals() {
        let runningSubtotal = 0;
        let runningTax = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const gst = parseFloat(row.querySelector('.gst-input').value) || 0;

            const baseAmount = qty * cost;
            const taxAmount = baseAmount * (gst / 100);
            const lineTotal = baseAmount + taxAmount;

            runningSubtotal += baseAmount;
            runningTax += taxAmount;

            row.querySelector('.line-total-cell').textContent = '₹' + lineTotal.toFixed(2);
        });

        const overallDiscount = parseFloat(document.getElementById('overallDiscount').value) || 0;
        const grandTotal = Math.max(0, (runningSubtotal + runningTax) - overallDiscount);

        document.getElementById('subtotalDisplay').textContent = '₹' + runningSubtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = '₹' + runningTax.toFixed(2);
        document.getElementById('discountDisplay').textContent = '-₹' + overallDiscount.toFixed(2);
        document.getElementById('grandTotalDisplay').textContent = '₹' + grandTotal.toFixed(2);
    }

    document.getElementById('addItemBtn').addEventListener('click', () => addRow());
    document.getElementById('overallDiscount').addEventListener('input', recalculateTotals);

    // Initial rows
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endsection
