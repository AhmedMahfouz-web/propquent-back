# Email Configuration Guide - ProperQuant Password Reset

## Overview
This guide explains how to configure the email system for sending password reset emails using Hostinger SMTP.

## Email Credentials
- **Email Address**: support@properquant.net
- **Password**: KEd+K*mt4I;
- **SMTP Server**: smtp.hostinger.com
- **Port**: 465
- **Encryption**: SSL

## Setup Instructions

### 1. Update Your .env File

Add or update the following lines in your `.env` file:

```env
# Application Settings
APP_NAME=ProperQuant
APP_URL=https://properquant.net
FRONTEND_URL=https://properquant.net

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=support@properquant.net
MAIL_PASSWORD="KEd+K*mt4I;"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="support@properquant.net"
MAIL_FROM_NAME="ProperQuant Support"
```

**Important Notes:**
- Wrap the password in quotes if it contains special characters
- Use `ssl` encryption for port 465
- Use `tls` encryption for port 587 (alternative)

### 2. Clear Configuration Cache

After updating the .env file, run these commands:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 3. Test Email Sending

You can test the email configuration using Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
Mail::raw('Test email from ProperQuant', function($message) {
    $message->to('your-test-email@example.com')
            ->subject('Test Email');
});
```

## How It Works

### 1. Password Reset Flow

1. **User requests password reset** via API endpoint:
   ```
   POST /api/auth/forgot-password
   Body: { "email": "user@example.com" }
   ```

2. **System generates reset token** and saves it to the user's record

3. **Email is sent** to the user with:
   - Reset link: `https://properquant.net/reset-password/{token}`
   - Reset token (displayed in email)
   - Expiration time (1 hour by default)

4. **User clicks link** or uses token to reset password:
   ```
   POST /api/auth/reset-password/{token}
   Body: { "password": "newpassword", "password_confirmation": "newpassword" }
   ```

### 2. Email Template

The email template is located at:
```
resources/views/emails/reset-password.blade.php
```

Features:
- Professional design with gradient header
- Clear call-to-action button
- Token display for manual entry
- Expiration warning
- Security notice
- Mobile-responsive design

### 3. Mailable Class

The email logic is in:
```
app/Mail/ResetPasswordMail.php
```

This class handles:
- Email subject and sender
- Passing data to the view
- Email configuration

## API Endpoints

### Forgot Password
```http
POST /api/auth/forgot-password
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password reset instructions have been sent to your email",
  "data": {
    "email": "user@example.com",
    "expires_at": "2025-10-05T07:53:02.000000Z"
  }
}
```

### Reset Password
```http
POST /api/auth/reset-password/{token}
Content-Type: application/json

{
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password reset successful"
}
```

## Troubleshooting

### Email Not Sending

1. **Check SMTP credentials**:
   - Verify email and password are correct
   - Ensure no extra spaces in .env file

2. **Check firewall/port**:
   - Port 465 must be open for outgoing connections
   - Try alternative port 587 with TLS encryption

3. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Test SMTP connection**:
   ```bash
   telnet smtp.hostinger.com 465
   ```

### Common Errors

**Error: Connection refused**
- Solution: Check if port 465 is blocked by firewall
- Alternative: Use port 587 with `MAIL_ENCRYPTION=tls`

**Error: Authentication failed**
- Solution: Verify email password is correct
- Check if email account is active in Hostinger

**Error: SSL certificate problem**
- Solution: Update PHP OpenSSL extension
- Or disable SSL verification (not recommended for production)

## Security Best Practices

1. **Never commit .env file** to version control
2. **Use environment variables** for sensitive data
3. **Rotate passwords regularly**
4. **Monitor email logs** for suspicious activity
5. **Set rate limiting** on forgot password endpoint
6. **Use HTTPS** for all API endpoints

## Alternative Ports

If port 465 doesn't work, try port 587:

```env
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

## Queue Configuration (Optional)

For better performance, send emails asynchronously:

1. Update .env:
```env
QUEUE_CONNECTION=database
```

2. Run queue worker:
```bash
php artisan queue:work
```

3. Update controller to queue emails:
```php
Mail::to($user->email)->queue(new ResetPasswordMail($user, $token, $resetUrl));
```

## Support

For issues with email configuration:
- Check Laravel logs: `storage/logs/laravel.log`
- Contact Hostinger support for SMTP issues
- Review Laravel Mail documentation: https://laravel.com/docs/mail

## Files Modified/Created

1. **Created**:
   - `app/Mail/ResetPasswordMail.php` - Mailable class
   - `resources/views/emails/reset-password.blade.php` - Email template
   - `EMAIL_SETUP_GUIDE.md` - This guide

2. **Modified**:
   - `app/Http/Controllers/Api/AuthController.php` - Added email sending
   - `config/mail.php` - Added encryption support
   - `.env.example` - Updated with proper mail configuration

## Testing Checklist

- [ ] .env file updated with correct credentials
- [ ] Configuration cache cleared
- [ ] Test email sent successfully
- [ ] Password reset email received
- [ ] Reset link works correctly
- [ ] Token validation works
- [ ] Password successfully reset
- [ ] Email template displays correctly
- [ ] Mobile responsive design verified

---

**Last Updated**: October 5, 2025
**Version**: 1.0
