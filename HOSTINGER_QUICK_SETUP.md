# Hostinger Quick Setup Guide

## 🚀 Quick Start (3 Steps)

### Step 1: Run Once After Upload
```bash
php artisan evaluations:initialize --force
```

### Step 2: Set Up Cron Job
**Hostinger Panel → Advanced → Cron Jobs → Create Cron Job**

**Common Task:**
```
Schedule: * * * * * (Every minute)
Command: cd /home/username/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

**Replace**:
- `username` → Your Hostinger username
- `yourdomain.com` → Your actual domain

### Step 3: Verify
```bash
php artisan schedule:list
```

---

## 📋 Hostinger Cron Job Settings

### If "Every Minute" is Available:
- **Schedule**: `* * * * *` (Every minute)
- **Command**: 
  ```bash
  cd /home/username/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
  ```

### If Only Specific Times Available:
Create 2 cron jobs:

**Job 1 - Monthly Update:**
- **Day**: 1st of month
- **Time**: 01:00 AM
- **Command**: 
  ```bash
  cd /home/username/domains/yourdomain.com/public_html && php artisan evaluations:calculate --force >> /dev/null 2>&1
  ```

**Job 2 - Daily Update:**
- **Schedule**: Daily
- **Time**: 02:00 AM
- **Command**: 
  ```bash
  cd /home/username/domains/yourdomain.com/public_html && php artisan evaluations:calculate >> /dev/null 2>&1
  ```

---

## 🔍 Find Your Path

Via SSH:
```bash
pwd
```

Typical Hostinger paths:
- `/home/u123456789/domains/yourdomain.com/public_html`
- `/home/username/public_html`

---

## ✅ Verification Checklist

- [ ] Uploaded all files
- [ ] Configured `.env` with database credentials
- [ ] Set permissions (755 for storage and bootstrap/cache)
- [ ] Ran `php artisan evaluations:initialize --force`
- [ ] Created cron job in Hostinger panel
- [ ] Verified with `php artisan schedule:list`
- [ ] Checked logs in `storage/logs/laravel.log`

---

## 📝 What Happens Automatically

✨ **Monthly (1st at 1:00 AM)**:
- Full recalculation of ALL evaluations and profits
- Updates all historical data from beginning
- Ensures long-term accuracy

✨ **Daily (2:00 AM)** - OPTIMIZED:
- Updates only last 3 months (current + previous 2)
- Much faster than full calculation
- Keeps current data fresh without overloading server

---

## 🆘 Quick Fixes

### Cron Not Working?
```bash
# Check PHP path
which php

# Use full path in cron
/usr/bin/php artisan schedule:run
```

### Permission Errors?
```bash
chmod -R 755 storage bootstrap/cache
chmod +x artisan
```

### Database Errors?
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📞 Need Help?

1. Check: `storage/logs/laravel.log`
2. Test manually: `php artisan evaluations:calculate`
3. View schedule: `php artisan schedule:list`
4. Run scheduler once: `php artisan schedule:run`

---

## 🎯 That's It!

After setup, the system runs automatically. No manual intervention needed! 🎉
