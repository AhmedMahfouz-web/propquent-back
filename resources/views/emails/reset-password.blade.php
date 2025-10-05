<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .message {
            font-size: 15px;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .reset-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .reset-button:hover {
            transform: translateY(-2px);
        }

        .token-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }

        .token-label {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .token-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            word-break: break-all;
        }

        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .warning-text {
            font-size: 14px;
            color: #856404;
            margin: 0;
        }

        .expiry {
            font-size: 14px;
            color: #dc3545;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-text {
            font-size: 13px;
            color: #6c757d;
            margin: 5px 0;
        }

        .footer-link {
            color: #667eea;
            text-decoration: none;
        }

        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 30px 0;
        }

        .help-text {
            font-size: 14px;
            color: #6c757d;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>

        <div class="content">
            <div class="greeting">
                Hello {{ $userName }},
            </div>

            <div class="message">
                We received a request to reset your password for your ProperQuant account.
                If you made this request, click the button below to reset your password.
            </div>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Reset Password
                </a>
            </div>

            <div class="token-box">
                <div class="token-label">Your Reset Token</div>
                <div class="token-value">{{ $resetToken }}</div>
            </div>

            <div class="expiry">
                This link will expire at {{ $expiresAt->format('M d, Y h:i A') }}
            </div>

            <div class="divider"></div>

            <div class="warning">
                <p class="warning-text">
                    <strong>Security Notice:</strong> If you didn't request a password reset,
                    please ignore this email or contact our support team if you have concerns.
                    Your password will remain unchanged.
                </p>
            </div>

            <div class="help-text">
                If the button doesn't work, copy and paste this link into your browser:<br>
                <a href="{{ $resetUrl }}" style="color: #667eea; word-break: break-all;">{{ $resetUrl }}</a>
            </div>
        </div>

        <div class="footer">
            <p class="footer-text">
                <strong>ProperQuant</strong><br>
                Real Estate Investment Management Platform
            </p>
            <p class="footer-text">
                <a href="https://properquant.net" class="footer-link">properquant.net</a> |
                <a href="mailto:support@properquant.net" class="footer-link">support@properquant.net</a>
            </p>
            <p class="footer-text" style="margin-top: 20px; font-size: 12px;">
                © {{ date('Y') }} ProperQuant. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>
