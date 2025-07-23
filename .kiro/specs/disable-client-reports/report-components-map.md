# Report Components Map

This document maps all the components related to the client reports that need to be disabled.

## Report Pages

1. **Client Portfolio Report**

    - Class: `App\Filament\Pages\Reports\ClientPortfolioReport`
    - Navigation Group: Reports
    - Navigation Icon: chart-pie
    - Related Requirements: 1.1, 1.3

2. **Client Statement Report**

    - Class: `App\Filament\Pages\Reports\ClientStatementReport`
    - Navigation Group: Reports
    - Navigation Icon: document-text
    - Related Requirements: 1.1, 1.3

3. **Investment Performance Report**

    - Class: `App\Filament\Pages\Reports\InvestmentPerformanceReport`
    - Navigation Group: Reports
    - Navigation Icon: chart-bar
    - Related Requirements: 1.1, 1.3

4. **Profit Distribution Report**

    - Class: `App\Filament\Pages\Reports\ProfitDistributionReport`
    - Navigation Group: Reports
    - Navigation Icon: currency-dollar
    - Related Requirements: 1.1, 1.3

5. **Property Performance Report**
    - Class: `App\Filament\Pages\Reports\PropertyPerformanceReport`
    - Navigation Group: Reports
    - Navigation Icon: home
    - Related Requirements: 1.1, 1.3

## Widgets

1. **Client Portfolio Overview Widget**

    - Class: `App\Filament\Widgets\ClientPortfolioOverviewWidget`
    - Related Report: Client Portfolio Report
    - Related Requirements: 1.2

2. **Investment Performance Widget**

    - Class: `App\Filament\Widgets\InvestmentPerformanceWidget`
    - Related Report: Investment Performance Report
    - Related Requirements: 1.2

3. **Property Performance Widget**

    - Class: `App\Filament\Widgets\PropertyPerformanceWidget`
    - Related Report: Property Performance Report
    - Related Requirements: 1.2

4. **Recent Client Activity Widget**
    - Class: `App\Filament\Widgets\RecentClientActivityWidget`
    - Related to: Client reports in general
    - Related Requirements: 1.2

## Configuration Keys

Based on the identified components, here are the configuration keys that will be used to control their visibility:

1. `client_portfolio` - Controls Client Portfolio Report and related widgets
2. `client_statement` - Controls Client Statement Report
3. `investment_performance` - Controls Investment Performance Report and related widgets
4. `profit_distribution` - Controls Profit Distribution Report
5. `property_performance` - Controls Property Performance Report and related widgets
6. `client_investments` - Controls client investment functionality in general

## Dependencies

Some potential dependencies between reports and other system components:

1. The dashboard may reference these widgets directly
2. Other reports may use data or services from these reports
3. Navigation may have hardcoded references to these reports

These dependencies will need to be handled with conditional logic to prevent errors when the reports are disabled.
