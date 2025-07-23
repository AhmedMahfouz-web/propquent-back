# Design Document: Disable Client Reports

## Overview

This design outlines the approach for disabling and hiding several client-related reports and their associated widgets and pages in the system. The implementation will focus on using Laravel and Filament's built-in mechanisms for conditionally registering components, ensuring that the disabled reports can be easily re-enabled in the future if needed.

## Architecture

The implementation will leverage Laravel's service provider system and Filament's registration hooks to conditionally register or exclude the report components. This approach allows for a clean, configuration-driven solution rather than removing code or adding conditional logic throughout the application.

### Configuration Approach

We will create a new configuration file `config/reports.php` that will contain settings for enabling/disabling specific reports. This configuration-based approach allows for:

1. Easy toggling of reports without code changes
2. Environment-specific configurations
3. Future extensibility for other report settings

## Components and Interfaces

### Reports to Disable

Based on the requirements, the following reports and their associated components need to be disabled:

1. Client Investments Report

    - Report page class
    - Associated widgets
    - Navigation menu items

2. Client Portfolio Report

    - Report page class
    - Associated widgets
    - Navigation menu items

3. Investment Performance Report

    - Report page class
    - Associated widgets
    - Navigation menu items

4. Profit Distribution Report

    - Report page class
    - Associated widgets
    - Navigation menu items

5. Client Statement Report

    - Report page class
    - Associated widgets
    - Navigation menu items

6. Property Performance Report
    - Report page class
    - Associated widgets
    - Navigation menu items

### Implementation Components

1. **Configuration File**

    - Create `config/reports.php` with toggles for each report type

2. **Service Provider Modifications**

    - Modify the appropriate service providers to check configuration before registering report components

3. **Filament Resource Registration**

    - Update Filament panel provider to conditionally register report pages and widgets

4. **Navigation Modifications**
    - Update navigation registration to exclude disabled report links

## Data Models

No changes to data models are required for this feature. All existing database tables and structures will be maintained to ensure that reports can be re-enabled in the future without data loss.

## Error Handling

The implementation will include graceful handling for cases where other parts of the system might depend on the disabled reports:

1. Check for dependencies before disabling components
2. Provide fallback behavior where appropriate
3. Log warnings for any potential issues during the disabling process

## Testing Strategy

Testing will focus on verifying that the disabled reports are properly hidden from the UI and that their functionality is not being loaded:

1. **Unit Tests**

    - Test configuration loading
    - Test conditional registration logic

2. **Feature Tests**

    - Verify report pages are not accessible
    - Verify widgets are not displayed
    - Verify navigation items are not shown

3. **Integration Tests**
    - Ensure other system functionality is not affected by the disabled reports
    - Test that re-enabling reports through configuration works correctly
