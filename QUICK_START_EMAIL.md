# Quick Start - Password Reset Email Setup

## 🚀 Quick Setup (3 Steps)

### Step 1: Update .env File
Copy these lines to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=support@properquant.net
MAIL_PASSWORD="KEd+K*mt4I;"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="support@properquant.net"
MAIL_FROM_NAME="ProperQuant Support"
FRONTEND_URL=https://properquant.net
```

### Step 2: Clear Cache
```bash
php artisan config:clear && php artisan cache:clear
```

### Step 3: Test It
```bash
php artisan email:test your-email@example.com
```

## ✅ What Was Implemented

1. **Email Mailable Class** (`app/Mail/ResetPasswordMail.php`)
   - Handles email sending logic
   - Configures sender and subject

2. **Beautiful Email Template** (`resources/views/emails/reset-password.blade.php`)
   - Professional design with gradient header
   - Reset button and token display
   - Mobile responsive
   - Security warnings

3. **Updated AuthController** (`app/Http/Controllers/Api/AuthController.php`)
   - Sends email when user requests password reset
   - Generates secure reset token
   - Creates reset URL

4. **Test Command** (`app/Console/Commands/TestEmailCommand.php`)
   - Easy way to test email configuration
   - Shows current settings
   - Provides troubleshooting tips

## 📧 How Users Reset Password

1. User calls API: `POST /api/auth/forgot-password` with their email
2. System generates reset token and sends email
3. User receives email with reset link and token
4. User clicks link or uses token to reset password
5. User calls API: `POST /api/auth/reset-password/{token}` with new password

## 🔧 Troubleshooting

**Email not sending?**
- Check `.env` credentials are correct
- Run `php artisan config:clear`
- Check if port 465 is open
- Try port 587 with `MAIL_ENCRYPTION=tls`

**Still not working?**
- Check logs: `storage/logs/laravel.log`
- Test SMTP: `telnet smtp.hostinger.com 465`
- Verify email account is active in Hostinger

## 📝 API Usage Example

```bash
# Request password reset
curl -X POST https://properquant.net/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'

# Reset password with token
curl -X POST https://properquant.net/api/auth/reset-password/TOKEN_HERE \
  -H "Content-Type: application/json" \
  -d '{"password":"newpass123","password_confirmation":"newpass123"}'
```

## 📚 Full Documentation

See `EMAIL_SETUP_GUIDE.md` for complete documentation.

---

**Ready to use!** Just update your .env and test it. 🎉
