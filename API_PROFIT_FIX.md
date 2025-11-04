# Home API Profit Calculation Fix

## 🚨 Problem Found

The `financial_summary.profit` values in the home API were incorrect and didn't match the `historical_data` values.

### The Bug

**File**: `app/Http/Controllers/Api/HomeController.php`  
**Function**: `calculateUserProfitForMonth()` (lines 164-186)

**Wrong Code**:
```php
// WRONG: Using cumulative profit instead of monthly profit
$companyAssetProfit = MonthlyProjectEvaluation::where('month_date', $currentMonth)
    ->sum('profit_asset_cumulative');  // ❌ This is CUMULATIVE, not monthly
```

### Why It Was Wrong

`profit_asset_cumulative` contains the **total profit from project inception**, not the profit for that specific month.

**Example**:
- Project started Jan 2024
- Oct 2025: profit_asset_cumulative = $50,000 (total from Jan 2024 to Oct 2025)
- Nov 2025: profit_asset_cumulative = $51,000 (total from Jan 2024 to Nov 2025)

Using cumulative values directly gives wrong results:
- ❌ Shows $50,000 for October (should be monthly profit, e.g., $1,118)
- ❌ Shows $51,000 for November (should be monthly profit, e.g., $25)

---

## ✅ The Fix

**Updated Code**:
```php
// CORRECT: Calculate monthly profit using the same method as historical data
$companyProfit = $this->calculateCompanyProfitByMonth([$currentMonth]);
$companyAssetProfit = $companyProfit[$currentMonth]['asset'] ?? 0;
$companyOperationProfit = $companyProfit[$currentMonth]['operation'] ?? 0;
$companyTotalProfit = $companyProfit[$currentMonth]['total'] ?? 0;
```

### How It Works Now

The fix reuses the existing `calculateCompanyProfitByMonth()` function which:

1. **Gets each project's monthly profit**:
   ```php
   Monthly Asset Profit = Current Month Evaluation 
                        - Previous Month Evaluation 
                        + Revenue Asset 
                        - Expense Asset
   ```

2. **Sums all projects**: Total company profit = Sum of all projects' monthly profits

3. **Calculates user profit**: 
   ```php
   User Profit = Previous Month Equity % × Current Month Company Profit
   ```

---

## 📊 Expected Results

### Before Fix

**API Response** (financial_summary):
```json
"profit": {
    "total": 1039.47,        // ❌ Wrong (using cumulative)
    "this_month": 1039.47,
    "asset_profit": 1039.47,
    "operation_profit": 0
}
```

**Historical Data**:
```json
{
    "month": "2025-11",
    "profit_asset": 25.35,   // ✅ Correct (monthly calculation)
    "total_profit": 25.35
}
```

**Mismatch**: 1039.47 ≠ 25.35 ❌

### After Fix

**API Response** (financial_summary):
```json
"profit": {
    "total": 25.35,          // ✅ Matches historical data!
    "this_month": 25.35,
    "asset_profit": 25.35,
    "operation_profit": 0
}
```

**Historical Data**:
```json
{
    "month": "2025-11",
    "profit_asset": 25.35,   // ✅ Correct
    "total_profit": 25.35
}
```

**Match**: 25.35 = 25.35 ✅

---

## 🎯 Consistency Achieved

Both `financial_summary` and `historical_data` now use the **exact same calculation method**:

```php
// Both call this function
$companyProfit = $this->calculateCompanyProfitByMonth([$currentMonth]);
```

**Benefits**:
1. ✅ **Consistent**: Summary and historical data always match
2. ✅ **Accurate**: Uses monthly profit, not cumulative
3. ✅ **Maintainable**: One source of truth for profit calculation
4. ✅ **Reliable**: No more discrepancies between endpoints

---

## 🔍 Understanding the Difference

### Cumulative Profit (Wrong for Monthly Display)

```
Project X:
Jan 2024: profit_asset_cumulative = $1,000
Feb 2024: profit_asset_cumulative = $2,500 (total so far)
Mar 2024: profit_asset_cumulative = $3,800 (total so far)
...
Nov 2025: profit_asset_cumulative = $51,039 (total from beginning)
```

**If we use cumulative**: Shows $51,039 for November ❌

### Monthly Profit (Correct)

```
Project X Monthly Profit:
Jan 2024: $1,000 (first month)
Feb 2024: $1,500 ($2,500 - $1,000)
Mar 2024: $1,300 ($3,800 - $2,500)
...
Nov 2025: $25 (change from Oct to Nov)
```

**If we calculate monthly**: Shows $25 for November ✅

---

## 📝 Technical Details

### What `calculateCompanyProfitByMonth()` Does

**For each project**:
1. Gets previous month's evaluation
2. Gets current month's evaluation
3. Calculates: `profit = current - previous + revenue - expense`

**For all projects**:
1. Sums all projects' monthly profits
2. Returns by month: `['2025-11' => ['asset' => $X, 'operation' => $Y, 'total' => $Z]]`

### Formula Breakdown

```
Company Monthly Asset Profit:
= Sum of all projects (
    Current Month Evaluation 
    - Previous Month Evaluation 
    + Revenue Asset 
    - Expense Asset
  )

User Monthly Profit:
= User's Previous Month Equity % × Company Monthly Profit
```

---

## 🚀 Testing

### Test the API

```bash
# Call home API
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://yourdomain.com/api/home/dashboard
```

### Verify Results

Check that `financial_summary.profit.total` matches the last item in `historical_data`:

```json
{
  "financial_summary": {
    "profit": {
      "total": 25.35  // ← Should match below
    }
  },
  "historical_data": [
    ...
    {
      "month": "2025-11",
      "total_profit": 25.35  // ← Should match above
    }
  ]
}
```

---

## ✅ Summary

**Problem**: Used `profit_asset_cumulative` (total from beginning) instead of monthly profit  
**Fix**: Use `calculateCompanyProfitByMonth()` for consistent monthly calculations  
**Result**: `financial_summary` and `historical_data` now show matching values

**File Changed**: `app/Http/Controllers/Api/HomeController.php` (lines 164-184)

**Impact**: Home API now shows correct monthly profit values! 🎉
