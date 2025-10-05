<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #2563eb;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333333;
        }
        .message {
            font-size: 15px;
            color: #666666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .button-wrapper {
            text-align: center;
            margin: 35px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 15px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .info-value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #374151;
            word-break: break-all;
        }
        .expiry-notice {
            text-align: center;
            font-size: 13px;
            color: #dc2626;
            margin: 20px 0;
        }
        .security-notice {
            background-color: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            font-size: 14px;
            color: #92400e;
        }
        .alternative-link {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-top: 25px;
        }
        .alternative-link a {
            color: #2563eb;
            word-break: break-all;
        }
        .footer {
            background-color: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            font-size: 13px;
            color: #6b7280;
            margin: 8px 0;
        }
        .footer-link {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>Password Reset</h1>
        </div>

        <div class="content">
            <p class="greeting">Hello {{ $userName }},</p>

            <p class="message">
                We received a request to reset your password. Click the button below to create a new password for your ProperQuant account.
            </p>

            <div class="button-wrapper">
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>

            <div class="info-box">
                <div class="info-label">Reset Token</div>
                <div class="info-value">{{ $resetToken }}</div>
            </div>

            <p class="expiry-notice">
                This link expires at {{ $expiresAt->format('M d, Y h:i A') }}
            </p>

            <div class="security-notice">
                <strong>Security Notice:</strong> If you didn't request this password reset, please ignore this email. Your password will remain unchanged.
            </div>

            <p class="alternative-link">
                If the button doesn't work, copy this link:<br>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </p>
        </div>

        <div class="footer">
            <p class="footer-text"><strong>ProperQuant</strong></p>
            <p class="footer-text">Real Estate Investment Management</p>
            <p class="footer-text">
                <a href="https://properquant.net" class="footer-link">properquant.net</a> • 
                <a href="mailto:support@properquant.net" class="footer-link">support@properquant.net</a>
            </p>
            <p class="footer-text" style="margin-top: 15px; font-size: 12px;">
                © {{ date('Y') }} ProperQuant. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
