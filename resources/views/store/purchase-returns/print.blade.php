<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debit Note #{{ $purchaseReturn->return_number }} - {{ $store->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; }
        body { background: #f8fafc; color: #0f172a; padding: 24px; font-size: 12px; }
        .page-container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 36px; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .no-print { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; max-width: 900px; margin-left: auto; margin-right: auto; }
        .btn-print { background: #4b55c8; color: #ffffff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; font-size: 13px; }
        .btn-back { background: #ffffff; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; border: 1px solid #cbd5e1; font-size: 13px; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 16px; }
        .store-title { font-size: 20px; font-weight: 800; color: #0f172a; text-transform: uppercase; }
        .store-sub { font-size: 11px; color: #475569; line-height: 1.4; margin-top: 2px; }
        .doc-title { font-size: 18px; font-weight: 900; text-align: right; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; }

        .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .parties-table td { width: 50%; vertical-align: top; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .party-heading { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .party-name { font-size: 14px; font-weight: 700; color: #0f172a; }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 16px; margin-bottom: 20px; }
        .items-table th { background: #0f172a; color: #ffffff; padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; color: #1e293b; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace, Courier, sans-serif; }
        .font-bold { font-weight: bold; }

        .summary-wrapper { display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; }
        .reason-box { width: 55%; font-size: 11px; color: #334155; line-height: 1.5; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
        .summary-box { width: 40%; border: 1px solid #0f172a; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .summary-total { background: #0f172a; color: #ffffff; font-weight: 800; font-size: 13px; }

        .sign-area { margin-top: 80px; display: flex; justify-content: space-between; padding: 0 20px; }
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
        <a href="{{ route('store.purchase-returns.show', $purchaseReturn->id) }}" class="btn-back">
            ← Back to Return Details
        </a>
        <button onclick="window.print()" class="btn-print">
            🖨 Print Debit Note
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
                    <div class="doc-title">DEBIT NOTE</div>
                    <div class="store-sub" style="text-align: right; margin-top: 6px;">
                        <strong>Debit Note #:</strong> <span class="font-mono font-bold">{{ $purchaseReturn->return_number }}</span><br>
                        <strong>Date:</strong> {{ $purchaseReturn->return_date->format('d M Y') }}<br>
                        @if($purchaseReturn->purchase)
                            <strong>Ref Purchase Bill:</strong> <span class="font-mono font-bold">{{ $purchaseReturn->purchase->invoice_number }}</span><br>
                            <strong>Bill Date:</strong> {{ $purchaseReturn->purchase->purchase_date->format('d M Y') }}<br>
                        @endif
                        <strong>Status:</strong> {{ strtoupper($purchaseReturn->status->value) }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Supplier & Bill To Details -->
        <table class="parties-table">
            <tr>
                <td>
                    <div class="party-heading">Vendor / Creditor (Issued To)</div>
                    <div class="party-name">{{ $purchaseReturn->supplier?->name }}</div>
                    <div class="store-sub">
                        @if($purchaseReturn->supplier?->company_name) {{ $purchaseReturn->supplier->company_name }}<br> @endif
                        <strong>Phone:</strong> {{ $purchaseReturn->supplier?->phone }}<br>
                        @if($purchaseReturn->supplier?->address) {{ $purchaseReturn->supplier->address }}, {{ $purchaseReturn->supplier->city }}<br> @endif
                        @if($purchaseReturn->supplier?->gst_number) <strong>GSTIN:</strong> {{ $purchaseReturn->supplier->gst_number }}<br> @endif
                    </div>
                </td>
                <td>
                    <div class="party-heading">Debit Note Accounting Reference</div>
                    <div class="store-sub">
                        <strong>Reason for Return:</strong> {{ $purchaseReturn->reason }}<br>
                        <strong>Adjustment Type:</strong>
                        @if($purchaseReturn->refund_amount > 0 && $purchaseReturn->adjustment_amount > 0)
                            Split (On-Account + Direct Refund)
                        @elseif($purchaseReturn->refund_amount > 0)
                            Direct Refund ({{ ucfirst($purchaseReturn->refund_method ?? 'cash') }})
                        @else
                            On-Account / Supplier Credit
                        @endif
                        <br>
                        <strong>Origin Purchase Amount:</strong> ₹{{ number_format($purchaseReturn->purchase?->grand_total ?? 0, 2) }}<br>
                        <strong>Generated By:</strong> {{ $purchaseReturn->creator?->name ?? 'Store User' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%;" class="text-center">#</th>
                    <th style="width: 38%;">Medicine Particulars</th>
                    <th style="width: 14%;">Batch No</th>
                    <th style="width: 10%;" class="text-center">Expiry</th>
                    <th style="width: 10%;" class="text-center">Qty Ret.</th>
                    <th style="width: 10%;" class="text-right">Unit Rate (₹)</th>
                    <th style="width: 12%;" class="text-right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseReturn->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $item->medicine?->displayName() }}</div>
                        @if($item->reason)
                            <div style="font-size: 10px; color: #64748b;">Reason: {{ $item->reason }}</div>
                        @endif
                    </td>
                    <td class="font-mono font-bold">{{ $item->batch?->batch_number ?? 'N/A' }}</td>
                    <td class="text-center font-mono">{{ $item->batch?->expiry_date?->format('m/Y') ?? '—' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item->quantity }}</td>
                    <td class="text-right font-mono">{{ number_format($item->purchase_price, 2) }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary & Remarks -->
        <div class="summary-wrapper">
            <div class="reason-box">
                <strong>Declaration / Remarks:</strong><br>
                This Debit Note is issued against returned stock due to damaged / expired / defective pharmaceutical merchandise.
                Kindly credit our account or adjust against current outstanding payables accordingly.
                @if($purchaseReturn->notes)
                    <div style="margin-top: 6px;"><strong>Notes:</strong> {{ $purchaseReturn->notes }}</div>
                @endif
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <span>Gross Return Value:</span>
                    <span class="font-mono font-bold">₹{{ number_format($purchaseReturn->grand_total, 2) }}</span>
                </div>
                @if($purchaseReturn->adjustment_amount > 0)
                <div class="summary-row">
                    <span>Adjusted vs Bill Dues:</span>
                    <span class="font-mono">₹{{ number_format($purchaseReturn->adjustment_amount, 2) }}</span>
                </div>
                @endif
                @if($purchaseReturn->refund_amount > 0)
                <div class="summary-row">
                    <span>Cash/Bank Refund:</span>
                    <span class="font-mono">₹{{ number_format($purchaseReturn->refund_amount, 2) }}</span>
                </div>
                @endif
                <div class="summary-row summary-total">
                    <span>Total Debit Note:</span>
                    <span class="font-mono">₹{{ number_format($purchaseReturn->grand_total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="sign-area">
            <div class="sign-box">
                Authorized Signatory<br>
                <strong>{{ $store->name }}</strong>
            </div>
            <div class="sign-box">
                Receiver's Signature / Stamp<br>
                <strong>{{ $purchaseReturn->supplier?->name }}</strong>
            </div>
        </div>

    </div>

</body>
</html>
