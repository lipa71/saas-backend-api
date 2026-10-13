<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Invoice Notification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f5f7;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            width: 100%;
            padding: 20px;
            background-color: #f4f5f7;
        }
        .email-content {
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e8ebf0;
            border-radius: 8px;
            padding: 40px;
        }
        h1 {
            font-size: 19px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 15px;
        }
        p {
            font-size: 14px;
            line-height: 1.5;
            color: #4b5563;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .financial-summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e8ebf0;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td>
                        <h1>Dear {{ $customerName }},</h1>
                        <p>We are writing to inform you that a new commercial invoice has been formally issued to your organization by <strong>{{ tenant('name') }}</strong>.</p>

                        <div class="financial-summary">
                            <strong>Invoice Reference:</strong> {{ $invoiceNumber }}<br>
                            <strong>Total Amount Due:</strong> {{ $grossAmount }} € (EUR)<br>
                            <strong>Currency Status:</strong> Clear / Settlement Pending
                        </div>

                        <p>The secure, official cryptographic digital print of your document has been compiled and attached directly to this electronic transmission as a corporate-grade PDF file.</p>
                        <p>Please review the attached invoice details for processing and timely accounting reconciliation.</p>

                        <p>Best regards,<br>The Billing & Finance Department</p>
                    </td>
                </tr>
                <tr>
                    <td class="footer">
                        This is an automated encrypted financial notification bound to the protected client network space.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
