<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\RecurringServicePlanService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(RecurringServicePlanService::class)->sendDueReminders())
    ->name('service-plan-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();
