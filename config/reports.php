<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Visibility Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls which reports are visible and active in the system.
    | Setting a report to 'false' will:
    | - Hide it from the navigation menu
    | - Prevent its page from being registered with Filament
    | - Exclude its widgets from the dashboard
    |
    | To re-enable a report, simply change its value to 'true' and clear the config cache:
    | php artisan config:clear
    |
    */

    'enabled' => [
        // Client-related reports (disabled as per requirements)
        'client_investments' => false,
        'client_portfolio' => false,
        'investment_performance' => false,
        'profit_distribution' => false,
        'client_statement' => false,
        'property_performance' => false,

        // Other reports (enabled by default)
        'user_investment' => true,
        'project_transaction' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Dependencies
    |--------------------------------------------------------------------------
    |
    | This section defines dependencies between reports. If a report is disabled,
    | its dependent reports will also be automatically disabled.
    |
    */

    'dependencies' => [
        // Example: 'dependent_report' => ['parent_report1', 'parent_report2'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Widget Configuration
    |--------------------------------------------------------------------------
    |
    | This section controls specific widget settings for each report type.
    | These settings will only apply if the report is enabled.
    |
    */

    'widgets' => [
        'dashboard_limit' => 6, // Maximum number of report widgets on dashboard
    ],
];
