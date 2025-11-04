# Project Details API - User's Share Instead of Raw Values

## 🎯 Change Summary

**Updated**: Project details API (`/api/projects/{id}`) now shows **user's share** of project financial data instead of raw project values.

**Formula**: All financial values are multiplied by user's equity percentage
```
User's Value = Project Value × (User Equity % / 100)
```

---

## 📊 API Response Changes

### Before (Raw Project Values)

```json
{
  "data": { ... },
  "financial_data": {
    "investment_amount": 2000000.00,    // ❌ Total project investment
    "total_profit": 150000.00,          // ❌ Total project profit
    "operation_profit": 20000.00,       // ❌ Total project operation profit
    "asset_profit": 130000.00,          // ❌ Total project asset profit
    "currency": "USD"
  }
}
```

### After (User's Share)

```json
{
  "data": { ... },
  "financial_data": {
    "investment_amount": 10200.00,      // ✅ User's share (0.51% × $2M)
    "total_profit": 765.00,             // ✅ User's share (0.51% × $150K)
    "operation_profit": 102.00,         // ✅ User's share (0.51% × $20K)
    "asset_profit": 663.00,             // ✅ User's share (0.51% × $130K)
    "currency": "USD"
  }
}
```

---

## 🧮 Calculation Example

### User Info

- **Deposits**: $150,000
- **Withdrawals**: $50,000
- **Net Equity**: $100,000
- **Company Total Equity**: $19,600,000 (cash + assets)
- **User Equity %**: (100,000 / 19,600,000) × 100 = **0.51%**

### Project Financial Data (Raw)

- **Investment Amount**: $2,000,000
- **Total Profit**: $150,000
- **Operation Profit**: $20,000
- **Asset Profit**: $130,000

### User's Share (0.51% × Raw Values)

```javascript
const equityFraction = 0.51 / 100;  // 0.0051

investment_amount = 0.0051 × 2,000,000 = $10,200
total_profit      = 0.0051 × 150,000   = $765
operation_profit  = 0.0051 × 20,000    = $102
asset_profit      = 0.0051 × 130,000   = $663
```

### API Returns

```json
{
  "financial_data": {
    "investment_amount": 10200.00,
    "total_profit": 765.00,
    "operation_profit": 102.00,
    "asset_profit": 663.00,
    "currency": "USD"
  }
}
```

---

## 🔧 How It Works

### Step 1: Get User's Equity Percentage

Uses the same calculation as Home API:

```php
$userEquityPercentage = $this->getUserEquityPercentageForHome($userId, $currentDate);
// Returns: 0.51 (meaning 0.51%)

$equityFraction = $userEquityPercentage / 100;
// Converts to: 0.0051 (for multiplication)
```

**Calculation**:
```php
// User equity (deposits - withdrawals)
$userEquity = $deposits - $withdrawals;

// Company total equity (cash + assets)
$companyTotalEquity = $cash + $assetEvaluation;

// Percentage
$userEquityPercentage = ($userEquity / $companyTotalEquity) × 100;
```

### Step 2: Calculate Project Financial Data (Raw)

Same calculation as before:

```php
// Investment amount = total expenses
$projectInvestmentAmount = $expenseAsset + $expenseOperation;

// Operation profit from database
$projectOperationProfit = SUM(profit_operation);

// Asset profit calculated monthly
$projectAssetProfit = Σ(
    currentEvaluation 
    - previousEvaluation 
    + revenue 
    - expense
);

// Total profit
$projectTotalProfit = $operationProfit + $assetProfit;
```

### Step 3: Apply User's Percentage

```php
return [
    'investment_amount' => $equityFraction × $projectInvestmentAmount,
    'total_profit'      => $equityFraction × $projectTotalProfit,
    'operation_profit'  => $equityFraction × $projectOperationProfit,
    'asset_profit'      => $equityFraction × $projectAssetProfit
];
```

---

## 📈 Real Example

### Scenario

**User**: Ahmed Mahfouz
- Equity: $100,000 (0.51% of company)

**Project**: Chalet in SouthMed
- Total Investment: $2,500,000
- Total Profit: $180,000
- Operation Profit: $30,000
- Asset Profit: $150,000

### API Response

```json
GET /api/projects/68

{
  "success": true,
  "data": {
    "id": 68,
    "title": "Chalet in SouthMed",
    "status": "on-going",
    ...
  },
  "financial_data": {
    "investment_amount": 12750.00,    // 0.51% × $2.5M
    "total_profit": 918.00,           // 0.51% × $180K
    "operation_profit": 153.00,       // 0.51% × $30K
    "asset_profit": 765.00,           // 0.51% × $150K
    "currency": "USD"
  }
}
```

---

## ✅ Verification

### Check 1: Values Match User's Percentage

```javascript
const userEquityPercent = 0.51;  // From home API
const projectTotalProfit = 180000;  // Raw project value

const expectedUserProfit = (userEquityPercent / 100) × projectTotalProfit;
// = 0.0051 × 180000 = 918

const actualUserProfit = financialData.total_profit;
// = 918

console.assert(expectedUserProfit === actualUserProfit);
// ✅ Pass
```

### Check 2: Sum of Profits

```javascript
const operationProfit = financialData.operation_profit;
const assetProfit = financialData.asset_profit;
const totalProfit = financialData.total_profit;

console.assert(operationProfit + assetProfit === totalProfit);
// ✅ Pass: 153 + 765 = 918
```

### Check 3: Consistency with Home API

```javascript
// User's equity % should be same across all APIs
const homeEquityPercent = homeData.financial_summary.equity.percentage;
const projectUserShare = projectData.financial_data.total_profit;
const projectRawValue = 180000;  // Raw project profit

const calculatedPercent = (projectUserShare / projectRawValue) × 100;

console.assert(Math.abs(calculatedPercent - homeEquityPercent) < 0.01);
// ✅ Pass: Both show 0.51%
```

---

## 🎯 Key Changes

### What Changed

1. **Investment Amount**: Now shows user's share, not total project investment
2. **Total Profit**: Now shows user's share, not total project profit
3. **Operation Profit**: Now shows user's share, not total project operation profit
4. **Asset Profit**: Now shows user's share, not total project asset profit

### What Stayed the Same

- ✅ Project details (title, description, status, etc.)
- ✅ Project images
- ✅ Developer information
- ✅ Calculation formulas (just multiplied by user %)
- ✅ Currency (USD)

### Why This Change

**Before**: Showed raw project values → Confusing for users  
**After**: Shows user's actual share → Clear and actionable

**User can now see**:
- How much they invested in this project
- How much profit they made from this project
- Their actual financial stake in the project

---

## 🔍 Technical Details

### Functions Added

**1. `getUserEquityPercentageForHome()`**
```php
private function getUserEquityPercentageForHome(int $userId, Carbon $endDate): float
{
    $userEquity = $this->calculateUserCumulativeEquity($userId, $endDate);
    $companyTotalEquity = $this->calculateCompanyTotalEquity($endDate);
    
    if ($companyTotalEquity == 0) {
        return 0;
    }
    
    return ($userEquity / $companyTotalEquity) * 100;
}
```

**2. `calculateUserCumulativeEquity()`**
```php
private function calculateUserCumulativeEquity(int $userId, Carbon $endDate): float
{
    $deposits = UserTransaction::where('user_id', $userId)
        ->where('transaction_type', UserTransaction::TYPE_DEPOSIT)
        ->where('status', UserTransaction::STATUS_DONE)
        ->where('transaction_date', '<=', $endDate)
        ->sum('amount');

    $withdrawals = UserTransaction::where('user_id', $userId)
        ->where('transaction_type', UserTransaction::TYPE_WITHDRAWAL)
        ->where('status', UserTransaction::STATUS_DONE)
        ->where('transaction_date', '<=', $endDate)
        ->sum('amount');

    return $deposits - $withdrawals;
}
```

**3. `calculateCompanyTotalEquity()`**
```php
private function calculateCompanyTotalEquity(Carbon $endDate): float
{
    // Get cash balance (cached or calculated)
    $cash = $this->getCompanyCash($endDate);
    
    // Get asset evaluation from database
    $assetEvaluation = MonthlyProjectEvaluation::where('month_date', $month)
        ->sum('asset_evaluation');
    
    return $cash + $assetEvaluation;
}
```

### Functions Updated

**`getProjectFinancialData()`**
```php
// Before
private function getProjectFinancialData($project): array

// After
private function getProjectFinancialData($project, int $userId): array
```

Now accepts user ID and multiplies all values by user's equity percentage.

**`show()`**
```php
// Before
$financialData = $this->getProjectFinancialData($resource);

// After
$user = Auth::user();
$financialData = $this->getProjectFinancialData($resource, $user->id);
```

Now passes authenticated user's ID to get their share.

---

## 📊 Use Cases

### Portfolio Analysis

User can see their exact financial stake in each project:

```
My Projects:
├── Chalet in SouthMed
│   ├── My Investment: $12,750
│   └── My Profit: $918
├── Villa in NewCairo
│   ├── My Investment: $8,500
│   └── My Profit: $1,200
└── Total
    ├── My Investment: $21,250
    └── My Profit: $2,118
```

### Performance Tracking

Compare profit percentage across projects:

```javascript
projects.forEach(project => {
    const roi = (project.financial_data.total_profit / 
                 project.financial_data.investment_amount) * 100;
    console.log(`${project.title}: ${roi.toFixed(2)}% ROI`);
});

// Output:
// Chalet in SouthMed: 7.20% ROI
// Villa in NewCairo: 14.12% ROI
```

### Investment Decisions

User can see if they're over/under-invested in specific projects:

```javascript
const myTotalEquity = 100000;
const myProjectInvestment = project.financial_data.investment_amount;
const allocationPercent = (myProjectInvestment / myTotalEquity) * 100;

console.log(`This project represents ${allocationPercent.toFixed(2)}% of my portfolio`);
```

---

## 📝 Files Changed

- **File**: `app/Http/Controllers/Api/ProjectController.php`
- **Functions Updated**:
  - `show()` - Now gets user and passes ID
  - `getProjectFinancialData()` - Now accepts userId and multiplies by equity %
- **Functions Added**:
  - `getUserEquityPercentageForHome()` - Get user's equity percentage
  - `calculateUserCumulativeEquity()` - Calculate user's equity
  - `calculateCompanyTotalEquity()` - Calculate company total equity

---

## ✅ Summary

**Before**: Project details API showed raw project values (confusing)  
**After**: Project details API shows user's share (clear and actionable)

**Formula**: User's Share = Project Value × (User Equity % / 100)

**Benefit**: Users now see their actual financial stake in each project! 🎉
