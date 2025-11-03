<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically refresh evaluations and profits on the first day of each month at 1:00 AM
Schedule::command('evaluations:calculate --force')
    ->monthlyOn(1, '00:01')
    ->timezone('Africa/Cairo')
    ->description('Refresh monthly evaluations and profits')
    ->onSuccess(function () {
        \Log::info('Monthly evaluations refreshed successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to refresh monthly evaluations');
    });

// Also run daily at 00:02 AM to ensure current month is always up to date
Schedule::command('evaluations:calculate')
    ->dailyAt('00:02')
    ->timezone('Africa/Cairo')
    ->description('Update current month evaluations')
    ->onSuccess(function () {
        \Log::info('Daily evaluations update completed');
    })
    ->onFailure(function () {
        \Log::error('Failed to update daily evaluations');
    });
