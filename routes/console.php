<?php

use App\Helper\ReportHelper;
use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$dailyTime = Setting::getValue('daily_hour') ?? '00:00';
// Validate time format (optional but recommended)
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $dailyTime)) {
    $dailyTime = '00:00'; // Fallback if invalid format
}
Schedule::call(function () {
    ReportHelper::sendDailyReport();
})->dailyAt($dailyTime);
