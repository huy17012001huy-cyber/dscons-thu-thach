<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\DsconsRevitToolSeeder;

Schedule::command('aip:snapshot-leaderboard')->dailyAt('00:00');
Schedule::command('aip:recalc-pillar-stats')->hourly();
Schedule::command('aip:update-challenge')->everyFifteenMinutes();
Schedule::command('aip:process-challenges')->dailyAt('02:00');
Schedule::command('aip:reset-streaks')->dailyAt('01:00');
Schedule::command('aip:watch-logins')->everyFifteenMinutes();
Schedule::command('recruitment:expire-requests')->dailyAt('03:00');

Artisan::command('revit-tools:seed-demo', function () {
    app(DsconsRevitToolSeeder::class)->run();
    $this->info('Đã seed DSCons Tool Test 1 và Test 2.');
});
