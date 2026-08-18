<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('aip:snapshot-leaderboard')->dailyAt('00:00');
Schedule::command('aip:recalc-pillar-stats')->hourly();
Schedule::command('aip:update-challenge')->everyFifteenMinutes();
Schedule::command('aip:process-challenges')->dailyAt('02:00');
Schedule::command('aip:reset-streaks')->dailyAt('01:00');
Schedule::command('aip:watch-logins')->everyFifteenMinutes();
