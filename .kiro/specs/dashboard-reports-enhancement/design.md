# Design Document

## Overview

This design document outlines the comprehensive enhancement of the Laravel-Filament real estate investment management system. The enhancement transforms the platform into a sophisticated client-facing investment backend that enables property investment companies to track client investments, calculate profits, generate professional reports, and provide transparent investment performance data.

The design focuses on four core areas: (1) implementing a robust client investment reporting system with multiple report types and advanced analytics, (2) completely redesigning the dashboard with custom investment-focused widgets, (3) adding a project key field alongside existing UUIDs for better client communication, and (4) integrating advanced client management features with profit calculation capabilities.

## Architecture

### System Architecture Overview

The enhanced system maintains the existing Laravel-Filament architecture while adding new layers for client investment management:

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Filament Admin Panel  │  Custom Report Pages  │  API Layer │
├─────────────────────────────────────────────────────────────┤
│                    Business Logic Layer                     │
├─────────────────────────────────────────────────────────────┤
│ Investment Services │ Report Services │ Client Services     │
├─────────────────────────────────────────────────────────────┤
│                    Data Access Layer                        │
├─────────────────────────────────────────────────────────────┤
│    Eloquent Models    │    Repositories    │   Caching      │
├─────────────────────────────────────────────────────────────┤
│                    Database Layer                           │
├─────────────────────────────────────────────────────────────┤
│  Projects │ Clients │ Investments │ Transactions │ Reports  │
└─────────────────────────────────────────────────────────────┘
```

### Key Architectural Principles

1. **Separation of Concerns**: Clear separation between client management, investment tracking, and reporting functionality
2. **Service-Oriented Design**: Business logic encapsulated in dedicated service classes
3. **Repository Pattern**: Data access abstracted through repository interfaces
4. **Event-Driven Architecture**: Investment calculations and client notifications triggered by events
5. **Caching Strategy**: Performance optimization through strategic caching of calculations and reports

## Components and Interfaces

### 1. Client Investment Reporting System

#### Report Pages Structure

```
resources/views/filament/pages/reports/
├── ClientPortfolioReport.php
├── InvestmentPerformanceReport.php
├── ProfitDistributionReport.php
├── PropertyPerformanceReport.php
├── ClientStatementReport.php
└── InvestmentAnalyticsReport.php
```

#### Report Service Architecture

```php
interface ReportServiceInterface
{
    public function generateClientPortfolioReport(int $clientId, array $filters): ReportData;
    public function generateInvestmentPerformanceReport(array $filters): ReportData;
    public function generateProfitDistributionReport(int $clientId, string $period): ReportData;
    public function exportReport(ReportData $report, string $format): ExportResult;
}

class InvestmentReportService implements ReportServiceInterface
{
    public function __construct(
        private ClientRepository $clientRepository,
        private InvestmentRepository $investmentRepository,
        private ProfitCalculationService $profitService,
        private CacheManager $cache
    ) {}
}
```

#### Report Data Models

```php
class ReportData
{
    public function __construct(
        public string $title,
        public array $data,
        public array $charts,
        public array $summary,
        public Carbon $generatedAt
    ) {}
}

class ClientPortfolioData
{
    public function __construct(
        public int $clientId,
        public string $clientName,
        public Collection $investments,
        public Money $totalInvested,
        public Money $currentValue,
        public Money $totalProfit,
        public float $returnPercentage
    ) {}
}
```

### 2. Custom Dashboard Widgets

#### Widget Architecture

```php
abstract class InvestmentWidget extends BaseWidget
{
    protected function getClientData(): array;
    protected function getInvestmentMetrics(): array;
    protected function formatCurrency(float $amount): string;
}

class ClientPortfolioWidget extends InvestmentWidget
{
    protected function getStats(): array
    {
        return [
            'total_clients' => $this->getTotalClients(),
            'total_investments' => $this->getTotalInvestments(),
            'portfolio_value' => $this->getPortfolioValue(),
            'profit_distribution' => $this->getProfitDistribution()
        ];
    }
}
```

#### Widget Components

1. **ClientPortfolioOverviewWidget**: Displays client distribution and investment amounts
2. **InvestmentPerformanceWidget**: Shows portfolio performance metrics and returns
3. **PropertyPerformanceWidget**: Tracks individual property performance and yields
4. **RecentClientActivityWidget**: Lists recent client investments and transactions
5. **ProfitDistributionWidget**: Monitors profit payments and distribution schedules
6. **InvestmentAlertsWidget**: Displays critical alerts and notifications

### 3. Project Key Enhancement

#### Database Schema Enhancement

```sql
ALTER TABLE projects ADD COLUMN project_key VARCHAR(50) UNIQUE NULL AFTER id;
CREATE INDEX idx_projects_project_key ON projects(project_key);
```

#### Model Enhancement

```php
class Project extends Model implements HasMedia
{
    protected $fillable = [
        'id',
        'project_key', // New field
        'key',
        'title',
        // ... existing fields
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->id)) {
                $project->id = (string) Str::uuid();
            }
            if (empty($project->key)) {
                $project->key = $project->id;
            }
            // Project key remains optional and user-defined
        });
    }

    public function getDisplayIdentifier(): string
    {
        return $this->project_key ?? $this->id;
    }
}
```

### 4. Client Management System

#### Client Model Structure

```php
class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'investment_preferences',
        'risk_profile',
        'kyc_status',
        'communication_preferences',
        'is_active'
    ];

    protected $casts = [
        'investment_preferences' => 'array',
        'communication_preferences' => 'array',
        'kyc_status' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(ClientInvestment::class);
    }

    public function profitDistributions(): HasMany
    {
        return $this->hasMany(ProfitDistribution::class);
    }
}
```

#### Investment Tracking Models

```php
class ClientInvestment extends Model
{
    protected $fillable = [
        'client_id',
        'project_id',
        'investment_amount',
        'investment_date',
        'investment_type',
        'expected_return',
        'status'
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'investment_date' => 'date'
    ];
}

class ProfitDistribution extends Model
{
    protected $fillable = [
        'client_id',
        'investment_id',
        'distribution_amount',
        'distribution_date',
        'distribution_type',
        'status'
    ];
}
```

## Data Models

### Enhanced Database Schema

#### New Tables

```sql
-- Client management
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    investment_preferences JSON,
    risk_profile ENUM('conservative', 'moderate', 'aggressive'),
    kyc_status BOOLEAN DEFAULT FALSE,
    communication_preferences JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Client investments
CREATE TABLE client_investments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    project_id VARCHAR(36) NOT NULL,
    investment_amount DECIMAL(15,2) NOT NULL,
    investment_date DATE NOT NULL,
    investment_type ENUM('equity', 'debt', 'hybrid') NOT NULL,
    expected_return DECIMAL(5,2),
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- Profit distributions
CREATE TABLE profit_distributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    investment_id BIGINT UNSIGNED,
    distribution_amount DECIMAL(15,2) NOT NULL,
    distribution_date DATE NOT NULL,
    distribution_type ENUM('dividend', 'capital_gain', 'rental_income') NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (investment_id) REFERENCES client_investments(id)
);

-- Report cache
CREATE TABLE report_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(100) NOT NULL,
    report_key VARCHAR(255) NOT NULL,
    report_data JSON NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_report_cache_key (report_type, report_key),
    INDEX idx_report_cache_expires (expires_at)
);
```

#### Modified Tables

```sql
-- Add project_key to existing projects table
ALTER TABLE projects ADD COLUMN project_key VARCHAR(50) UNIQUE NULL AFTER id;
CREATE INDEX idx_projects_project_key ON projects(project_key);
```

### Data Relationships

```
Client (1) ──── (N) ClientInvestment ──── (1) Project
   │                      │
   │                      │
   └── (N) ProfitDistribution ──── (1) ClientInvestment
```

## Error Handling

### Investment Calculation Errors

```php
class InvestmentCalculationException extends Exception
{
    public static function invalidInvestmentAmount(float $amount): self
    {
        return new self("Invalid investment amount: {$amount}");
    }

    public static function missingProfitData(int $investmentId): self
    {
        return new self("Missing profit data for investment ID: {$investmentId}");
    }
}
```

### Report Generation Errors

```php
class ReportGenerationException extends Exception
{
    public static function insufficientData(string $reportType): self
    {
        return new self("Insufficient data to generate {$reportType} report");
    }

    public static function exportFailed(string $format, string $reason): self
    {
        return new self("Failed to export report in {$format} format: {$reason}");
    }
}
```

### Error Handling Strategy

1. **Graceful Degradation**: Reports show available data with clear indicators for missing information
2. **User-Friendly Messages**: Technical errors translated to business-friendly language
3. **Logging and Monitoring**: Comprehensive error logging for debugging and monitoring
4. **Fallback Mechanisms**: Alternative data sources when primary calculations fail

## Testing Strategy

### Unit Testing

```php
class InvestmentCalculationServiceTest extends TestCase
{
    public function test_calculates_client_portfolio_value(): void
    {
        // Test individual client portfolio calculations
    }

    public function test_calculates_profit_distributions(): void
    {
        // Test profit distribution calculations
    }

    public function test_handles_missing_investment_data(): void
    {
        // Test error handling for missing data
    }
}
```

### Integration Testing

```php
class ReportGenerationTest extends TestCase
{
    public function test_generates_client_portfolio_report(): void
    {
        // Test end-to-end report generation
    }

    public function test_exports_report_in_multiple_formats(): void
    {
        // Test report export functionality
    }
}
```

### Feature Testing

```php
class DashboardWidgetTest extends TestCase
{
    public function test_dashboard_displays_investment_widgets(): void
    {
        // Test custom widget display
    }

    public function test_dashboard_excludes_default_widgets(): void
    {
        // Test removal of default Filament widgets
    }
}
```

### Testing Approach

1. **Test-Driven Development**: Write tests before implementing features
2. **Mock External Dependencies**: Use mocks for database and external services
3. **Data Factories**: Create realistic test data using Laravel factories
4. **Performance Testing**: Ensure report generation performs well with large datasets
5. **Security Testing**: Validate client data access controls and permissions
