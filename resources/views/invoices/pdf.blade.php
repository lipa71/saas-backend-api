<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Core font required by DomPDF for European characters */
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 10px;
        }
        .header-table, .details-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1a56db;
            text-transform: uppercase;
        }
        .company-details {
            text-align: right;
            font-size: 11px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .details-table td {
            width: 50%;
            vertical-align: top;
            font-size: 11px;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px;
            border: 1px solid #e5e7eb;
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 40%;
            margin-left: 60%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .totals-table .grand-total {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #1a56db;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td>
                <div class="title">Commercial Invoice</div>
                <div style="margin-top: 5px;">
                    <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Issue Date:</strong> {{ $invoice->created_at->format('Y-m-d') }}<br>
                    <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}
                </div>
            </td>
            <td class="company-details">
                <strong>Seller:</strong><br>
                {{ tenant('name') }}<br>
                Email: {{ tenant('email') }}<br>
                Workspace ID: {{ tenant('id') }}
            </td>
        </tr>
    </table>

    <!-- Contractor Block -->
    <table class="details-table">
        <tr>
            <td>
                <div class="section-title">Billing Details (Buyer)</div>
                <strong>{{ $invoice->customer_name }}</strong><br>
                <strong>VAT ID:</strong> {{ $invoice->customer_vat_number }}
            </td>
            <td>
                <div class="section-title">Payment Overview</div>
                <strong>Status:</strong> {{ strtoupper($invoice->status) }}<br>
                <strong>Currency:</strong> EUR (€)
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th>No</th>
            <th>Description</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-center">VAT</th>
            <th class="text-right">Net Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_net_price, 2) }} €</td>
                <td class="text-center">{{ (float)$item->vat_rate }}%</td>
                <td class="text-right">{{ number_format($item->net_amount, 2) }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Financial Totals Accumulation Block -->
    <table class="totals-table">
        <tr>
            <td><strong>Subtotal Net</strong></td>
            <td class="text-right">{{ number_format($invoice->net_amount, 2) }} €</td>
        </tr>
        <tr>
            <td><strong>Total VAT</strong></td>
            <td class="text-right">{{ number_format($invoice->vat_amount, 2) }} €</td>
        </tr>
        <tr class="grand-total">
            <td><strong>Total Gross</strong></td>
            <td class="text-right">{{ number_format($invoice->gross_amount, 2) }} €</td>
        </tr>
    </table>

    <!-- Structural Reverse Charge Accounting Footnote Notice -->
    @if($invoice->items->where('vat_rate_id', 3)->count() > 0)
        <div style="margin-top: 20px; font-size: 10px; color: #4b5563; font-style: italic;">
            * Reverse charge: Customer is liable for the calculation and accounting of VAT.
        </div>
    @endif

    <div class="footer">
        Generated dynamically within protected tenant space. B2B SaaS Enterprise Infrastructure Platform.
    </div>
</div>
</body>
</html>
