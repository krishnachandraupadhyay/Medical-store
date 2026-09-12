<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $sale->invoice_number }}</title>
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
            /* Thermal print rules when in thermal mode */
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
<body id="invoiceBody" class="bg-slate-100 text-slate-900 p-3 sm:p-8 font-sans antialiased">
    
    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print max-w-4xl mx-auto mb-4 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Retail Tax Invoice:</span>
            <span class="font-mono font-black text-sm text-[#4b55c8]">{{ $sale->invoice_number }}</span>
            @if($sale->hold_reference)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                    Held Bill: {{ $sale->hold_reference }}
                </span>
            @endif
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
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-indigo-50 text-[#4b55c8] border border-indigo-100">
                    Retail GST Invoice
                </span>
                <span class="text-lg font-black font-mono text-slate-900 block">{{ $sale->invoice_number }}</span>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p>Invoice Date: <b class="text-slate-800">{{ $sale->sale_date->format('d M Y') }}</b></p>
                    <p>Time: {{ $sale->created_at->format('h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Billed To & Payment Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
            <div>
                <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Patient / Billed To:</span>
                <span class="font-bold text-slate-900 text-sm block mt-0.5">{{ $sale->customer_name ?: 'Walk-in Customer' }}</span>
                @if($sale->customer && $sale->customer->customer_code)
                    <span class="text-slate-500 font-mono text-[10px] block">Customer Code: {{ $sale->customer->customer_code }}</span>
                @endif
                @if($sale->customer_phone) 
                    <span class="text-slate-600 block mt-0.5 font-medium">Contact: {{ $sale->customer_phone }}</span> 
                @endif
                @if($sale->customer && $sale->customer->address) 
                    <span class="text-slate-500 block mt-0.5">{{ $sale->customer->address }}</span> 
                @endif
                @if($sale->customer && $sale->customer->doctor_name)
                    <span class="text-slate-700 block mt-0.5 font-semibold">Doctor: Dr. {{ $sale->customer->doctor_name }}</span>
                @endif
            </div>
            <div class="sm:text-right">
                <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Payment Overview:</span>
                <div class="mt-1 flex sm:justify-end items-center gap-2">
                    @php
                        $statusClass = match($sale->payment_status?->value) {
                            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'partial' => 'bg-amber-100 text-amber-800 border-amber-200',
                            default => 'bg-rose-100 text-rose-800 border-rose-200',
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase border {{ $statusClass }}">
                        {{ strtoupper($sale->payment_status?->value ?? 'UNPAID') }}
                    </span>
                </div>
                <span class="text-xs text-slate-600 block mt-1">
                    Primary Tender: <b>{{ strtoupper($sale->payment_method instanceof \App\Enums\PaymentMethod ? $sale->payment_method->value : ($sale->payment_method ?: 'Cash')) }}</b>
                </span>
            </div>
        </div>

        <!-- Items Table with HSN & GST Breakdown -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y border-slate-200 bg-slate-50/80 text-slate-700 font-extrabold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Item Description</th>
                        <th class="py-2.5 px-2 text-center">HSN</th>
                        <th class="py-2.5 px-2">Batch</th>
                        <th class="py-2.5 px-2 text-center">Exp</th>
                        <th class="py-2.5 px-2 text-right">MRP</th>
                        <th class="py-2.5 px-2 text-right">Rate</th>
                        <th class="py-2.5 px-2 text-center">Qty</th>
                        <th class="py-2.5 px-2 text-right">Disc</th>
                        <th class="py-2.5 px-2 text-center">GST</th>
                        <th class="py-2.5 px-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @php
                        $taxBreakdown = [];
                    @endphp
                    @foreach($sale->items as $idx => $item)
                    @php
                        $gst = (float) ($item->gst_rate ?? 0);
                        $lineTax = (float) ($item->tax_amount ?? 0);
                        $taxable = max(0, ((float) $item->unit_price * $item->quantity) - (float) ($item->discount ?? 0));
                        if (!isset($taxBreakdown[$gst])) {
                            $taxBreakdown[$gst] = ['taxable' => 0.0, 'tax' => 0.0];
                        }
                        $taxBreakdown[$gst]['taxable'] += $taxable;
                        $taxBreakdown[$gst]['tax'] += $lineTax;
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-2.5 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3">
                            <span class="font-bold text-slate-900 block">{{ $item->medicine?->displayName() }}</span>
                            @if($item->medicine?->generic_name)
                                <span class="text-[10px] text-slate-400 block">{{ $item->medicine->generic_name }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-500">{{ $item->medicine?->hsn_code ?: '-' }}</td>
                        <td class="py-2.5 px-2 font-mono font-bold text-slate-800">{{ $item->batch?->batch_number }}</td>
                        <td class="py-2.5 px-2 text-center font-mono text-slate-600">{{ $item->batch?->expiry_date?->format('m/y') }}</td>
                        <td class="py-2.5 px-2 text-right font-mono text-slate-500">₹{{ number_format((float) ($item->mrp ?: $item->unit_price), 2) }}</td>
                        <td class="py-2.5 px-2 text-right font-mono font-semibold">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2.5 px-2 text-center font-bold">{{ $item->quantity }}</td>
                        <td class="py-2.5 px-2 text-right font-mono text-emerald-600">
                            {{ (float) $item->discount > 0 ? '₹'.number_format($item->discount, 2) : '-' }}
                        </td>
                        <td class="py-2.5 px-2 text-center font-mono font-semibold text-slate-600">{{ $gst }}%</td>
                        <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tax Breakdown & Financial Totals Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
            <!-- Left: GST Summary & Multi-Payment Details -->
            <div class="space-y-4">
                @if(count($taxBreakdown) > 0)
                <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                    <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider block mb-2">GST Tax Summary</span>
                    <table class="w-full text-left text-[11px] font-mono">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-200">
                                <th class="pb-1">Rate</th>
                                <th class="pb-1 text-right">Taxable</th>
                                <th class="pb-1 text-right">CGST</th>
                                <th class="pb-1 text-right">SGST</th>
                                <th class="pb-1 text-right">Total Tax</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($taxBreakdown as $rate => $vals)
                            @php
                                $halfTax = round($vals['tax'] / 2, 2);
                            @endphp
                            <tr>
                                <td class="py-1 font-bold text-slate-700">{{ $rate }}%</td>
                                <td class="py-1 text-right">₹{{ number_format($vals['taxable'], 2) }}</td>
                                <td class="py-1 text-right text-slate-500">₹{{ number_format($halfTax, 2) }}</td>
                                <td class="py-1 text-right text-slate-500">₹{{ number_format($halfTax, 2) }}</td>
                                <td class="py-1 text-right font-bold text-slate-800">₹{{ number_format($vals['tax'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Payment Tender Details -->
                @if($sale->payments->count() > 0)
                <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                    <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider block mb-2">Payment Receipts / Split Tender</span>
                    <div class="space-y-1.5 text-xs font-mono">
                        @foreach($sale->payments as $pmt)
                        <div class="flex items-center justify-between py-1 border-b border-slate-100 last:border-0">
                            <div>
                                <span class="font-bold text-slate-800 uppercase">{{ $pmt->payment_method }}</span>
                                @if($pmt->reference_number)
                                    <span class="text-[10px] text-slate-400 block font-normal">Ref: {{ $pmt->reference_number }}</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="font-black text-emerald-700">₹{{ number_format($pmt->amount, 2) }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $pmt->payment_date?->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right: Grand Totals Calculation -->
            <div class="flex justify-end">
                <div class="w-full max-w-xs space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal:</span>
                        <span class="font-mono font-bold">₹{{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    @if($sale->discount > 0)
                    <div class="flex justify-between text-emerald-600 font-semibold">
                        <span>Bill Discount:</span>
                        <span class="font-mono">-₹{{ number_format($sale->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($sale->tax > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>GST Tax Amount:</span>
                        <span class="font-mono font-bold">₹{{ number_format($sale->tax, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between text-base font-black text-slate-900 border-t-2 border-slate-900 pt-2 pb-1">
                        <span>Grand Total:</span>
                        <span class="font-mono text-[#4b55c8]">₹{{ number_format($sale->grand_total, 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between text-slate-700 font-bold border-t border-slate-200 pt-1.5">
                        <span>Amount Paid:</span>
                        <span class="font-mono text-emerald-700">₹{{ number_format($sale->paid_amount, 2) }}</span>
                    </div>

                    @if($sale->outstandingAmount() > 0)
                    <div class="flex justify-between text-rose-600 font-black bg-rose-50 p-2 rounded-xl border border-rose-200">
                        <span>Balance Due:</span>
                        <span class="font-mono">₹{{ number_format($sale->outstandingAmount(), 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Terms and Notes Footer -->
        <div class="pt-6 border-t border-slate-200 text-center text-[10px] text-slate-400 space-y-1">
            <p class="font-bold text-slate-600">Thank you for visiting {{ $store->name }}! Wish you a quick recovery.</p>
            <p>Medicines once sold cannot be returned without original cash memo & batch verification. Store under recommended temperature conditions.</p>
            <p class="text-[9px] text-slate-300 font-mono">Invoice #{{ $sale->invoice_number }} • Generated on {{ now()->format('d-m-Y H:i:s') }}</p>
        </div>
    </div>

    <!-- THERMAL 80MM RECEIPT VIEW (Hidden by default, switched via JS or ?format=thermal) -->
    <div id="thermalView" class="thermal-container thermal-font hidden max-w-sm mx-auto bg-white border border-dashed border-slate-300 p-4 text-xs text-black">
        <!-- Header -->
        <div class="text-center pb-2 border-b border-black">
            <h2 class="font-black text-sm uppercase">{{ $store->name }}</h2>
            <p class="text-[10px]">{{ $store->address }}, {{ $store->city }}</p>
            @if($store->phone) <p class="text-[10px]">Tel: {{ $store->phone }}</p> @endif
            @if($store->tax_number) <p class="text-[10px] font-bold">GSTIN: {{ $store->tax_number }}</p> @endif
            <p class="font-black text-xs mt-1 border-t border-dotted border-black pt-1">RETAIL TAX INVOICE</p>
        </div>

        <!-- Meta -->
        <div class="py-2 border-b border-dotted border-black text-[11px] space-y-0.5">
            <div class="flex justify-between">
                <span>Inv: <b>{{ $sale->invoice_number }}</b></span>
                <span>{{ $sale->sale_date->format('d/m/y') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Customer: {{ Str::limit($sale->customer_name ?: 'Walk-in', 16) }}</span>
                <span>{{ $sale->created_at->format('H:i') }}</span>
            </div>
            @if($sale->customer_phone)
                <div>Phone: {{ $sale->customer_phone }}</div>
            @endif
            @if($sale->customer && $sale->customer->customer_code)
                <div>Code: {{ $sale->customer->customer_code }}</div>
            @endif
            @if($sale->customer && $sale->customer->doctor_name)
                <div>Dr: {{ $sale->customer->doctor_name }}</div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="py-2 border-b border-black">
            <div class="flex justify-between font-black text-[10px] uppercase pb-1 border-b border-dotted border-black">
                <span class="w-1/2">Item/Batch</span>
                <span class="w-1/6 text-center">Qty</span>
                <span class="w-1/6 text-right">Rate</span>
                <span class="w-1/6 text-right">Amt</span>
            </div>
            <div class="space-y-1.5 pt-1 text-[11px]">
                @foreach($sale->items as $item)
                <div>
                    <div class="font-bold truncate">{{ $item->medicine?->displayName() }}</div>
                    <div class="flex justify-between text-[10px]">
                        <span class="text-slate-600">B:{{ $item->batch?->batch_number }} E:{{ $item->batch?->expiry_date?->format('m/y') }}</span>
                        <span class="text-center">{{ $item->quantity }}</span>
                        <span class="text-right">₹{{ number_format($item->unit_price, 2) }}</span>
                        <span class="text-right font-bold">₹{{ number_format($item->line_total, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Totals -->
        <div class="py-2 border-b border-dotted border-black text-[11px] space-y-1">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>₹{{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->discount > 0)
            <div class="flex justify-between">
                <span>Discount:</span>
                <span>-₹{{ number_format($sale->discount, 2) }}</span>
            </div>
            @endif
            @if($sale->tax > 0)
            <div class="flex justify-between">
                <span>Tax (GST):</span>
                <span>₹{{ number_format($sale->tax, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between font-black text-sm pt-1 border-t border-black">
                <span>TOTAL:</span>
                <span>₹{{ number_format($sale->grand_total, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold pt-0.5">
                <span>Paid ({{ strtoupper($sale->payment_method instanceof \App\Enums\PaymentMethod ? $sale->payment_method->value : ($sale->payment_method ?: 'Cash')) }}):</span>
                <span>₹{{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            @if($sale->outstandingAmount() > 0)
            <div class="flex justify-between font-black">
                <span>DUE:</span>
                <span>₹{{ number_format($sale->outstandingAmount(), 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="text-center pt-2 text-[10px] space-y-1">
            <p class="font-bold">THANK YOU! GET WELL SOON</p>
            <p>No exchange without memo</p>
            <p class="text-[9px]">*** End of Receipt ***</p>
        </div>
    </div>

    <script>
        function setFormat(format) {
            const stdView = document.getElementById('standardView');
            const thmView = document.getElementById('thermalView');
            const btnStd = document.getElementById('btnStandard');
            const btnThm = document.getElementById('btnThermal');
            const body = document.getElementById('invoiceBody');

            if (format === 'thermal') {
                stdView.classList.add('hidden');
                thmView.classList.remove('hidden');
                btnThm.classList.add('bg-white', 'shadow-xs', 'text-slate-800');
                btnThm.classList.remove('text-slate-500');
                btnStd.classList.remove('bg-white', 'shadow-xs', 'text-slate-800');
                btnStd.classList.add('text-slate-500');
                body.classList.add('thermal-mode');
            } else {
                thmView.classList.add('hidden');
                stdView.classList.remove('hidden');
                btnStd.classList.add('bg-white', 'shadow-xs', 'text-slate-800');
                btnStd.classList.remove('text-slate-500');
                btnThm.classList.remove('bg-white', 'shadow-xs', 'text-slate-800');
                btnThm.classList.add('text-slate-500');
                body.classList.remove('thermal-mode');
            }
        }

        // Check URL parameter format=thermal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('format') === 'thermal') {
            setFormat('thermal');
        }
    </script>
</body>
</html>
