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
            background-color: #f8fafc;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #4b5563;
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
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.2s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        .token-section {
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        .token-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }
        .token-value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #1f2937;
            background-color: #ffffff;
            padding: 12px 16px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            word-break: break-all;
        }
        .expiry-notice {
            text-align: center;
            font-size: 14px;
            color: #dc2626;
            font-weight: 600;
            margin: 25px 0;
            padding: 12px;
            background-color: #fef2f2;
            border-radius: 8px;
            border-left: 4px solid #dc2626;
        }
        .security-notice {
            background-color: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .security-title {
            font-size: 16px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
        }
        .security-text {
            font-size: 14px;
            color: #92400e;
            margin: 0;
        }
        .alternative-link {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-top: 30px;
        }
        .alternative-link a {
            color: #3b82f6;
            text-decoration: none;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-brand {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .footer-tagline {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }
        .footer-links {
            margin-bottom: 20px;
        }
        .footer-links a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            margin: 0 15px;
            font-size: 14px;
        }
        .footer-copyright {
            font-size: 12px;
            color: #9ca3af;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 20px;
                border-radius: 8px;
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
            <h1>🔐 Password Reset</h1>
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
                    🔑 Reset My Password
                </a>
            </div>

            <!-- Token Section -->
            <div class="token-section">
                <div class="token-label">🎫 Reset Token</div>
                <div class="token-value">{{ $resetToken }}</div>
            </div>

            <!-- Expiry Notice -->
            <div class="expiry-notice">
                ⏰ This link expires at {{ $expiresAt->format('M d, Y h:i A') }}
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-title">⚠️ Security Notice</div>
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
                <a href="https://properquant.net">🌐 Website</a>
                <a href="mailto:support@properquant.net">📧 Support</a>
            </div>
            
            <div class="footer-copyright">
                © {{ date('Y') }} ProperQuant. All rights reserved.<br>
                This email was sent to {{ $userName }} regarding password reset.
            </div>
        </div>
    </div>
</body>
</html>
