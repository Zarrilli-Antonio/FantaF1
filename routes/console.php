<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('f1:sync-season')->daily();
Schedule::command('f1:sync-due-results')->hourly();
Schedule::command('leagues:resolve-due-auction-rounds')->everyFiveMinutes();
