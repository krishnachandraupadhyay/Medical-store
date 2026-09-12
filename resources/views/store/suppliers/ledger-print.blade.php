<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger Statement - {{ $supplier->name }} - {{ $store->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; }
        body { background: #f8fafc; color: #0f172a; padding: 24px; font-size: 12px; }
        .page-container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 36px; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .no-print { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; max-width: 900px; margin-left: auto; margin-right: auto; }
        .btn-print { background: #4b55c8; color: #ffffff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; font-size: 13px; }
        .btn-back { background: #ffffff; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; border: 1px solid #cbd5e1; font-size: 13px; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; border-bottom: 2px solid #0f172a; padding-bottom: 16px; }
        .store-title { font-size: 20px; font-weight: 800; color: #0f172a; text-transform: uppercase; }
        .store-sub { font-size: 11px; color: #475569; line-height: 1.4; margin-top: 2px; }
        .statement-title { font-size: 16px; font-weight: 800; text-align: right; color: #4b55c8; text-transform: uppercase; letter-spacing: 0.5px; }

        .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .parties-table td { width: 50%; vertical-align: top; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .party-heading { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .party-name { font-size: 14px; font-weight: 700; color: #0f172a; }

        .ledger-table { width: 100%; border-collapse: collapse; margin-top: 16px; margin-bottom: 20px; }
        .ledger-table th { background: #0f172a; color: #ffffff; padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .ledger-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; color: #1e293b; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace, Courier, sans-serif; }
        .font-bold { font-weight: bold; }

        .summary-box { float: right; width: 320px; border: 1px solid #0f172a; margin-top: 10px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .summary-total { background: #0f172a; color: #ffffff; font-weight: 800; font-size: 13px; }

        .sign-area { margin-top: 100px; display: flex; justify-content: space-between; padding: 0 20px; }
        .sign-box { width: 220px; border-top: 1px solid #475569; text-align: center; padding-top: 6px; font-size: 11px; font-weight: 600; color: #475569; }

        @media print {
            body { background: #ffffff; padding: 0; }
            .page-container { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="{{ route('store.suppliers.ledger', array_merge(['supplier' => $supplier], request()->query())) }}" class="btn-back">
            ← Back to Ledger
        </a>
        <button onclick="window.print()" class="btn-print">
            🖨 Print Statement
        </button>
    </div>

    <div class="page-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <div class="store-title">{{ $store->name }}</div>
                    <div class="store-sub">
                        {{ $store->address ?: 'Medical Store Premises' }}<br>
                        @if($store->city) {{ $store->city }}, {{ $store->state }} - {{ $store->pincode }}<br> @endif
                        Phone: {{ $store->phone ?: 'N/A' }} | Email: {{ $store->email ?: 'N/A' }}<br>
                        @if($store->gst_number) <strong>GSTIN:</strong> {{ $store->gst_number }} | @endif
                        @if($store->drug_license_no) <strong>DL No:</strong> {{ $store->drug_license_no }} @endif
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top;">
                    <div class="statement-title">Supplier Ledger Statement</div>
                    <div class="store-sub" style="text-align: right; margin-top: 6px;">
                        <strong>Date:</strong> {{ date('d M Y') }}<br>
                        <strong>Period:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Inception' }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Supplier Details -->
        <table class="parties-table">
            <tr>
                <td>
                    <div class="party-heading">Vendor / Creditor Details</div>
                    <div class="party-name">{{ $supplier->name }}</div>
                    <div class="store-sub">
                        @if($supplier->company_name) {{ $supplier->company_name }}<br> @endif
                        <strong>Code:</strong> {{ $supplier->supplier_code ?? 'SUP' }}<br>
                        <strong>Phone:</strong> {{ $supplier->phone }} @if($supplier->alternate_phone) / {{ $supplier->alternate_phone }} @endif<br>
                        @if($supplier->address) {{ $supplier->address }}, {{ $supplier->city }} {{ $supplier->state }} {{ $supplier->pincode }}<br> @endif
                        @if($supplier->gst_number) <strong>GSTIN:</strong> {{ $supplier->gst_number }} | @endif
                        @if($supplier->pan_number) <strong>PAN:</strong> {{ $supplier->pan_number }} @endif
                    </div>
                </td>
                <td>
                    <div class="party-heading">Account Terms</div>
                    <div class="store-sub">
                        <strong>Opening Balance:</strong> ₹{{ number_format($supplier->opening_balance ?? 0, 2) }} ({{ ucfirst($supplier->opening_balance_type ?? 'payable') }})<br>
                        <strong>Credit Limit:</strong> {{ $supplier->credit_limit ? '₹' . number_format($supplier->credit_limit, 2) : 'No Limit' }}<br>
                        <strong>Payment Terms:</strong> {{ $supplier->payment_terms ?: 'Standard' }}<br>
                        <strong>Account Status:</strong> {{ ucfirst($supplier->status) }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Ledger Entries Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 14%;">Ref / Bill #</th>
                    <th style="width: 38%;">Description / Particulars</th>
                    <th class="text-right" style="width: 12%;">Debit (₹)</th>
                    <th class="text-right" style="width: 12%;">Credit (₹)</th>
                    <th class="text-right" style="width: 12%;">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Opening Balance -->
                <tr style="background: #f1f5f9; font-style: italic;">
                    <td>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Initial' }}</td>
                    <td class="font-mono">B/F</td>
                    <td>Opening Balance Brought Forward</td>
                    <td class="text-right font-mono">—</td>
                    <td class="text-right font-mono">—</td>
                    <td class="text-right font-mono font-bold">
                        ₹{{ number_format(abs($periodOpeningBalance), 2) }} {{ $periodOpeningBalance > 0 ? 'Dr' : ($periodOpeningBalance < 0 ? 'Cr' : '') }}
                    </td>
                </tr>

                @forelse($ledgerEntries as $entry)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}</td>
                        <td class="font-mono font-bold">{{ $entry['reference'] }}</td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="text-right font-mono font-bold">
                            {{ $entry['debit'] > 0 ? '₹' . number_format($entry['debit'], 2) : '—' }}
                        </td>
                        <td class="text-right font-mono font-bold" style="color: #059669;">
                            {{ $entry['credit'] > 0 ? '₹' . number_format($entry['credit'], 2) : '—' }}
                        </td>
                        <td class="text-right font-mono font-bold">
                            ₹{{ number_format(abs($entry['balance']), 2) }} {{ $entry['balance'] > 0 ? 'Dr' : ($entry['balance'] < 0 ? 'Cr' : '') }}
                        </td>
                    </tr>
                @empty
                    @if($periodOpeningBalance == 0)
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">
                                No transactions recorded within this statement period.
                            </td>
                        </tr>
                    @endif
                @endforelse
            </tbody>
        </table>

        <!-- Summary Totals -->
        <div style="overflow: hidden; margin-top: 10px;">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Opening Balance</span>
                    <span class="font-mono">₹{{ number_format(abs($periodOpeningBalance), 2) }} {{ $periodOpeningBalance > 0 ? 'Dr' : ($periodOpeningBalance < 0 ? 'Cr' : '') }}</span>
                </div>
                <div class="summary-row">
                    <span>Total Purchases (Debit)</span>
                    <span class="font-mono font-bold">₹{{ number_format($totalDebits, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Total Payments (Credit)</span>
                    <span class="font-mono font-bold" style="color: #059669;">₹{{ number_format($totalCredits, 2) }}</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Net Closing Due</span>
                    <span class="font-mono">₹{{ number_format(abs($closingBalance), 2) }} {{ $closingBalance > 0 ? 'Dr' : ($closingBalance < 0 ? 'Cr (Adv)' : '') }}</span>
                </div>
            </div>
        </div>

        <div style="clear: both;"></div>

        <!-- Signature Section -->
        <div class="sign-area">
            <div class="sign-box">
                Vendor Verified By
            </div>
            <div class="sign-box">
                For {{ $store->name }}<br>(Authorized Signatory)
            </div>
        </div>
    </div>

</body>
</html>
