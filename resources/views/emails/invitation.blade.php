<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Invitation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f5f7;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }
        .email-wrapper {
            width: 100%;
            margin: 0;
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
        .email-header {
            padding-bottom: 20px;
            border-bottom: 1px solid #e8ebf0;
            margin-bottom: 20px;
        }
        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: #1a56db;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        .button-container {
            margin-top: 25px;
            margin-bottom: 25px;
            text-align: center;
        }
        .button {
            background-color: #1a56db;
            border-radius: 5px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            line-height: 45px;
            text-align: center;
            text-decoration: none;
            width: 220px;
            -webkit-text-size-adjust: none;
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
                <!-- Header -->
                <tr>
                    <td class="email-header">
                        <span class="logo-text">Enterprise B2B SaaS</span>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td>
                        <h1>Hello,</h1>
                        <p>You have been formally invited to join the corporate workspace <strong>{{ $workspaceName }}</strong> as a team collaborator.</p>
                        <p>To accept this invitation, set up your secure sub-account profile, and access your operational panel, please click the verification button below:</p>

                        <div class="button-container">
                            <a href="{{ $actionUrl }}" class="button" target="_blank">Setup Your Account</a>
                        </div>

                        <p>If you did not expect this request, no further action is required. This link will expire automatically.</p>
                        <p>Best regards,<br>The SaaS Infrastructure Team</p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer">
                        This is an automated operational notification bound to the protected client network space. Please do not reply directly to this mail.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
