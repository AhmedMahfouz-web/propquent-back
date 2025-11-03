# Deployment Guide - Hostinger Setup

This guide explains how to deploy the PropQuent application on Hostinger with automatic monthly evaluation updates.

## Table of Contents
1. [Initial Deployment](#initial-deployment)
2. [One-Time Initialization](#one-time-initialization)
3. [Setting Up Cron Jobs](#setting-up-cron-jobs)
4. [Verification](#verification)
5. [Troubleshooting](#troubleshooting)

---

## Initial Deployment

### Step 1: Upload Files to Hostinger

1. Connect to your Hostinger account via FTP or File Manager
2. Upload all files to your domain's root directory (usually `public_html` or `domains/yourdomain.com/public_html`)
3. Make sure the `.env` file is uploaded and configured with your production database credentials

### Step 2: Configure Environment

1. Update your `.env` file with production settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password

# Timezone for scheduled tasks
APP_TIMEZONE=Africa/Cairo
```

2. Set proper permissions via SSH or File Manager:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Step 3: Install Dependencies (via SSH)

If you have SSH access:
```bash
composer install --optimize-autoloader --no-dev
php artisan key:generate
```

---

## One-Time Initialization

After uploading your files, run this command **once** to initialize all evaluations and profits:

### Via SSH:
```bash
php artisan evaluations:initialize --force
```

### Via Hostinger Cron Job (if no SSH access):
1. Go to Hostinger Panel → Advanced → Cron Jobs
2. Create a **one-time** cron job:
   - **Schedule**: Set it to run once (e.g., 5 minutes from now)
   - **Command**:
   ```bash
   cd /home/username/domains/yourdomain.com/public_html && php artisan evaluations:initialize --force
   ```
3. After it runs successfully, **delete this cron job**

---

## Setting Up Cron Jobs

### Method 1: Via Hostinger Control Panel (Recommended)

1. **Log in to Hostinger Control Panel**
2. **Navigate to**: Advanced → Cron Jobs
3. **Click**: "Create Cron Job"

#### Cron Job 1: Laravel Scheduler (Main Task)

This single cron job will run all scheduled tasks (monthly updates, daily updates, etc.)

- **Name**: Laravel Scheduler
- **Schedule**: Every minute
- **Command**:
```bash
cd /home/username/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

**Important**: Replace:
- `username` with your Hostinger username
- `yourdomain.com` with your actual domain

#### Alternative Schedule (if "every minute" is not available):

If your hosting only allows hourly or specific times:

- **Name**: Evaluation Updates
- **Schedule**: Daily at 02:00 AM
- **Command**:
```bash
cd /home/username/domains/yourdomain.com/public_html && php artisan evaluations:calculate >> /dev/null 2>&1
```

### Method 2: Via SSH (Advanced)

If you have SSH access, edit crontab:
```bash
crontab -e
```

Add this line:
```bash
* * * * * cd /home/username/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

---

## What Gets Scheduled

The system automatically runs these tasks:

### 1. Monthly Refresh (1st of each month at 1:00 AM)
- **Task**: `evaluations:calculate --force`
- **Purpose**: Complete recalculation of all evaluations and profits
- **When**: First day of every month
- **Why**: Ensures new month starts with fresh, accurate data

### 2. Daily Update (Every day at 2:00 AM)
- **Task**: `evaluations:calculate`
- **Purpose**: Update current month's evaluations
- **When**: Daily
- **Why**: Keeps current month data fresh without full recalculation

---

## Verification

### Check Scheduled Tasks

Via SSH:
```bash
php artisan schedule:list
```

Expected output:
```
  0 1 1 * *  evaluations:calculate --force ........ Next Due: 1 day from now
  0 2 * * *  evaluations:calculate ................ Next Due: 14 hours from now
```

### Test Manual Execution

Via SSH:
```bash
# Test the calculation command
php artisan evaluations:calculate

# Test the scheduler (runs all due tasks)
php artisan schedule:run

# Test the initialization command
php artisan evaluations:initialize
```

### Check Logs

1. **Application Logs**: Check `storage/logs/laravel.log`
2. **Cron Logs**: Check Hostinger Control Panel → Cron Jobs → View Logs
3. Look for these messages:
   - `Monthly evaluations refreshed successfully`
   - `Daily evaluations update completed`

### Verify in Application

1. Log in to your admin panel
2. Go to any financial report
3. Check that data is current and accurate
4. Look for the "Asset evaluations last updated" timestamp

---

## Troubleshooting

### Issue: Cron Job Not Running

**Solution**:
1. Check cron job path is correct
2. Verify PHP path: `which php` via SSH
3. Try full PHP path in cron:
```bash
/usr/bin/php artisan schedule:run
```

### Issue: Permission Denied

**Solution**:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod +x artisan
```

### Issue: Database Connection Error

**Solution**:
1. Verify `.env` database credentials
2. Clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Memory Limit Error

**Solution**:
1. Increase PHP memory limit in `.htaccess`:
```apache
php_value memory_limit 512M
```

2. Or in `php.ini`:
```ini
memory_limit = 512M
```

### Issue: Tasks Not Running at Scheduled Time

**Solution**:
1. Check server timezone:
```bash
php artisan tinker
>>> now()
```

2. Update timezone in `.env`:
```env
APP_TIMEZONE=Africa/Cairo
```

3. Update timezone in `routes/console.php` if needed

---

## Manual Commands Reference

Run these commands via SSH when needed:

```bash
# Initialize everything (run once after deployment)
php artisan evaluations:initialize --force

# Manually calculate evaluations
php artisan evaluations:calculate

# Force recalculate all evaluations
php artisan evaluations:calculate --force

# Calculate for specific project
php artisan evaluations:calculate --project-key=PROJECT_KEY

# Calculate for date range
php artisan evaluations:calculate --from-month=2024-01-01 --to-month=2024-12-01

# View scheduled tasks
php artisan schedule:list

# Test scheduled tasks (runs immediately)
php artisan schedule:run

# View logs
tail -f storage/logs/laravel.log
```

---

## Important Notes

1. **First Run**: Always run `evaluations:initialize --force` after deployment
2. **Timezone**: Make sure APP_TIMEZONE matches your business timezone
3. **Cron Frequency**: The Laravel scheduler needs to run every minute for accurate scheduling
4. **Logs**: Monitor logs regularly to ensure tasks are running successfully
5. **Backup**: Always backup your database before force recalculation

---

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Verify cron job logs in Hostinger panel
3. Test commands manually via SSH
4. Ensure all permissions are correct

---

## Summary

**After deployment, follow these steps**:

1. ✅ Upload all files to Hostinger
2. ✅ Configure `.env` file
3. ✅ Set proper permissions (755 for storage and bootstrap/cache)
4. ✅ Run: `php artisan evaluations:initialize --force`
5. ✅ Set up cron job to run every minute: `* * * * * php artisan schedule:run`
6. ✅ Verify tasks are scheduled: `php artisan schedule:list`
7. ✅ Monitor logs for successful execution

**The system will automatically**:
- Refresh all evaluations on the 1st of each month at 1:00 AM
- Update current month data daily at 2:00 AM
- Log success/failure to Laravel logs

**You're done!** The system will now keep evaluations and profits current automatically.
