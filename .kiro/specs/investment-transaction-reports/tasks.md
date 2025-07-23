# Implementation Plan

-   [x] 1. Analyze existing report structure and identify modification points

    -   Review current report implementations in the codebase
    -   Identify which reports need to be modified or extended
    -   Determine reusable components from existing reports
    -   Map current data structures to new reporting requirements
    -   _Requirements: 1.1, 1.2, 2.1, 2.2_

-   [x] 2. Enhance database schema for user transactions and project transactions

    -   Update or extend existing transaction tables to support deposit/withdrawal tracking
    -   Add transaction_category and transaction_type columns to project_transactions table if not present
    -   Create indexes for efficient querying of transaction data
    -   Ensure backward compatibility with existing reports
    -   _Requirements: 1.1, 1.2, 2.1, 2.2_

-   [x] 3. Extend existing transaction models and repositories

    -   Update UserTransaction model with deposit/withdrawal functionality
    -   Extend ProjectTransaction model with enhanced categorization
    -   Add methods to existing repositories for new reporting queries
    -   Implement caching for frequently accessed report data
    -   _Requirements: 1.1, 1.2, 2.1, 2.2_

-   [x] 4. Implement User Investment Report functionality

    -   Extend existing report service with monthly deposit/withdrawal calculation
    -   Add net deposit calculation for each month
    -   Create active investor determination based on net deposit history
    -   Implement data aggregation for monthly investment summaries
    -   _Requirements: 1.1, 1.2, 1.3, 1.4_

-   [x] 5. Implement Project Transaction Report functionality

    -   Extend existing project reporting with category-based analysis
    -   Add methods for retrieving revenue and expenses by category
    -   Implement monthly net cash flow calculation
    -   Create data aggregation for monthly transaction summaries
    -   _Requirements: 2.1, 2.2, 2.3, 2.5_

-   [x] 6. Modify or extend User Investment Report page

    -   Update existing user report page or create new page if needed
    -   Add form for date range and user filtering
    -   Implement monthly deposit/withdrawal table display
    -   Add active investor status display
    -   _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

-   [x] 7. Modify or extend Project Transaction Report page

    -   Update existing project report page or create new page if needed
    -   Add form for date range and project filtering
    -   Implement revenue and expense category tables
    -   Add monthly net cash flow display
    -   _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

-   [x] 8. Add data visualization components to reports

    -   Implement monthly deposit/withdrawal bar chart
    -   Create net deposit trend line chart
    -   Add revenue/expense category pie charts
    -   Implement month-over-month comparison charts
    -   _Requirements: 1.3, 2.5_

-   [x] 9. Implement report export functionality

    -   Add CSV export for both report types
    -   Implement PDF export for both report types
    -   Add export buttons to report pages
    -   Ensure exports include all relevant data and formatting
    -   _Requirements: 3.1, 3.2, 3.3_

-   [x] 10. Update navigation and menu integration

    -   Update report links in Filament navigation menu
    -   Ensure proper authorization for report access
    -   Add quick links from dashboard to new reports
    -   _Requirements: User experience enhancement_

-   [x] 11. Test report modifications

    -   Write unit tests for modified report services
    -   Create feature tests for updated report pages
    -   Test export functionality
    -   Verify data visualization components
    -   _Requirements: All requirements - testing coverage_

-   [x] 12. Update documentation and help text
    -   Update inline help text for report filters
    -   Create tooltips for data interpretation
    -   Update documentation for export formats
    -   Update user guide for report usage
    -   _Requirements: User experience enhancement_
