# Requirements Document

## Introduction

This feature enhances the real estate project management system by implementing two specific report pages:

1. **User Investment Report**: A report that tracks user deposits (investments) and withdrawals, calculates net deposits each month, and identifies active investors based on whether they still have a positive net deposit amount from their first month until now.

2. **Project Transaction Report**: A report that shows total revenue and expense transactions for each project, categorized by transaction type, on a monthly basis.

The key focus is on understanding the investment relationship between users and the company as a whole, rather than investments in specific properties. When a user deposits money, they are investing with the company as a whole. The system will track these investments over time and provide detailed monthly reporting on both user investment activities and project transaction activities.

## Requirements

### Requirement 1

**User Story:** As an investment manager, I want a user investment report page that shows deposits, withdrawals, and net deposits by month so that I can track investment patterns and identify active investors.

#### Acceptance Criteria

1. WHEN I access the user investment report THEN the system SHALL display total deposits for each month
2. WHEN I access the user investment report THEN the system SHALL display total withdrawals for each month
3. WHEN I access the user investment report THEN the system SHALL calculate and display net deposits for each month
4. WHEN I view investor status THEN the system SHALL identify active investors based on whether they still have a positive net deposit amount from their first month until now
5. WHEN I filter the report THEN the system SHALL allow filtering by date range and user

### Requirement 2

**User Story:** As a project manager, I want a project transaction report page that shows revenue and expense transactions by category each month so that I can monitor project financial performance.

#### Acceptance Criteria

1. WHEN I access the project transaction report THEN the system SHALL display total revenue transactions categorized by revenue type for each month
2. WHEN I access the project transaction report THEN the system SHALL display total expense transactions categorized by expense type for each month
3. WHEN I view project financial data THEN the system SHALL calculate and display net project cash flow for each month
4. WHEN I filter the report THEN the system SHALL allow filtering by date range and project
5. WHEN I analyze project finances THEN the system SHALL provide visualizations of revenue and expense distribution by category

### Requirement 3

**User Story:** As a data analyst, I want to export both investment and transaction reports so that I can perform further analysis and share insights with stakeholders.

#### Acceptance Criteria

1. WHEN I need to export report data THEN the system SHALL provide export functionality in CSV format for data analysis
2. WHEN I need to share reports THEN the system SHALL provide export functionality in PDF format for presentation
3. WHEN I export data THEN the system SHALL include all relevant metrics, categories, and time period information
