# Automatic Evaluation Updates - Summary

## Overview

The system now automatically updates evaluations and profits without requiring manual transaction triggers.

---

## ✅ What Was Implemented

### 1. Scheduled Tasks (`routes/console.php`)

**Monthly Refresh** - 1st of each month at 1:00 AM:
- Runs: `evaluations:calculate --force`
- Purpose: Complete recalculation of all evaluations
- Ensures: Fresh start for each new month

**Daily Update** - Every day at 2:00 AM:
- Runs: `evaluations:calculate`
- Purpose: Update current month data
- Ensures: Reports always show current data

### 2. Initialization Command

**Command**: `php artisan evaluations:initialize`

**Purpose**: One-time setup after deployment

**What it does**:
1. Clears all caches
2. Calculates all historical evaluations
3. Optimizes the application
4. Provides next steps

**Options**:
- `--force`: Recalculate existing evaluations

### 3. Documentation

- `DEPLOYMENT_GUIDE.md`: Complete deployment instructions
- `HOSTINGER_QUICK_SETUP.md`: Quick reference for Hostinger
- `AUTOMATIC_UPDATES_SUMMARY.md`: This file

---

## 🚀 How to Use

### After Uploading to Hostinger

**Step 1 - Run Initialization (ONE TIME ONLY)**:
```bash
php artisan evaluations:initialize --force
```

**Step 2 - Set Up Cron Job**:

Go to **Hostinger Panel → Advanced → Cron Jobs**

Create cron job with:
- **Schedule**: Every minute (`* * * * *`)
- **Command**: 
```bash
cd /home/username/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

Replace `username` and `yourdomain.com` with your actual values.

**Step 3 - Verify**:
```bash
php artisan schedule:list
```

---

## 🔄 How It Works

### Before (Manual Updates)

- ❌ Evaluations only updated when transactions created/modified
- ❌ New months showed old data until transactions triggered
- ❌ Required manual intervention
- ❌ Reports could be outdated

### After (Automatic Updates)

- ✅ Evaluations update automatically every day
- ✅ New months start with fresh calculations
- ✅ No manual intervention needed
- ✅ Reports always current and accurate

---

## 📅 Update Schedule

| Task | Frequency | Time | Command | Purpose |
|------|-----------|------|---------|---------|
| **Monthly Refresh** | 1st of month | 1:00 AM | `evaluations:calculate --force` | Full recalculation (all data) |
| **Daily Update** | Every day | 2:00 AM | `evaluations:calculate --from-month=LAST_3_MONTHS` | Update last 3 months (optimized) |

**Timezone**: Africa/Cairo (configurable in `routes/console.php`)

---

## 🛠️ Manual Commands

Use these when needed:

```bash
# Initialize after deployment (run once)
php artisan evaluations:initialize --force

# Manual calculation (anytime)
php artisan evaluations:calculate

# Force recalculation (overwrites existing)
php artisan evaluations:calculate --force

# Calculate specific project
php artisan evaluations:calculate --project-key=ABC123

# Calculate date range
php artisan evaluations:calculate --from-month=2024-01-01 --to-month=2024-12-01

# View scheduled tasks
php artisan schedule:list

# Test scheduler (runs tasks immediately)
php artisan schedule:run
```

---

## 📊 What Gets Updated

### Project Evaluations
- Asset evaluations (cumulative)
- Asset expenses and revenues
- Operation expenses and revenues
- Asset profit (cumulative)
- Operation profit (cumulative)
- Total profit (cumulative)

### User Financials
- Equity percentages
- User profits based on equity
- Deposits and withdrawals
- Cash balances

### Company Reports
- Total company evaluations
- Total expenses and revenues
- Monthly totals and summaries
- All financial metrics

---

## 🔍 Monitoring

### Check Logs

**Laravel Log**:
```bash
tail -f storage/logs/laravel.log
```

Look for:
- `Monthly evaluations refreshed successfully`
- `Daily evaluations update completed`
- Any error messages

**Hostinger Cron Logs**:
- Go to Hostinger Panel → Cron Jobs
- Click on your cron job
- View execution logs

### Verify Data

1. Log in to admin panel
2. Check any financial report
3. Verify data is current
4. Check "Asset evaluations last updated" timestamp

---

## ⚙️ Configuration

### Change Timezone

Edit `routes/console.php`:
```php
->timezone('Africa/Cairo')  // Change to your timezone
```

### Change Schedule Times

Edit `routes/console.php`:
```php
// Monthly at 1:00 AM
->monthlyOn(1, '01:00')

// Daily at 2:00 AM
->dailyAt('02:00')
```

### Disable Automatic Updates

Remove or comment out the scheduled tasks in `routes/console.php`

---

## 🚨 Troubleshooting

### Tasks Not Running

**Problem**: Scheduled tasks aren't executing

**Solution**:
1. Verify cron job is set up correctly
2. Check cron job path matches your actual path
3. Test manually: `php artisan schedule:run`
4. Check logs: `storage/logs/laravel.log`

### Permission Errors

**Problem**: Permission denied errors

**Solution**:
```bash
chmod -R 755 storage bootstrap/cache
chmod +x artisan
```

### Memory Errors

**Problem**: Memory limit exceeded

**Solution**:
Increase PHP memory in `.htaccess`:
```apache
php_value memory_limit 512M
```

### Database Errors

**Problem**: Connection or query errors

**Solution**:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 💡 Best Practices

1. **Initial Setup**: Always run `evaluations:initialize --force` after deployment
2. **Monitoring**: Check logs regularly, especially after first setup
3. **Timezone**: Ensure timezone matches your business location
4. **Backup**: Backup database before running force recalculation
5. **Testing**: Test manually before relying on cron jobs

---

## 📈 Benefits

✨ **Automatic**: No manual intervention required
✨ **Accurate**: Always current data in reports
✨ **Reliable**: Scheduled tasks run consistently
✨ **Scalable**: Handles growing data efficiently
✨ **Logged**: All operations logged for monitoring
✨ **Flexible**: Easy to customize schedule and behavior

---

## 🎯 Summary

You now have a fully automated system that:
- Updates evaluations daily at 2:00 AM
- Does full refresh monthly on the 1st at 1:00 AM
- Requires no manual intervention
- Keeps all reports current and accurate
- Logs all operations for monitoring

**After deployment**: Run `evaluations:initialize --force` once, set up the cron job, and you're done!

The system handles everything automatically from that point forward. 🎉
