# Home API - Cash and Asset Fields

## 🎯 New Fields Added

Added two new fields to the `/api/home/dashboard` response that show the user's share of company cash and assets:

1. **`cash`** - User's share of company cash balance
2. **`asset`** - User's share of company asset evaluation

---

## 📊 API Response Structure

### New Fields in `financial_summary`

```json
{
  "financial_summary": {
    "equity": { ... },
    "profit": { ... },
    "roi": { ... },
    "deposits_withdrawals": { ... },
    
    "cash": 512.00,
    "asset": 6075.44
  }
}
```

---

## 🧮 Calculation Formula

Both fields use the same formula:

```
User's Value = User Equity % × Company Total Value
```

### Cash Calculation

```
User Cash = (User Equity % / 100) × Company Cash Balance
```

**Example**:
- User Equity %: 0.51%
- Company Cash: $100,000
- User Cash: (0.51 / 100) × $100,000 = **$510**

### Asset Calculation

```
User Asset = (User Equity % / 100) × Company Asset Evaluation
```

**Example**:
- User Equity %: 0.51%
- Company Asset Evaluation: $19,000,000
- User Asset: (0.51 / 100) × $19,000,000 = **$96,900**

---

## 🔍 How It Works

### 1. Get User's Equity Percentage

```php
$userEquityPercentage = $this->getUserEquityPercentage($userId, $endDate);
// Returns: 0.51 (meaning 0.51%)

$equityFraction = $userEquityPercentage / 100;
// Converts to: 0.0051 (for multiplication)
```

### 2. Get Company Cash Balance

```php
$companyCash = $this->getCompanyCash($currentMonth, $endDate);
```

**Tries two methods**:

**Method 1 - Cached (Fast)**:
```php
// Check MonthlyCashBalance table
$cachedCash = MonthlyCashBalance::where('month_date', $month)->first();
if ($cachedCash) {
    return $cachedCash->cash_balance;
}
```

**Method 2 - Calculated (Fallback)**:
```php
// Calculate from transactions
Cash = Deposits - Withdrawals + Revenue - Expenses
```

### 3. Get Company Asset Evaluation

```php
$companyAssetEvaluation = MonthlyProjectEvaluation::where('month_date', $currentMonth)
    ->sum('asset_evaluation');
```

Sums all projects' asset evaluations from the pre-calculated database.

### 4. Apply User's Percentage

```php
return [
    'cash' => $equityFraction * $companyCash,
    'asset' => $equityFraction * $companyAssetEvaluation
];
```

---

## 📈 Complete Example

### User Data

**Equity**:
- Deposits: $150,000
- Withdrawals: $50,000
- Net Equity: $100,000

**Equity %**:
- Company Total Equity: $19,600,000
- User Equity %: (100,000 / 19,600,000) × 100 = **0.51%**

### Company Data (Current Month)

**Cash Balance**:
- Total Deposits: $2,000,000
- Total Withdrawals: $500,000
- Total Revenue: $800,000
- Total Expenses: $2,200,000
- **Company Cash**: $100,000

**Asset Evaluation**:
- Sum of all projects' evaluations: **$19,500,000**

### User's Share

**Cash**:
```
User Cash = 0.51% × $100,000
         = 0.0051 × $100,000
         = $510
```

**Asset**:
```
User Asset = 0.51% × $19,500,000
          = 0.0051 × $19,500,000
          = $99,450
```

### API Response

```json
{
  "financial_summary": {
    "equity": {
      "amount": 100000,
      "percentage": 0.51
    },
    "cash": 510.00,
    "asset": 99450.00
  }
}
```

---

## ✅ Verification

### Check 1: Cash + Asset ≈ User Equity × Company Total Equity

```javascript
const userCash = financialSummary.cash;
const userAsset = financialSummary.asset;
const userTotal = userCash + userAsset;

const equityFraction = financialSummary.equity.percentage / 100;
const companyTotal = companyCash + companyAssetEvaluation;
const expectedTotal = equityFraction * companyTotal;

console.assert(Math.abs(userTotal - expectedTotal) < 1);
// ✅ Pass: User's cash + asset equals their share of company total
```

### Check 2: Equity Breakdown

```javascript
// User's total equity should match their deposits - withdrawals
const calculatedEquity = userCash + userAsset;
const actualEquity = financialSummary.equity.amount;

// May differ slightly due to rounding or timing
console.log(`Calculated: ${calculatedEquity}`);
console.log(`Actual: ${actualEquity}`);
```

### Check 3: Percentage Application

```javascript
// Verify percentage is correctly applied
const userEquityPercent = financialSummary.equity.percentage;
const companyCashValue = 100000; // Example

const expectedUserCash = (userEquityPercent / 100) * companyCashValue;
const actualUserCash = financialSummary.cash;

console.assert(Math.abs(expectedUserCash - actualUserCash) < 0.01);
// ✅ Pass: Percentage correctly applied
```

---

## 🔧 Technical Implementation

### Functions Added

**1. `calculateUserCashAndAssets()`**
```php
private function calculateUserCashAndAssets(int $userId, Carbon $endDate): array
{
    // Get user equity %
    $userEquityPercentage = $this->getUserEquityPercentage($userId, $endDate);
    $equityFraction = $userEquityPercentage / 100;
    
    // Get company values
    $companyCash = $this->getCompanyCash($currentMonth, $endDate);
    $companyAssetEvaluation = MonthlyProjectEvaluation::where('month_date', $currentMonth)
        ->sum('asset_evaluation');
    
    // Apply percentage
    return [
        'cash' => $equityFraction * $companyCash,
        'asset' => $equityFraction * $companyAssetEvaluation
    ];
}
```

**2. `getCompanyCash()`**
```php
private function getCompanyCash(string $month, Carbon $endDate): float
{
    // Try cached first
    $cachedCash = MonthlyCashBalance::where('month_date', $month)->first();
    if ($cachedCash) {
        return (float) $cachedCash->cash_balance;
    }
    
    // Calculate manually if cache missing
    return $allDeposits - $allWithdrawals + $allRevenue - $allExpenses;
}
```

### Database Dependencies

**Tables Used**:
1. `MonthlyCashBalance` - Cached cash balance per month
2. `MonthlyProjectEvaluation` - Pre-calculated asset evaluations
3. `UserTransaction` - Deposits and withdrawals
4. `ProjectTransaction` - Revenue and expenses

**Commands**:
- `php artisan evaluations:calculate` - Updates MonthlyProjectEvaluation
- Cash balance is updated automatically via observers

---

## 📊 Use Cases

### Portfolio Breakdown

Show user how their investment is distributed:

```
Your $100,000 Investment Breakdown:
├── Cash:   $510    (0.5%)
└── Assets: $99,450 (99.5%)
```

### Asset Allocation

Compare cash vs assets:

```javascript
const cashPercent = (financialSummary.cash / financialSummary.equity.amount) * 100;
const assetPercent = (financialSummary.asset / financialSummary.equity.amount) * 100;

console.log(`Cash Allocation: ${cashPercent.toFixed(2)}%`);
console.log(`Asset Allocation: ${assetPercent.toFixed(2)}%`);
```

### Liquidity Analysis

Determine user's liquid vs illiquid assets:

```javascript
const cash = financialSummary.cash;
const asset = financialSummary.asset;
const liquidityRatio = cash / (cash + asset);
console.log(`Liquidity Ratio: ${(liquidityRatio * 100).toFixed(2)}%`);
```

---

## 🎯 Key Points

### What These Fields Show

- **`cash`**: User's share of company's liquid cash
- **`asset`**: User's share of company's property/asset value

### How They're Calculated

1. Get user's equity percentage (based on deposits - withdrawals)
2. Get company totals (cash and asset evaluation)
3. Multiply: User % × Company Value

### Why They're Useful

- Shows portfolio composition (cash vs assets)
- Helps users understand their investment distribution
- Provides transparency into company holdings
- Enables liquidity analysis

### Relationship to Equity

```
User Equity = User Cash + User Assets (approximately)
```

May differ slightly due to:
- Timing of transactions
- Rounding
- Pending transactions
- Calculation method differences

---

## 📝 Files Changed

- **File**: `app/Http/Controllers/Api/HomeController.php`
- **Functions Added**:
  - `calculateUserCashAndAssets()` - Main calculation
  - `getCompanyCash()` - Get company cash balance
- **Response Updated**:
  - Added `cash` (raw number) to `financial_summary`
  - Added `asset` (raw number) to `financial_summary`

---

## ✅ Summary

**Added**: Two new fields showing user's share of company cash and assets  
**Formula**: User Equity % × Company Value  
**Purpose**: Show portfolio composition and investment distribution

**Now users can see exactly how their investment is split between cash and assets!** 🎉
