<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Receipt / Credit Note - {{ $salesReturn->return_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
            }
            .print-container { 
                box-shadow: none !important; 
                border: none !important; 
                border-radius: 0 !important; 
                padding: 10px !important; 
                max-width: 100% !important; 
            }
            body.thermal-mode {
                font-family: 'Courier New', Courier, monospace !important;
                width: 80mm !important;
                font-size: 11px !important;
            }
            body.thermal-mode .thermal-container {
                width: 76mm !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 4px !important;
            }
        }
        .thermal-font {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body id="receiptBody" class="bg-slate-100 text-slate-900 p-3 sm:p-8 font-sans antialiased">
    
    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print max-w-4xl mx-auto mb-4 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Credit Note / Return:</span>
            <span class="font-mono font-black text-sm text-[#4b55c8]">{{ $salesReturn->return_number }}</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                Restocked
            </span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Format Switcher -->
            <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold">
                <button type="button" id="btnStandard" onclick="setFormat('standard')" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-800 transition">
                    Standard (A4 / A5)
                </button>
                <button type="button" id="btnThermal" onclick="setFormat('thermal')" class="px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition">
                    Thermal (80mm)
                </button>
            </div>

            <button onclick="window.print()" class="px-4 py-2 bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold text-xs rounded-xl shadow-md shadow-[#4b55c8]/20 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition">
                Close
            </button>
        </div>
    </div>

    <!-- STANDARD A4 / A5 VIEW -->
    <div id="standardView" class="print-container max-w-4xl mx-auto bg-white border border-slate-200 rounded-3xl p-6 sm:p-10 shadow-sm space-y-6">
        <!-- Pharmacy Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start border-b border-slate-200 pb-6 gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-[#4b55c8] text-white flex items-center justify-center font-black text-sm">
                        Rx
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $store->name }}</h1>
                </div>
                <p class="text-xs text-slate-500 max-w-md">{{ $store->address }}, {{ $store->city }}, {{ $store->state }} - {{ $store->postal_code ?? '' }}</p>
                <div class="text-xs text-slate-600 flex flex-wrap gap-x-4 gap-y-1 pt-1 font-semibold">
                    @if($store->phone) <span>Tel: <b>{{ $store->phone }}</b></span> @endif
                    @if($store->email) <span>Email: {{ $store->email }}</span> @endif
                    @if($store->tax_number) <span class="text-[#4b55c8]">GSTIN: <b>{{ $store->tax_number }}</b></span> @endif
                    @if($store->dl_number ?? false) <span>DL: <b>{{ $store->dl_number }}</b></span> @endif
                </div>
            </div>
            
            <div class="sm:text-right space-y-1">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-100">
                    Credit Note / Sales Return
                </span>
                <span class="text-lg font-black font-mono text-slate-900 block">{{ $salesReturn->return_number }}</span>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p>Return Date: <b class="text-slate-800">{{ $salesReturn->return_date->format('d M Y') }}</b></p>
                    <p>Original Invoice: <b class="text-slate-800">#{{ $salesReturn->sale?->invoice_number }}</b></p>
                    <p>Time: {{ $salesReturn->created_at->format('h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Customer & Return Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
            <div>
                <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Customer / Patient Details:</span>
                <span class="font-bold text-slate-900 text-sm block mt-0.5">
                    {{ $salesReturn->customer?->name ?: ($salesReturn->sale?->customer_name ?: 'Walk-in Customer') }}
                </span>
                @if($salesReturn->customer && $salesReturn->customer->customer_code)
                    <span class="text-slate-500 font-mono text-[10px] block">Customer Code: {{ $salesReturn->customer->customer_code }}</span>
                @endif
                @if($salesReturn->customer?->phone || $salesReturn->sale?->customer_phone) 
                    <span class="text-slate-600 block mt-0.5 font-medium">Contact: {{ $salesReturn->customer?->phone ?: $salesReturn->sale?->customer_phone }}</span> 
                @endif
                @if($salesReturn->customer?->doctor_name) 
                    <span class="text-slate-600 block mt-0.5 font-medium">Doctor: Dr. {{ $salesReturn->customer->doctor_name }}</span> 
                @endif
            </div>
            <div class="sm:text-right space-y-1">
                <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Settlement & Restocking:</span>
                <div class="font-semibold text-slate-700">Reason: <span class="font-bold text-slate-900">{{ $salesReturn->reason }}</span></div>
                <div>Status: <span class="font-bold text-emerald-700 uppercase">Restocked & Completed</span></div>
                @if($salesReturn->refund_method)
                    <div>Refund Mode: <span class="font-bold text-amber-800 uppercase">{{ str_replace('_', ' ', $salesReturn->refund_method) }}</span></div>
                @endif
            </div>
        </div>

        <!-- Returned Items Table -->
        <div class="overflow-hidden border border-slate-200 rounded-2xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Medicine Description</th>
                        <th class="p-3 font-mono">Batch</th>
                        <th class="p-3">Exp</th>
                        <th class="p-3 text-center">Ret Qty</th>
                        <th class="p-3 text-right">Unit Price</th>
                        <th class="p-3 text-right">Tax (GST)</th>
                        <th class="p-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
                    @foreach($salesReturn->items as $idx => $item)
                    <tr>
                        <td class="p-3 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="p-3 font-bold text-slate-900">
                            {{ $item->medicine?->displayName() }}
                            @if($item->reason)
                                <span class="text-[10px] text-slate-400 block font-normal italic">Reason: {{ $item->reason }}</span>
                            @endif
                        </td>
                        <td class="p-3 font-mono text-slate-700">{{ $item->batch?->batch_number ?: 'N/A' }}</td>
                        <td class="p-3 text-slate-500">{{ $item->batch?->expiry_date?->format('m/y') ?: '—' }}</td>
                        <td class="p-3 text-center font-bold text-emerald-600">{{ $item->quantity }}</td>
                        <td class="p-3 text-right font-mono">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-3 text-right font-mono text-slate-600">
                            ₹{{ number_format($item->tax_amount, 2) }}
                            <span class="text-[10px] text-slate-400">({{ $item->gst_rate }}%)</span>
                        </td>
                        <td class="p-3 text-right font-black font-mono text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Settlement Summary -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-2 gap-6">
            <div class="text-xs text-slate-500 max-w-sm space-y-2">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1 text-[11px]">
                    <p class="font-bold text-slate-700">Verification & Restocking Note:</p>
                    <p>Returned medicines have been verified for integrity, seal, and non-tampered batch packaging and restored to inventory.</p>
                </div>
                @if($salesReturn->customer)
                <div class="flex justify-between items-center text-xs font-semibold px-1">
                    <span>Customer Outstanding Balance:</span>
                    <span class="font-bold text-rose-600 font-mono">₹{{ number_format($salesReturn->customer->outstandingAmount(), 2) }}</span>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-mono font-bold text-slate-900">₹{{ number_format($salesReturn->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Total Tax Restored:</span>
                    <span class="font-mono font-bold text-slate-900">₹{{ number_format($salesReturn->tax, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-900 text-sm font-black border-t border-slate-200 pt-2">
                    <span>Total Return Value:</span>
                    <span class="font-mono">₹{{ number_format($salesReturn->grand_total, 2) }}</span>
                </div>
                
                @if($salesReturn->adjustment_amount > 0)
                <div class="flex justify-between text-emerald-700 font-bold bg-emerald-50 px-2 py-1 rounded-lg">
                    <span>Adjusted vs Sale Due:</span>
                    <span class="font-mono">-₹{{ number_format($salesReturn->adjustment_amount, 2) }}</span>
                </div>
                @endif

                @if($salesReturn->refund_amount > 0)
                <div class="flex justify-between text-amber-800 font-black text-sm bg-amber-50 px-2.5 py-1.5 rounded-lg border border-amber-200">
                    <span>Refund Paid Out:</span>
                    <span class="font-mono">₹{{ number_format($salesReturn->refund_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Signature Footer -->
        <div class="pt-8 border-t border-slate-200 flex justify-between items-end text-[11px] text-slate-500">
            <div>
                <p>Generated by: {{ $salesReturn->creator?->name ?: 'Authorized Pharmacist' }}</p>
                <p class="text-[10px] text-slate-400">System Record ID: SR-{{ $salesReturn->id }} | Generated on {{ now()->format('d M Y, h:i A') }}</p>
            </div>
            <div class="text-right">
                <div class="w-36 border-b border-slate-300 pb-8 mb-1"></div>
                <p class="font-bold text-slate-700">Authorized Signatory</p>
                <p class="text-[10px] text-slate-400">Registered Pharmacist</p>
            </div>
        </div>
    </div>

    <!-- THERMAL 80MM VIEW (Hidden by default, enabled when thermal selected) -->
    <div id="thermalView" class="thermal-container print-container hidden max-w-sm mx-auto bg-white border border-slate-200 p-4 shadow-sm text-xs thermal-font space-y-2">
        <div class="text-center pb-2 border-b border-dashed border-slate-400">
            <h2 class="font-bold text-sm">{{ strtoupper($store->name) }}</h2>
            <p>{{ $store->city }}, {{ $store->state }}</p>
            @if($store->phone) <p>Tel: {{ $store->phone }}</p> @endif
            @if($store->tax_number) <p>GST: {{ $store->tax_number }}</p> @endif
            @if($store->dl_number ?? false) <p>DL: {{ $store->dl_number }}</p> @endif
            <p class="font-bold mt-1">*** CREDIT NOTE / RETURN ***</p>
        </div>

        <div class="space-y-0.5 text-[11px] pb-2 border-b border-dashed border-slate-400">
            <div>Return #: <b>{{ $salesReturn->return_number }}</b></div>
            <div>Original Inv #: <b>{{ $salesReturn->sale?->invoice_number }}</b></div>
            <div>Date: {{ $salesReturn->return_date->format('d/m/Y') }} {{ $salesReturn->created_at->format('h:i A') }}</div>
            <div>Customer: <b>{{ $salesReturn->customer?->name ?: ($salesReturn->sale?->customer_name ?: 'Walk-in') }}</b></div>
            <div>Reason: {{ $salesReturn->reason }}</div>
        </div>

        <!-- Items -->
        <div class="pb-2 border-b border-dashed border-slate-400">
            <table class="w-full text-left text-[11px]">
                <thead>
                    <tr class="border-b border-slate-300">
                        <th class="py-1">Item</th>
                        <th class="text-center py-1">Qty</th>
                        <th class="text-right py-1">Price</th>
                        <th class="text-right py-1">Amt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesReturn->items as $item)
                    <tr>
                        <td class="py-0.5">{{ Str::limit($item->medicine?->name, 14) }}</td>
                        <td class="text-center py-0.5">{{ $item->quantity }}</td>
                        <td class="text-right py-0.5">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right py-0.5">{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="space-y-0.5 text-[11px] pt-1 pb-2 border-b border-dashed border-slate-400">
            <div class="flex justify-between">
                <span>Total Return:</span>
                <b>₹{{ number_format($salesReturn->grand_total, 2) }}</b>
            </div>
            @if($salesReturn->adjustment_amount > 0)
            <div class="flex justify-between">
                <span>Due Adjusted:</span>
                <span>₹{{ number_format($salesReturn->adjustment_amount, 2) }}</span>
            </div>
            @endif
            @if($salesReturn->refund_amount > 0)
            <div class="flex justify-between font-bold">
                <span>Refund Paid:</span>
                <span>₹{{ number_format($salesReturn->refund_amount, 2) }}</span>
            </div>
            <div class="text-[10px]">Refund Mode: {{ strtoupper(str_replace('_', ' ', $salesReturn->refund_method)) }}</div>
            @endif
        </div>

        <div class="text-center pt-2 text-[10px]">
            <p>Medicines returned & restocked.</p>
            <p>Thank you!</p>
        </div>
    </div>

    <script>
        function setFormat(format) {
            const body = document.getElementById('receiptBody');
            const standardView = document.getElementById('standardView');
            const thermalView = document.getElementById('thermalView');
            const btnStandard = document.getElementById('btnStandard');
            const btnThermal = document.getElementById('btnThermal');

            if (format === 'thermal') {
                body.classList.add('thermal-mode');
                standardView.classList.add('hidden');
                thermalView.classList.remove('hidden');
                btnThermal.classList.add('bg-white', 'shadow-xs', 'text-slate-800');
                btnThermal.classList.remove('text-slate-500');
                btnStandard.classList.remove('bg-white', 'shadow-xs', 'text-slate-800');
                btnStandard.classList.add('text-slate-500');
            } else {
                body.classList.remove('thermal-mode');
                standardView.classList.remove('hidden');
                thermalView.classList.add('hidden');
                btnStandard.classList.add('bg-white', 'shadow-xs', 'text-slate-800');
                btnStandard.classList.remove('text-slate-500');
                btnThermal.classList.remove('bg-white', 'shadow-xs', 'text-slate-800');
                btnThermal.classList.add('text-slate-500');
            }
        }
    </script>
</body>
</html>
