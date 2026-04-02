<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily owner summary report at 7 AM
Schedule::command('gusto:daily-report')->dailyAt('07:00');

// Close sessions open more than 12 hours (nightly cleanup)
Schedule::command('gusto:close-stale-sessions')->dailyAt('02:00');

// Check BOM variance every 4 hours
Schedule::command('gusto:check-variance --days=1')->everyFourHours();
