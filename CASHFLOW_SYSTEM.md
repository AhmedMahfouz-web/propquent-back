# Cashflow Analysis System

## Overview
The Cashflow Analysis System provides comprehensive financial tracking and visualization for PropQuent projects with optimized performance for large datasets.

## Key Features

### 1. Current Available Cash Calculation
**Formula**: `∑ Done Revenue + ∑ Done Deposits - ∑ Done Expenses - ∑ Done Withdrawals`

### 2. Month-by-Month Analysis
- Revenue vs Expenses tracking
- Deposits vs Withdrawals monitoring  
- Running balance calculation
- Interactive charts with Chart.js

### 3. Project-Level Cashflow
- Individual project net cashflow
- Unpaid installment tracking
- Next installment due dates
- Transaction history

### 4. Performance Optimizations
- **Database Indexes**: Added composite indexes for optimal query performance
- **Caching**: 5-15 minute cache layers for expensive calculations
- **Query Optimization**: Raw SQL for aggregations, EXISTS instead of JOINs
- **Lazy Loading**: Components load data on demand

## Database Indexes Added
```sql
-- Project Transactions
idx_project_status_type: (project_key, status, financial_type)
idx_transaction_date_status: (transaction_date, status)
idx_status_date: (status, transaction_date)
idx_type_status_date: (financial_type, status, transaction_date)

-- User Transactions  
idx_user_type_status: (transaction_type, status)
idx_user_date_status: (transaction_date, status)
idx_user_type_status_date: (transaction_type, status, transaction_date)

-- Projects
idx_project_status: (status)
idx_project_developer: (developer_id)
idx_project_status_developer: (status, developer_id)
```

## Files Created

### Core Resource
- `app/Filament/Resources/CashflowResource.php` - Main resource with optimized queries
- `app/Filament/Resources/CashflowResource/Pages/ListCashflows.php` - List page with tabs and filters
- `app/Filament/Resources/CashflowResource/Pages/ViewCashflow.php` - Detailed project view

### Widgets
- `app/Filament/Resources/CashflowResource/Widgets/CashflowOverviewWidget.php` - Summary stats
- `app/Filament/Resources/CashflowResource/Widgets/MonthlyCashflowChartWidget.php` - Interactive chart

### Views
- `resources/views/filament/resources/cashflow/monthly-breakdown.blade.php` - Monthly data table
- `resources/views/filament/resources/cashflow/installment-schedule.blade.php` - Upcoming payments
- `resources/views/filament/resources/cashflow/transaction-history.blade.php` - Transaction list

### Database
- `database/migrations/2024_01_15_000000_add_cashflow_performance_indexes.php` - Performance indexes
- `app/Console/Commands/TestCashflowCommand.php` - System testing command

## Usage

### Accessing the Cashflow System
Navigate to `/admin/cashflows` in your Filament admin panel.

### Key Metrics Displayed
1. **Current Available Cash** - Real-time company cash position
2. **Total Revenue** - All completed revenue transactions
3. **Total Expenses** - All completed expense transactions  
4. **Net Project Cashflow** - Revenue minus expenses
5. **Total Deposits** - All user deposits
6. **Total Withdrawals** - All user withdrawals

### Interactive Features
- **Filters**: Status, developer, date range
- **Tabs**: All projects, active, profitable, loss-making
- **Sorting**: By net cashflow, unpaid installments
- **Auto-refresh**: Every 30 seconds
- **Cache refresh**: Manual cache clearing button

### Project Details View
- Monthly breakdown with profit/loss indicators
- Installment schedule with overdue alerts
- Complete transaction history
- Summary cards for quick insights

## Performance Considerations

### Cache Strategy
- Company summary: 10 minutes
- Monthly data: 15 minutes  
- Query results: 5 minutes
- Manual refresh available

### Query Optimization
- Uses raw SQL for complex aggregations
- Composite indexes for multi-column filters
- EXISTS subqueries instead of expensive JOINs
- Selective column loading

### Memory Management
- Pagination for large datasets
- Limited result sets (50 transactions max in views)
- Efficient data structures
- Lazy loading components

## Testing
Run the test command to verify performance and data consistency:
```bash
php artisan cashflow:test
```

## Navigation
The Cashflow Analysis appears in the main navigation with a chart bar icon, sorted at position 6.

## Status Indicators
- **Green**: Profitable projects/positive cashflow
- **Red**: Loss-making projects/negative cashflow  
- **Yellow**: Pending transactions/upcoming due dates
- **Blue**: Informational metrics

This system provides comprehensive financial visibility while maintaining excellent performance even with large transaction datasets.
