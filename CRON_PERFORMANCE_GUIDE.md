# Cron Job Performance Guide

## 🎯 Optimized Schedule Strategy

### Current Optimized Setup

**Monthly Full Refresh** (1st at 1:00 AM):
```bash
php artisan evaluations:calculate --force
```
- Recalculates ALL historical data
- Runs once per month
- Ensures data accuracy

**Daily Light Update** (2:00 AM):
```bash
php artisan evaluations:calculate --from-month=LAST_3_MONTHS
```
- Only processes last 3 months
- Much faster than full calculation
- Keeps current data fresh

---

## 📊 Performance Comparison

### Full Calculation (No Date Range)

**What it does**:
- Processes ALL projects
- Calculates ALL months from first transaction to now
- Example: 100 projects × 24 months = 2,400 calculations

**When to use**:
- ✅ Monthly on 1st (with --force)
- ✅ After major data changes
- ✅ Initial setup
- ❌ NOT for daily runs (too resource-intensive)

**Estimated time**:
- Small (50 projects, 1 year): ~30 seconds
- Medium (200 projects, 2 years): ~2-3 minutes
- Large (500+ projects, 3+ years): ~10-15 minutes

### Optimized Daily (Last 3 Months)

**What it does**:
- Processes ALL projects
- Only calculates last 3 months
- Example: 100 projects × 3 months = 300 calculations

**When to use**:
- ✅ Daily updates
- ✅ Keeping current data fresh
- ✅ After recent transactions

**Estimated time**:
- Small (50 projects): ~5-10 seconds
- Medium (200 projects): ~20-30 seconds
- Large (500+ projects): ~1-2 minutes

### Current Month Only

**What it does**:
- Processes ALL projects
- Only calculates current month
- Example: 100 projects × 1 month = 100 calculations

**When to use**:
- ✅ Multiple times per day (if needed)
- ✅ Real-time updates during business hours
- ✅ After high-frequency transaction entry

**Estimated time**:
- Small (50 projects): ~3-5 seconds
- Medium (200 projects): ~10-15 seconds
- Large (500+ projects): ~30-45 seconds

---

## 🚀 Recommended Schedules

### Option 1: Conservative (Recommended for Most)

**Best for**: 50-500 projects, normal transaction frequency

```php
// Monthly: Full refresh
Schedule::command('evaluations:calculate --force')
    ->monthlyOn(1, '01:00');

// Daily: Last 3 months
Schedule::command('evaluations:calculate --from-month=' . now()->subMonths(2)->format('Y-m-01'))
    ->dailyAt('02:00');
```

**Resource Usage**: Low
**Data Freshness**: Updated daily
**Server Load**: Minimal

### Option 2: Balanced (Current Setup)

**Best for**: 100-300 projects, moderate transaction frequency

Same as Option 1, but add:

```php
// Every 6 hours: Current month only
Schedule::command('evaluations:calculate --from-month=' . now()->startOfMonth()->format('Y-m-01'))
    ->cron('0 8,12,16,20 * * *');  // 8am, 12pm, 4pm, 8pm
```

**Resource Usage**: Low-Medium
**Data Freshness**: Updated 4x per day
**Server Load**: Light

### Option 3: Real-Time (High Frequency)

**Best for**: High transaction volume, need real-time data

```php
// Monthly: Full refresh
Schedule::command('evaluations:calculate --force')
    ->monthlyOn(1, '01:00');

// Every 2 hours: Current month
Schedule::command('evaluations:calculate --from-month=' . now()->startOfMonth()->format('Y-m-01'))
    ->cron('0 */2 * * *');  // Every 2 hours
```

**Resource Usage**: Medium
**Data Freshness**: Real-time
**Server Load**: Moderate

### Option 4: Light (Minimal Resources)

**Best for**: Small dataset (< 50 projects) or tight resources

```php
// Weekly: Full refresh (1st and 15th)
Schedule::command('evaluations:calculate --force')
    ->cron('0 1 1,15 * *');  // 1st and 15th at 1am

// Daily: Current month only
Schedule::command('evaluations:calculate --from-month=' . now()->startOfMonth()->format('Y-m-01'))
    ->dailyAt('02:00');
```

**Resource Usage**: Very Low
**Data Freshness**: Updated daily (current month)
**Server Load**: Minimal

---

## 🔍 Monitoring Performance

### Check Execution Time

Add to `routes/console.php`:

```php
Schedule::command('evaluations:calculate --from-month=' . now()->subMonths(2)->format('Y-m-01'))
    ->dailyAt('02:00')
    ->onSuccess(function () {
        \Log::info('[DAILY] Completed in ' . round(microtime(true) - LARAVEL_START, 2) . 's');
    });
```

### Monitor Server Load

Via SSH:
```bash
# Check current load
top

# Check during cron execution
watch -n 1 'ps aux | grep "artisan evaluations"'

# Check memory usage
free -h
```

### Check Logs

```bash
# View Laravel log
tail -f storage/logs/laravel.log

# Check execution times
grep "DAILY" storage/logs/laravel.log

# Check for errors
grep "ERROR" storage/logs/laravel.log
```

---

## ⚙️ Server Requirements

### Minimum Requirements

**For Daily Full Calculation**:
- PHP Memory: 256MB
- CPU: Shared hosting OK
- Max Execution Time: 300s (5 min)
- Database: Standard MySQL

**For Optimized Daily (Last 3 Months)**:
- PHP Memory: 128MB
- CPU: Shared hosting OK
- Max Execution Time: 120s (2 min)
- Database: Standard MySQL

### Recommended Settings

Update `.htaccess` or `php.ini`:

```apache
# .htaccess
php_value memory_limit 256M
php_value max_execution_time 300
php_value max_input_time 300
```

Or in `php.ini`:
```ini
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

---

## 🎛️ Customization Options

### By Date Range

```bash
# Current month only (fastest)
php artisan evaluations:calculate --from-month=$(date +%Y-%m-01)

# Last 2 months
php artisan evaluations:calculate --from-month=$(date -d '2 months ago' +%Y-%m-01)

# Last 6 months
php artisan evaluations:calculate --from-month=$(date -d '6 months ago' +%Y-%m-01)

# Specific range
php artisan evaluations:calculate --from-month=2024-01-01 --to-month=2024-12-01

# Everything (slowest)
php artisan evaluations:calculate
```

### By Project

```bash
# Single project (very fast)
php artisan evaluations:calculate --project-key=ABC123

# Can combine with date range
php artisan evaluations:calculate --project-key=ABC123 --from-month=2024-01-01
```

---

## 📈 Scaling Recommendations

### Small Dataset (< 100 projects)

**Recommendation**: Run full calculation daily

```php
Schedule::command('evaluations:calculate --force')
    ->dailyAt('02:00');
```

**Why**: Fast enough, simplest setup

### Medium Dataset (100-500 projects)

**Recommendation**: Monthly full + Daily last 3 months (current setup)

**Why**: Best balance of freshness and performance

### Large Dataset (500+ projects)

**Recommendation**: Optimize further

```php
// Monthly: Full refresh
Schedule::command('evaluations:calculate --force')
    ->monthlyOn(1, '01:00');

// Daily: Current month only
Schedule::command('evaluations:calculate --from-month=' . now()->startOfMonth()->format('Y-m-01'))
    ->dailyAt('02:00');

// Optional: Last 3 months on Sundays
Schedule::command('evaluations:calculate --from-month=' . now()->subMonths(2)->format('Y-m-01'))
    ->weekly()
    ->sundays()
    ->at('03:00');
```

### Very Large Dataset (1000+ projects)

**Recommendation**: Use queue system

1. Create queue job for evaluation calculation
2. Process projects in batches
3. Run via cron but with queues

---

## ⚠️ Warning Signs

Watch for these issues:

### 🔴 Server Overload
- Cron jobs timing out
- 500 errors during execution
- Server CPU constantly at 100%

**Solution**: Reduce frequency or optimize date range

### 🟡 Slow Performance
- Jobs taking > 5 minutes
- Memory limit errors
- Timeout errors

**Solution**: 
- Increase PHP limits
- Reduce date range
- Run during off-peak hours

### 🟢 Normal Operation
- Jobs complete in < 2 minutes
- No timeout errors
- CPU spike brief and manageable

**Good to go!**

---

## 🎯 Hostinger-Specific Tips

### Shared Hosting Limits

Hostinger shared hosting typically has:
- Memory: 128-256MB
- Execution time: 120-300s
- CPU: Shared, burst OK

**Recommendation for Hostinger**:
- ✅ Use optimized schedule (last 3 months daily)
- ✅ Run during off-peak (2-4 AM)
- ✅ Monitor first few runs
- ❌ Avoid full calculation more than monthly

### VPS/Cloud Hosting

More resources available:
- Memory: 512MB-2GB+
- Execution time: Configurable
- CPU: Dedicated

**Recommendation for VPS**:
- ✅ Can run full calculation daily if needed
- ✅ Can add hourly current-month updates
- ✅ More flexibility with schedule

---

## 📝 Quick Decision Guide

**Question**: How many projects do you have?
- < 50: Run full daily ✅
- 50-200: Current setup (last 3 months) ✅
- 200-500: Current setup ✅
- 500+: Current month daily, full monthly ⚠️

**Question**: How often do transactions happen?
- Multiple per day: Add 4x daily current month
- Daily: Current setup is fine
- Weekly: Can reduce to weekly updates
- Monthly: Minimal schedule OK

**Question**: What hosting do you have?
- Shared: Stick to current setup
- VPS: Can increase frequency
- Cloud: Fully flexible

---

## ✅ Final Recommendation

**For most users with 50-500 projects on shared hosting**:

Keep the **current optimized setup**:
1. Monthly full refresh on 1st
2. Daily last 3 months update

This provides:
- ✅ Always current data
- ✅ Minimal server load
- ✅ Works on shared hosting
- ✅ Reliable performance

**No changes needed unless**:
- You have 1000+ projects (optimize further)
- You need real-time updates (add hourly)
- You have < 50 projects (can run full daily)

---

## 🆘 Troubleshooting

### Job Takes Too Long

**Solution**:
```php
// Reduce to current month only
Schedule::command('evaluations:calculate --from-month=' . now()->startOfMonth()->format('Y-m-01'))
    ->dailyAt('02:00');
```

### Memory Errors

**Solution**:
1. Increase PHP memory: `php_value memory_limit 512M`
2. Or process fewer months
3. Or run during low-traffic hours

### Timeout Errors

**Solution**:
1. Increase execution time: `php_value max_execution_time 600`
2. Or reduce date range
3. Or split into multiple smaller jobs

---

**Your current setup is already optimized! 🎉**

The daily run only processes last 3 months, which is much more efficient than full calculation.
