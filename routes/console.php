<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// MONTHLY: Full refresh on 1st of month at 1:00 AM (recalculates everything)
Schedule::command('evaluations:calculate --force')
    ->monthlyOn(1, '01:00')
    ->timezone('Africa/Cairo')
    ->description('Monthly: Full recalculation of all evaluations')
    ->onSuccess(function () {
        \Log::info('[MONTHLY] All evaluations refreshed successfully');
    })
    ->onFailure(function () {
        \Log::error('[MONTHLY] Failed to refresh evaluations');
    });

// DAILY: Update only last 3 months at 2:00 AM (efficient, keeps current data fresh)
Schedule::command('evaluations:calculate --from-month=' . now()->subMonths(2)->format('Y-m-01'))
    ->dailyAt('02:00')
    ->timezone('Africa/Cairo')
    ->description('Daily: Update last 3 months evaluations')
    ->onSuccess(function () {
        \Log::info('[DAILY] Last 3 months evaluations updated successfully');
    })
    ->onFailure(function () {
        \Log::error('[DAILY] Failed to update evaluations');
    });

// OPTIONAL: Light check every 6 hours during business day (only if you need real-time updates)
// Uncomment if you want evaluations updated 4 times a day
// Schedule::command('evaluations:calculate --from-month=' . now()->startOfMonth()->format('Y-m-01'))
//     ->cron('0 8,12,16,20 * * *')  // 8am, 12pm, 4pm, 8pm
//     ->timezone('Africa/Cairo')
//     ->description('Update current month only (lightweight)')
//     ->onSuccess(function () {
//         \Log::info('[HOURLY] Current month updated');
//     });
