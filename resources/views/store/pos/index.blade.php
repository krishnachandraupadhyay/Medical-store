@extends('store.layouts.app')

@section('title', 'Advanced POS Terminal & Barcode Billing')

@section('content')
<div class="h-[calc(100vh-100px)] flex flex-col gap-3">
    <!-- Top Bar -->
    <div class="flex flex-wrap items-center justify-between bg-white px-5 py-3 rounded-2xl border border-slate-200/80 shadow-xs flex-shrink-0 gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-black text-slate-900 leading-tight">Advanced POS Terminal</h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Barcode Ready
                    </span>
                </div>
                <p class="text-xs text-slate-400">High-speed retail dispensary & barcode checkout</p>
            </div>
        </div>

        <!-- Center: Quick Shortcuts Strip -->
        <div class="hidden xl:flex items-center gap-1.5 text-[11px] font-bold text-slate-500 bg-slate-100/80 px-3 py-1.5 rounded-xl border border-slate-200/60">
            <span class="text-slate-400 font-semibold mr-1">Hotkeys:</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F2 Barcode</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F3 Search</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F4 Customer</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F6 Held</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F8 Hold</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F9 Split</span>
            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs text-slate-700">F10 Pay</span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Held Bills Button with Badge -->
            <button type="button" onclick="openHeldBillsModal()" id="heldBillsBtn" class="relative px-3 py-2 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Held Bills (F6)</span>
                <span id="heldBillsCountBadge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-black bg-amber-500 text-white">0</span>
            </button>

            <!-- Shortcuts Help Modal Button -->
            <button type="button" onclick="openShortcutsModal()" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition" title="View Keyboard Shortcuts (F1)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>

            <a href="{{ route('store.sales.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                Invoices History
            </a>
        </div>
    </div>

    <!-- POS Main Split: Left Catalog/Search, Right Cart/Checkout -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-3 overflow-hidden">
        
        <!-- Left Column: Barcode Scan & Medicine Search (7 cols) -->
        <div class="lg:col-span-7 flex flex-col gap-3 overflow-hidden">
            
            <!-- Scan & Search Dual Bar -->
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs flex-shrink-0 space-y-2.5">
                
                <!-- 1. Dedicated Fast Barcode Scanner Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <input type="text" id="barcodeInput" autofocus placeholder="Scan Barcode (or press F2) - Press Enter to Auto-Add..." autocomplete="off" class="w-full text-sm font-mono font-bold rounded-xl border-2 border-emerald-300 pl-11 pr-28 py-2.5 bg-emerald-50/20 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/15 outline-none transition placeholder:font-sans placeholder:font-normal placeholder:text-slate-400">
                    <div class="absolute inset-y-0 right-1.5 flex items-center gap-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-1 rounded-lg border border-emerald-200">
                            Auto-Add ON
                        </span>
                    </div>
                </div>

                <!-- 2. Medicine Manual Name / Generic Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="medicineSearchInput" placeholder="Manual search by medicine, brand, generic (Press F3)..." autocomplete="off" class="w-full text-xs font-semibold rounded-xl border border-slate-200 pl-9 pr-4 py-2 focus:border-[#4b55c8] focus:ring-2 focus:ring-[#4b55c8]/15 outline-none transition">
                </div>

                <!-- Toast feedback alert inside search panel -->
                <div id="scanAlert" class="hidden p-2 rounded-xl text-xs font-bold transition flex items-center justify-between">
                    <span id="scanAlertText"></span>
                    <button type="button" onclick="dismissScanAlert()" class="text-xs opacity-70 hover:opacity-100 font-bold ml-2">✕</button>
                </div>
            </div>

            <!-- Search Results / Batches List -->
            <div id="searchResultsContainer" class="flex-1 bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs overflow-y-auto space-y-3">
                <div id="searchPlaceholder" class="h-full flex flex-col items-center justify-center text-slate-400 py-12">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Scan Barcode or Search Medicine</p>
                    <p class="text-xs text-slate-400 mt-0.5">Use barcode scanner above or search by medicine name to browse batches</p>
                </div>
                <div id="resultsList" class="space-y-3 hidden"></div>
            </div>
        </div>

        <!-- Right Column: Cart, Customer & Checkout (5 cols) -->
        <div class="lg:col-span-5 flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <form id="checkoutForm" method="POST" action="{{ route('store.pos.checkout') }}" class="h-full flex flex-col justify-between">
                @csrf
                <input type="hidden" name="sale_date" value="{{ now()->toDateString() }}">
                <input type="hidden" name="hold_reference" id="holdRefInput" value="">

                <!-- Customer Selection -->
                <div class="p-3.5 border-b border-slate-100 bg-slate-50/50 flex-shrink-0 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold uppercase text-slate-600 tracking-wider flex items-center gap-1.5">
                            <span>Customer Info</span>
                            <span class="text-[10px] text-slate-400 font-normal">(F4 to Add)</span>
                        </span>
                        <button type="button" onclick="openQuickCustomerModal()" class="text-xs font-bold text-[#4b55c8] hover:underline flex items-center gap-1">
                            + Quick Add
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <select name="customer_id" id="customerSelect" class="w-full text-xs font-semibold rounded-xl border border-slate-200 p-2 focus:border-[#4b55c8] outline-none">
                            <option value="" data-code="" data-name="Walk-in Customer" data-phone="">Walk-in Customer</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}" data-code="{{ $c->customer_code }}">{{ $c->name }} [{{ $c->customer_code }}] ({{ $c->phone ?: 'No phone' }})</option>
                            @endforeach
                        </select>
                        <input type="text" name="customer_phone" id="customerPhoneInput" placeholder="Customer Phone" class="w-full text-xs rounded-xl border border-slate-200 p-2 outline-none">
                    </div>
                    <div id="selectedCustomerBadge" class="hidden text-[10px] text-slate-500 font-medium flex items-center gap-2 pt-0.5">
                        <span>Code: <b id="selectedCustCode" class="text-slate-800 font-mono"></b></span>
                    </div>
                    <input type="hidden" name="customer_name" id="customerNameInput" value="Walk-in Customer">
                </div>

                <!-- Prescription Warning Banner -->
                <div id="rxBanner" class="hidden mx-3 mt-2 p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>One or more medicines in cart require a valid Doctor's Prescription.</span>
                </div>

                <!-- Cart Items Table -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <div id="emptyCartMessage" class="h-full flex flex-col items-center justify-center text-slate-400 py-10">
                        <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-xs font-bold text-slate-600">Cart is empty</p>
                        <p class="text-[11px] text-slate-400">Scan barcode or select medicine from catalog</p>
                    </div>
                    <div id="cartItemsList" class="space-y-2"></div>
                </div>

                <!-- Totals & Payment Details Footer -->
                <div class="border-t border-slate-200 bg-slate-50/90 p-3.5 space-y-2.5 flex-shrink-0">
                    <!-- Live Calculations Summary -->
                    <div class="space-y-1 text-xs text-slate-600">
                        <div class="flex justify-between font-semibold">
                            <span>Subtotal</span>
                            <span id="subtotalDisplay" class="font-mono">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span class="font-semibold">Overall Bill Discount</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-slate-400">₹</span>
                                <input type="number" name="discount" id="cartDiscount" value="0.00" step="0.01" min="0" oninput="calculateTotals()" class="w-20 text-right text-xs rounded-lg border border-slate-200 px-2 py-0.5 outline-none font-mono font-bold">
                            </div>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>GST / Taxes</span>
                            <span id="taxDisplay" class="font-mono">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm font-black text-slate-900 pt-1.5 border-t border-slate-200">
                            <span>Grand Total</span>
                            <span id="grandTotalDisplay" class="text-base text-[#4b55c8] font-mono">₹0.00</span>
                        </div>
                    </div>

                    <!-- Payment Mode Switcher (Single vs Split) -->
                    <div class="pt-1 border-t border-slate-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase text-slate-500">Tender Mode:</span>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btnSinglePayment" onclick="setTenderMode('single')" class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-white border border-slate-300 text-slate-800 shadow-2xs">
                                    Single (Cash/UPI)
                                </button>
                                <button type="button" id="btnSplitPayment" onclick="setTenderMode('split')" class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg text-slate-500 hover:text-slate-800">
                                    Split Tender (F9)
                                </button>
                            </div>
                        </div>

                        <!-- SINGLE PAYMENT MODE VIEW -->
                        <div id="singlePaymentView" class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500 block mb-0.5">Method</label>
                                <select name="payment_method" id="singlePaymentMethod" class="w-full text-xs font-bold rounded-xl border border-slate-200 p-2 outline-none">
                                    @foreach($paymentMethods as $m)
                                    <option value="{{ $m->value }}">{{ $m->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500 block mb-0.5">Tender / Received (₹)</label>
                                <input type="number" name="paid_amount" id="paidAmountInput" step="0.01" min="0" value="0.00" oninput="updateChangeDisplay()" class="w-full text-xs font-black font-mono rounded-xl border border-slate-200 p-2 outline-none">
                            </div>
                        </div>

                        <!-- SPLIT PAYMENT MODE VIEW -->
                        <div id="splitPaymentView" class="hidden space-y-2">
                            <div id="splitRowsContainer" class="space-y-1.5 max-h-28 overflow-y-auto">
                                <!-- Dynamic rows inserted by JS -->
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <button type="button" onclick="addSplitRow()" class="text-xs font-bold text-[#4b55c8] hover:underline">
                                    + Add Split Payment Row
                                </button>
                                <div class="text-[11px] font-bold">
                                    <span class="text-slate-400">Total Tendered: </span>
                                    <span id="splitTotalDisplay" class="font-mono text-slate-800 font-black">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Change Due / Balance Due Indicator -->
                        <div id="changeDueRow" class="hidden mt-2 flex justify-between text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                            <span>Change Due to Customer:</span>
                            <span id="changeDisplay" class="font-mono">₹0.00</span>
                        </div>
                    </div>

                    <!-- POS Action Buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <!-- 1. Hold Bill -->
                        <button type="button" onclick="holdCurrentBill()" class="py-2.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 font-bold text-xs text-amber-800 transition flex items-center justify-center gap-1 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Hold (F8)</span>
                        </button>

                        <!-- 2. Save Draft -->
                        <button type="submit" name="status" value="draft" class="py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 font-bold text-xs text-slate-700 transition">
                            Draft
                        </button>

                        <!-- 3. Checkout & Print -->
                        <button type="submit" name="status" value="completed" id="btnCheckout" class="py-2.5 rounded-xl bg-gradient-to-r from-[#4b55c8] to-[#3a44b5] hover:from-[#3f49b8] hover:to-[#313a9d] font-black text-xs text-white shadow-md shadow-[#4b55c8]/25 transition flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Pay (F10)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Quick Customer Modal (F4) -->
<div id="quickCustomerModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900">Quick Add Customer</h3>
            <button type="button" onclick="closeQuickCustomerModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Customer Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qcName" class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Phone Number</label>
                <input type="text" id="qcPhone" class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]" placeholder="10 digit mobile">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Email Address</label>
                <input type="email" id="qcEmail" class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-[#4b55c8]">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeQuickCustomerModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel (Esc)</button>
            <button type="button" onclick="submitQuickCustomer()" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#4b55c8] text-white hover:bg-[#3f49b8] shadow-sm">Save Customer</button>
        </div>
    </div>
</div>

<!-- 2. Held Bills Drawer/Modal (F6) -->
<div id="heldBillsModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-4 shadow-2xl border border-slate-100 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">Held POS Bills</h3>
                    <p class="text-xs text-slate-400">Select a held bill to resume checkout or discard</p>
                </div>
            </div>
            <button type="button" onclick="closeHeldBillsModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <div id="heldBillsList" class="flex-1 overflow-y-auto space-y-2.5 py-1">
            <div class="text-center py-12 text-slate-400 text-xs">Loading held bills...</div>
        </div>

        <div class="flex justify-between items-center pt-3 border-t border-slate-100 text-xs text-slate-500">
            <span>Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 font-mono font-bold text-slate-700">Esc</kbd> to close</span>
            <button type="button" onclick="closeHeldBillsModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Close</button>
        </div>
    </div>
</div>

<!-- 3. Hold Bill Confirmation Dialog (F8) -->
<div id="holdConfirmModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900">Hold Current Bill</h3>
            <button type="button" onclick="closeHoldConfirmModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <p class="text-xs text-slate-500">
            Save current cart as a held draft so you can attend to another customer. You can resume this bill at any time.
        </p>
        <div>
            <label class="text-xs font-bold text-slate-700 block mb-1">Hold Reference / Tag (Optional)</label>
            <input type="text" id="holdRefInputCustom" placeholder="e.g. Counter 2 / Customer in green shirt" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-amber-500">
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" onclick="closeHoldConfirmModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
            <button type="button" onclick="confirmHoldBill()" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 text-white hover:bg-amber-600 shadow-xs">Hold Bill Now</button>
        </div>
    </div>
</div>

<!-- 4. Keyboard Shortcuts Reference Modal (F1) -->
<div id="shortcutsModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span>⌨️</span> Keyboard Shortcuts
            </h3>
            <button type="button" onclick="closeShortcutsModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Barcode Scanner</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-emerald-700">F2</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Medicine Search</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-[#4b55c8]">F3</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Add Customer</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-slate-800">F4</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Held Bills</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-amber-700">F6</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Hold Active Bill</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-amber-700">F8</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Split Tender Mode</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-purple-700">F9</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between col-span-2">
                <span class="text-slate-600 font-semibold">Complete Checkout & Print</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-emerald-700">F10</kbd>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between col-span-2">
                <span class="text-slate-600 font-semibold">Close Any Open Modal</span>
                <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono font-bold text-slate-700">Esc</kbd>
            </div>
        </div>
        <div class="pt-2 text-right">
            <button type="button" onclick="closeShortcutsModal()" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700">Got it</button>
        </div>
    </div>
</div>

<script>
    // State
    let cart = [];
    let tenderMode = 'single'; // 'single' | 'split'
    let splitRows = [
        { method: 'cash', amount: 0, reference: '' }
    ];
    let searchTimeout = null;
    const paymentMethodsList = @json($paymentMethods);

    // Audio synthesizer for barcode scan feedback (no audio files needed!)
    function playBeep(type = 'success') {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'success') {
                osc.frequency.setValueAtTime(1400, ctx.currentTime);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.1);
            } else {
                osc.frequency.setValueAtTime(300, ctx.currentTime);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.25);
            }
        } catch (e) {
            // AudioContext not allowed before user gesture or unsupported
        }
    }

    // Initialize Held Bills counter on load
    document.addEventListener('DOMContentLoaded', () => {
        refreshHeldBillsCount();
        const barcodeInput = document.getElementById('barcodeInput');
        if (barcodeInput) barcodeInput.focus();
    });

    // ================= KEYBOARD SHORTCUTS HANDLER =================
    window.addEventListener('keydown', function(e) {
        // F1: Help / Shortcuts
        if (e.key === 'F1') {
            e.preventDefault();
            openShortcutsModal();
            return;
        }
        // F2: Focus Barcode
        if (e.key === 'F2') {
            e.preventDefault();
            const el = document.getElementById('barcodeInput');
            if (el) { el.focus(); el.select(); }
            return;
        }
        // F3: Focus Medicine Search
        if (e.key === 'F3') {
            e.preventDefault();
            const el = document.getElementById('medicineSearchInput');
            if (el) { el.focus(); el.select(); }
            return;
        }
        // F4: Quick Customer
        if (e.key === 'F4') {
            e.preventDefault();
            openQuickCustomerModal();
            return;
        }
        // F6: Held Bills
        if (e.key === 'F6') {
            e.preventDefault();
            openHeldBillsModal();
            return;
        }
        // F8: Hold Bill
        if (e.key === 'F8') {
            e.preventDefault();
            holdCurrentBill();
            return;
        }
        // F9: Toggle Split Payment
        if (e.key === 'F9') {
            e.preventDefault();
            setTenderMode(tenderMode === 'single' ? 'split' : 'single');
            return;
        }
        // F10: Checkout
        if (e.key === 'F10') {
            e.preventDefault();
            document.getElementById('btnCheckout').click();
            return;
        }
        // Esc: Close Modals
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    function closeAllModals() {
        closeQuickCustomerModal();
        closeHeldBillsModal();
        closeHoldConfirmModal();
        closeShortcutsModal();
    }

    // ================= BARCODE SCANNER FAST INPUT =================
    const barcodeInput = document.getElementById('barcodeInput');
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = this.value.trim();
            if (!code) return;
            handleBarcodeScan(code);
        }
    });

    function handleBarcodeScan(code) {
        fetch(`{{ route('store.pos.barcode') }}?code=${encodeURIComponent(code)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playBeep('success');
                    addToCart(data.medicine, data.batch);
                    showScanAlert(`Added [${data.medicine.name}] Batch #${data.batch.batch_number}`, 'success');
                    barcodeInput.value = '';
                    barcodeInput.focus();
                } else {
                    playBeep('error');
                    showScanAlert(data.message || 'Item not found or unavailable.', 'error');
                    barcodeInput.select();
                }
            })
            .catch(err => {
                playBeep('error');
                showScanAlert('Network error during barcode lookup.', 'error');
            });
    }

    function showScanAlert(text, type = 'success') {
        const alertBox = document.getElementById('scanAlert');
        const alertText = document.getElementById('scanAlertText');

        alertText.innerText = text;
        alertBox.className = type === 'success' 
            ? 'p-2 rounded-xl text-xs font-bold transition flex items-center justify-between bg-emerald-50 text-emerald-800 border border-emerald-200'
            : 'p-2 rounded-xl text-xs font-bold transition flex items-center justify-between bg-rose-50 text-rose-800 border border-rose-200';
        alertBox.classList.remove('hidden');

        setTimeout(() => {
            alertBox.classList.add('hidden');
        }, 4000);
    }

    function dismissScanAlert() {
        document.getElementById('scanAlert').classList.add('hidden');
    }

    // ================= MEDICINE MANUAL SEARCH =================
    document.getElementById('medicineSearchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        if (query.length < 1) {
            document.getElementById('resultsList').classList.add('hidden');
            document.getElementById('searchPlaceholder').classList.remove('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetchMedicines(query);
        }, 200);
    });

    function fetchMedicines(query) {
        fetch(`{{ route('store.pos.search-medicines') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                renderSearchResults(data);
            });
    }

    function renderSearchResults(medicines) {
        const placeholder = document.getElementById('searchPlaceholder');
        const list = document.getElementById('resultsList');

        if (medicines.length === 0) {
            placeholder.classList.remove('hidden');
            placeholder.innerHTML = '<p class="text-sm font-semibold text-slate-400">No active medicines with available stock found.</p>';
            list.classList.add('hidden');
            return;
        }

        placeholder.classList.add('hidden');
        list.classList.remove('hidden');
        list.innerHTML = '';

        medicines.forEach(med => {
            const card = document.createElement('div');
            card.className = 'p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-[#4b55c8]/40 transition space-y-2';

            let batchesHtml = '';
            med.batches.forEach(b => {
                const expiryClass = b.is_expired ? 'text-rose-600 bg-rose-50 border-rose-200' : (b.is_expiring_soon ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-slate-600 bg-slate-100');

                batchesHtml += `
                    <div class="flex items-center justify-between text-xs bg-white p-2 rounded-lg border border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-slate-800">${b.batch_number}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border ${expiryClass}">Exp: ${b.expiry_label}</span>
                            <span class="text-slate-400">Stock: <b class="text-slate-700">${b.quantity}</b></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-black font-mono text-slate-900">₹${b.selling_price.toFixed(2)}</span>
                            <button type="button" onclick='addToCart(${JSON.stringify(med)}, ${JSON.stringify(b)})' class="px-3 py-1 bg-[#4b55c8] hover:bg-[#3f49b8] text-white font-bold rounded-md transition text-xs shadow-2xs">
                                + Add
                            </button>
                        </div>
                    </div>
                `;
            });

            card.innerHTML = `
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm">${med.name}</span>
                            ${med.prescription_required ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-rose-100 text-rose-700 border border-rose-200">Rx Required</span>' : ''}
                        </div>
                        <p class="text-xs text-slate-400">${med.generic_name || ''} ${med.dosage_form ? '• ' + med.dosage_form : ''}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-slate-600">Total: ${med.total_stock}</span>
                        ${med.gst_rate ? `<span class="text-[10px] text-slate-400 block font-mono">GST: ${med.gst_rate}%</span>` : ''}
                    </div>
                </div>
                <div class="space-y-1.5 pt-1">
                    ${batchesHtml}
                </div>
            `;
            list.appendChild(card);
        });
    }

    // ================= CART LOGIC & ITEM-LEVEL DISCOUNTS =================
    function addToCart(med, batch) {
        const existing = cart.find(item => item.batch_id === batch.id);
        if (existing) {
            if (existing.quantity < batch.quantity) {
                existing.quantity += 1;
            } else {
                alert(`Cannot add more than available stock (${batch.quantity}).`);
            }
        } else {
            cart.push({
                medicine_id: med.id,
                medicine_name: med.name,
                batch_id: batch.id,
                batch_number: batch.batch_number,
                available_stock: batch.quantity,
                unit_price: batch.selling_price,
                mrp: batch.mrp,
                quantity: 1,
                discount: 0.00,
                gst_rate: med.gst_rate || 0,
                prescription_required: med.prescription_required
            });
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItemsList');
        const emptyMsg = document.getElementById('emptyCartMessage');
        const rxBanner = document.getElementById('rxBanner');

        if (cart.length === 0) {
            container.innerHTML = '';
            emptyMsg.classList.remove('hidden');
            rxBanner.classList.add('hidden');
            calculateTotals();
            return;
        }

        emptyMsg.classList.add('hidden');
        container.innerHTML = '';

        let hasRx = false;

        cart.forEach((item, index) => {
            if (item.prescription_required) hasRx = true;

            const lineSubtotal = Math.max(0, (item.unit_price * item.quantity) - item.discount);
            const lineTax = (lineSubtotal * (item.gst_rate / 100));
            const lineTotal = lineSubtotal + lineTax;

            const row = document.createElement('div');
            row.className = 'p-2.5 rounded-xl border border-slate-200 bg-white flex flex-col gap-1.5 shadow-2xs';
            row.innerHTML = `
                <input type="hidden" name="items[${index}][medicine_id]" value="${item.medicine_id}">
                <input type="hidden" name="items[${index}][batch_id]" value="${item.batch_id}">
                <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}">
                <input type="hidden" name="items[${index}][gst_rate]" value="${item.gst_rate}">
                <input type="hidden" name="items[${index}][discount]" value="${item.discount}">

                <!-- Top line: Name & Remove -->
                <div class="flex items-center justify-between gap-2">
                    <div class="overflow-hidden flex-1">
                        <span class="font-bold text-xs text-slate-800 truncate block">${item.medicine_name}</span>
                        <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400">
                            <span>Batch: <b class="text-slate-600">${item.batch_number}</b></span>
                            <span>Rate: ₹${item.unit_price.toFixed(2)}</span>
                            ${item.gst_rate ? `<span class="text-indigo-600 font-semibold">GST ${item.gst_rate}%</span>` : ''}
                        </div>
                    </div>
                    <button type="button" onclick="removeFromCart(${index})" class="text-rose-400 hover:text-rose-700 p-1 text-sm font-bold leading-none">✕</button>
                </div>

                <!-- Bottom line: Quantity, Line Discount, Line Total -->
                <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-50">
                    <!-- Quantity Stepper -->
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="updateQty(${index}, -1)" class="w-6 h-6 rounded bg-slate-100 font-bold text-slate-600 flex items-center justify-center hover:bg-slate-200 text-xs">-</button>
                        <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="1" max="${item.available_stock}" onchange="setQty(${index}, this.value)" class="w-12 text-center text-xs font-black font-mono rounded border border-slate-200 py-0.5 outline-none">
                        <button type="button" onclick="updateQty(${index}, 1)" class="w-6 h-6 rounded bg-slate-100 font-bold text-slate-600 flex items-center justify-center hover:bg-slate-200 text-xs">+</button>
                    </div>

                    <!-- Item Discount -->
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] text-slate-400">Disc: ₹</span>
                        <input type="number" step="0.01" min="0" value="${item.discount}" onchange="setItemDiscount(${index}, this.value)" class="w-14 text-right text-xs font-mono rounded border border-slate-200 px-1 py-0.5 outline-none font-semibold">
                    </div>

                    <!-- Line Total -->
                    <div class="text-right">
                        <span class="font-black font-mono text-xs text-slate-900">₹${lineTotal.toFixed(2)}</span>
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        if (hasRx) rxBanner.classList.remove('hidden');
        else rxBanner.classList.add('hidden');

        calculateTotals();
    }

    function updateQty(index, delta) {
        const item = cart[index];
        const newQty = item.quantity + delta;
        if (newQty > item.available_stock) {
            alert(`Maximum available stock in this batch is ${item.available_stock}.`);
            return;
        }
        if (newQty <= 0) {
            removeFromCart(index);
        } else {
            item.quantity = newQty;
            renderCart();
        }
    }

    function setQty(index, val) {
        const item = cart[index];
        const qty = parseInt(val) || 1;
        if (qty > item.available_stock) {
            alert(`Maximum available stock is ${item.available_stock}.`);
            item.quantity = item.available_stock;
        } else {
            item.quantity = Math.max(1, qty);
        }
        renderCart();
    }

    function setItemDiscount(index, val) {
        const item = cart[index];
        item.discount = Math.max(0, parseFloat(val) || 0);
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalTax = 0;

        cart.forEach(item => {
            const lineSub = Math.max(0, (item.unit_price * item.quantity) - item.discount);
            const lineTax = lineSub * (item.gst_rate / 100);
            subtotal += lineSub;
            totalTax += lineTax;
        });

        const overallDiscountInput = document.getElementById('cartDiscount');
        const overallDiscount = parseFloat(overallDiscountInput.value) || 0;
        const grandTotal = Math.max(0, (subtotal - overallDiscount) + totalTax);

        document.getElementById('subtotalDisplay').innerText = `₹${subtotal.toFixed(2)}`;
        document.getElementById('taxDisplay').innerText = `₹${totalTax.toFixed(2)}`;
        document.getElementById('grandTotalDisplay').innerText = `₹${grandTotal.toFixed(2)}`;

        // Sync payment amount defaults
        if (tenderMode === 'single') {
            const paidInput = document.getElementById('paidAmountInput');
            if (paidInput.value === '0.00' || paidInput.dataset.auto === 'true') {
                paidInput.value = grandTotal.toFixed(2);
                paidInput.dataset.auto = 'true';
            }
        } else {
            syncSplitTotals();
        }

        updateChangeDisplay();
    }

    function updateChangeDisplay() {
        const grandTotalText = document.getElementById('grandTotalDisplay').innerText.replace('₹', '');
        const grandTotal = parseFloat(grandTotalText) || 0;
        
        let tendered = 0;
        if (tenderMode === 'single') {
            tendered = parseFloat(document.getElementById('paidAmountInput').value) || 0;
        } else {
            tendered = splitRows.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
        }

        const changeRow = document.getElementById('changeDueRow');
        const changeDisplay = document.getElementById('changeDisplay');

        if (tendered > grandTotal) {
            const change = tendered - grandTotal;
            changeDisplay.innerText = `₹${change.toFixed(2)}`;
            changeRow.classList.remove('hidden');
        } else {
            changeRow.classList.add('hidden');
        }
    }

    // ================= TENDER MODE (SINGLE vs SPLIT) =================
    function setTenderMode(mode) {
        tenderMode = mode;
        const btnSingle = document.getElementById('btnSinglePayment');
        const btnSplit = document.getElementById('btnSplitPayment');
        const viewSingle = document.getElementById('singlePaymentView');
        const viewSplit = document.getElementById('splitPaymentView');

        if (mode === 'split') {
            btnSplit.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-white border border-slate-300 text-slate-800 shadow-2xs';
            btnSingle.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-lg text-slate-500 hover:text-slate-800';
            viewSingle.classList.add('hidden');
            viewSplit.classList.remove('hidden');

            const grandTotalText = document.getElementById('grandTotalDisplay').innerText.replace('₹', '');
            const grandTotal = parseFloat(grandTotalText) || 0;
            if (splitRows.length === 1 && splitRows[0].amount === 0) {
                splitRows[0].amount = grandTotal;
            }
            renderSplitRows();
        } else {
            btnSingle.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-white border border-slate-300 text-slate-800 shadow-2xs';
            btnSplit.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-lg text-slate-500 hover:text-slate-800';
            viewSplit.classList.add('hidden');
            viewSingle.classList.remove('hidden');
            calculateTotals();
        }
    }

    function addSplitRow() {
        const grandTotalText = document.getElementById('grandTotalDisplay').innerText.replace('₹', '');
        const grandTotal = parseFloat(grandTotalText) || 0;
        const currentTotal = splitRows.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
        const remainder = Math.max(0, grandTotal - currentTotal);

        splitRows.push({
            method: 'upi',
            amount: remainder,
            reference: ''
        });
        renderSplitRows();
    }

    function removeSplitRow(index) {
        if (splitRows.length <= 1) return;
        splitRows.splice(index, 1);
        renderSplitRows();
    }

    function renderSplitRows() {
        const container = document.getElementById('splitRowsContainer');
        container.innerHTML = '';

        splitRows.forEach((row, i) => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'flex items-center gap-1.5 bg-white p-1.5 rounded-xl border border-slate-200';

            let optionsHtml = '';
            paymentMethodsList.forEach(m => {
                optionsHtml += `<option value="${m.value}" ${m.value === row.method ? 'selected' : ''}>${m.label}</option>`;
            });

            rowDiv.innerHTML = `
                <select name="payments[${i}][method]" onchange="splitRows[${i}].method = this.value" class="text-xs font-bold rounded-lg border border-slate-200 p-1.5 outline-none w-28">
                    ${optionsHtml}
                </select>
                <input type="number" step="0.01" min="0" name="payments[${i}][amount]" value="${row.amount}" oninput="splitRows[${i}].amount = parseFloat(this.value) || 0; syncSplitTotals()" class="w-24 text-right text-xs font-mono font-black rounded-lg border border-slate-200 p-1.5 outline-none" placeholder="Amount">
                <input type="text" name="payments[${i}][reference]" value="${row.reference}" oninput="splitRows[${i}].reference = this.value" placeholder="Txn Ref" class="flex-1 text-xs rounded-lg border border-slate-200 p-1.5 outline-none">
                ${splitRows.length > 1 ? `<button type="button" onclick="removeSplitRow(${i})" class="text-rose-500 font-bold p-1 hover:text-rose-700">✕</button>` : ''}
            `;
            container.appendChild(rowDiv);
        });

        syncSplitTotals();
    }

    function syncSplitTotals() {
        const total = splitRows.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
        document.getElementById('splitTotalDisplay').innerText = `₹${total.toFixed(2)}`;

        // Sync with primary paid_amount field so standard form submission works
        document.getElementById('paidAmountInput').value = total.toFixed(2);
        updateChangeDisplay();
    }

    // ================= HOLD & RESUME BILLS =================
    function holdCurrentBill() {
        if (cart.length === 0) {
            alert('Cannot hold an empty bill. Add at least one medicine.');
            return;
        }
        document.getElementById('holdConfirmModal').classList.remove('hidden');
        document.getElementById('holdConfirmModal').classList.add('flex');
        document.getElementById('holdRefInputCustom').focus();
    }

    function closeHoldConfirmModal() {
        document.getElementById('holdConfirmModal').classList.add('hidden');
        document.getElementById('holdConfirmModal').classList.remove('flex');
    }

    function confirmHoldBill() {
        const ref = document.getElementById('holdRefInputCustom').value.trim();
        const form = document.getElementById('checkoutForm');
        document.getElementById('holdRefInput').value = ref;

        const formData = new FormData(form);
        formData.append('status', 'draft');

        fetch('{{ route('store.pos.hold') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeHoldConfirmModal();
                showScanAlert(data.message, 'success');
                cart = [];
                renderCart();
                refreshHeldBillsCount();
                barcodeInput.focus();
            } else {
                alert(data.message || 'Could not hold bill.');
            }
        })
        .catch(err => {
            alert('Error communicating with server while holding bill.');
        });
    }

    function refreshHeldBillsCount() {
        fetch('{{ route('store.pos.held-bills') }}')
            .then(r => r.json())
            .then(bills => {
                const badge = document.getElementById('heldBillsCountBadge');
                if (bills && bills.length > 0) {
                    badge.innerText = bills.length;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
    }

    function openHeldBillsModal() {
        const modal = document.getElementById('heldBillsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        fetch('{{ route('store.pos.held-bills') }}')
            .then(r => r.json())
            .then(bills => {
                const list = document.getElementById('heldBillsList');
                if (!bills || bills.length === 0) {
                    list.innerHTML = `
                        <div class="h-48 flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-bold text-slate-600">No held bills right now</p>
                            <p class="text-[11px]">Bills saved with "Hold (F8)" will appear here</p>
                        </div>
                    `;
                    return;
                }

                list.innerHTML = '';
                bills.forEach(b => {
                    const card = document.createElement('div');
                    card.className = 'p-3 rounded-2xl border border-slate-200 hover:border-amber-400 bg-white transition flex items-center justify-between gap-3 shadow-2xs';
                    card.innerHTML = `
                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-200">
                                    ${b.hold_reference || 'HELD'}
                                </span>
                                <span class="font-mono text-xs text-slate-400 font-bold">${b.invoice_number}</span>
                                <span class="text-[11px] text-slate-400">• ${b.held_at}</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800">
                                <span>Customer: ${b.customer_name}</span>
                                <span class="text-slate-400 font-normal">(${b.items_count} medicines)</span>
                            </div>
                            <div class="text-sm font-black font-mono text-[#4b55c8]">
                                ₹${b.grand_total.toFixed(2)}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="resumeHeldBill(${b.id})" class="px-3.5 py-1.5 rounded-xl bg-[#4b55c8] hover:bg-[#3a44b5] text-white font-bold text-xs shadow-xs transition">
                                Resume Bill
                            </button>
                            <button type="button" onclick="discardHeldBill(${b.id}, '${b.hold_reference}')" class="px-2.5 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition">
                                Discard
                            </button>
                        </div>
                    `;
                    list.appendChild(card);
                });
            });
    }

    function closeHeldBillsModal() {
        const modal = document.getElementById('heldBillsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function resumeHeldBill(saleId) {
        if (cart.length > 0) {
            if (!confirm('Active cart contains items. Resuming this held bill will replace current items. Continue?')) {
                return;
            }
        }

        fetch(`{{ url('store/pos/resume') }}/${saleId}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const sale = res.sale;
                    cart = sale.items.map(item => ({
                        medicine_id: item.medicine_id,
                        medicine_name: item.medicine_name,
                        batch_id: item.batch_id,
                        batch_number: item.batch_number,
                        available_stock: item.current_stock,
                        unit_price: item.unit_price,
                        mrp: item.mrp,
                        quantity: item.quantity,
                        discount: item.discount,
                        gst_rate: item.gst_rate,
                        prescription_required: false
                    }));

                    // Populate customer
                    if (sale.customer_id) {
                        document.getElementById('customerSelect').value = sale.customer_id;
                    }
                    document.getElementById('customerNameInput').value = sale.customer_name || 'Walk-in Customer';
                    document.getElementById('customerPhoneInput').value = sale.customer_phone || '';
                    document.getElementById('cartDiscount').value = sale.discount.toFixed(2);

                    closeHeldBillsModal();
                    renderCart();
                    refreshHeldBillsCount();
                    showScanAlert(`Resumed bill [${sale.hold_reference || sale.invoice_number}]`, 'success');

                    if (res.warnings && res.warnings.length > 0) {
                        alert("Stock Notice:\n" + res.warnings.join("\n"));
                    }
                } else {
                    alert(res.message || 'Error resuming bill.');
                }
            });
    }

    function discardHeldBill(saleId, ref) {
        if (!confirm(`Are you sure you want to discard held bill #${ref}?`)) return;

        fetch(`{{ url('store/pos/held') }}/${saleId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                openHeldBillsModal();
                refreshHeldBillsCount();
                showScanAlert('Held bill discarded.', 'success');
            } else {
                alert(res.message || 'Error discarding bill.');
            }
        });
    }

    // ================= CUSTOMER SELECTION & MODAL =================
    document.getElementById('customerSelect').addEventListener('change', function(e) {
        const selected = e.target.options[e.target.selectedIndex];
        document.getElementById('customerNameInput').value = selected.dataset.name || 'Walk-in Customer';
        document.getElementById('customerPhoneInput').value = selected.dataset.phone || '';

        const badge = document.getElementById('selectedCustomerBadge');
        const codeSpan = document.getElementById('selectedCustCode');
        if (selected.dataset.code) {
            codeSpan.innerText = selected.dataset.code;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });

    function openQuickCustomerModal() {
        document.getElementById('quickCustomerModal').classList.remove('hidden');
        document.getElementById('quickCustomerModal').classList.add('flex');
        document.getElementById('qcName').focus();
    }

    function closeQuickCustomerModal() {
        document.getElementById('quickCustomerModal').classList.add('hidden');
        document.getElementById('quickCustomerModal').classList.remove('flex');
    }

    function submitQuickCustomer() {
        const name = document.getElementById('qcName').value.trim();
        const phone = document.getElementById('qcPhone').value.trim();
        const email = document.getElementById('qcEmail').value.trim();

        if (!name) {
            alert('Customer name is required.');
            return;
        }

        fetch('{{ route('store.pos.quick-customer') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, phone, email })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const sel = document.getElementById('customerSelect');
                const opt = document.createElement('option');
                opt.value = res.customer.id;
                opt.dataset.name = res.customer.name;
                opt.dataset.phone = res.customer.phone || '';
                opt.dataset.code = res.customer.code || '';
                opt.innerText = `${res.customer.name} [${res.customer.code}] (${res.customer.phone || 'No phone'})`;
                opt.selected = true;
                sel.appendChild(opt);

                document.getElementById('customerNameInput').value = res.customer.name;
                document.getElementById('customerPhoneInput').value = res.customer.phone || '';

                const badge = document.getElementById('selectedCustomerBadge');
                document.getElementById('selectedCustCode').innerText = res.customer.code || '';
                badge.classList.remove('hidden');

                closeQuickCustomerModal();
                showScanAlert(`Customer [${res.customer.name}] added!`, 'success');
                barcodeInput.focus();
            } else {
                alert('Error creating customer.');
            }
        });
    }

    // ================= SHORTCUTS MODAL =================
    function openShortcutsModal() {
        document.getElementById('shortcutsModal').classList.remove('hidden');
        document.getElementById('shortcutsModal').classList.add('flex');
    }

    function closeShortcutsModal() {
        document.getElementById('shortcutsModal').classList.add('hidden');
        document.getElementById('shortcutsModal').classList.remove('flex');
    }
</script>
@endsection
