<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }
        .header {
            background-color: #000000;
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            color: #cccccc;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #333333;
            margin-bottom: 35px;
            line-height: 1.7;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 16px 32px;
            background-color: #000000;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            border: 2px solid #000000;
        }
        .reset-button:hover {
            background-color: #ffffff;
            color: #000000;
        }
        .token-section {
            background-color: #f8f8f8;
            border: 1px solid #e0e0e0;
            padding: 25px;
            margin: 30px 0;
        }
        .token-label {
            font-size: 14px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .token-value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #000000;
            background-color: #ffffff;
            padding: 12px 16px;
            border: 1px solid #cccccc;
            word-break: break-all;
        }
        .expiry-notice {
            text-align: center;
            font-size: 14px;
            color: #000000;
            font-weight: 600;
            margin: 25px 0;
            padding: 12px;
            background-color: #f0f0f0;
            border-left: 4px solid #000000;
        }
        .security-notice {
            background-color: #f8f8f8;
            border: 1px solid #cccccc;
            padding: 20px;
            margin: 30px 0;
        }
        .security-title {
            font-size: 16px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .security-text {
            font-size: 14px;
            color: #333333;
            margin: 0;
        }
        .alternative-link {
            text-align: center;
            font-size: 14px;
            color: #666666;
            margin-top: 30px;
        }
        .alternative-link a {
            color: #000000;
            text-decoration: underline;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer-brand {
            font-size: 20px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 8px;
        }
        .footer-tagline {
            font-size: 14px;
            color: #666666;
            margin-bottom: 20px;
        }
        .footer-links {
            margin-bottom: 20px;
        }
        .footer-links a {
            color: #000000;
            text-decoration: underline;
            font-weight: 500;
            margin: 0 15px;
            font-size: 14px;
        }
        .footer-copyright {
            font-size: 12px;
            color: #999999;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 20px;
            }
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 30px 20px;
            }
            .reset-button {
                padding: 14px 24px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Password Reset</h1>
            <p>Secure your ProperQuant account</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello {{ $userName }},</div>
            
            <div class="message">
                We received a request to reset your password for your ProperQuant account. 
                Click the button below to create a new password and regain access to your account.
            </div>

            <!-- CTA Button -->
            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Reset My Password
                </a>
            </div>

            <div class="divider"></div>

            <!-- Token Section -->
            <div class="token-section">
                <div class="token-label">Reset Token</div>
                <div class="token-value">{{ $resetToken }}</div>
            </div>

            <!-- Expiry Notice -->
            <div class="expiry-notice">
                This link expires at {{ $expiresAt->format('M d, Y h:i A') }}
            </div>

            <div class="divider"></div>

            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-title">Security Notice</div>
                <p class="security-text">
                    If you didn't request this password reset, please ignore this email. 
                    Your password will remain unchanged and your account stays secure.
                </p>
            </div>

            <!-- Alternative Link -->
            <div class="alternative-link">
                <p>Having trouble with the button?</p>
                <p>Copy and paste this link into your browser:</p>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-brand">ProperQuant</div>
            <div class="footer-tagline">Real Estate Investment Management Platform</div>
            
            <div class="footer-links">
                <a href="https://properquant.net">Website</a>
                <a href="mailto:support@properquant.net">Support</a>
            </div>
            
            <div class="footer-copyright">
                © {{ date('Y') }} ProperQuant. All rights reserved.<br>
                This email was sent to {{ $userName }} regarding password reset.
            </div>
        </div>
    </div>
</body>
</html>
