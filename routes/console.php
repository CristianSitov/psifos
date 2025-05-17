<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('app:fetch:hours:2025')
    ->hourlyAt(0);
Schedule::command('app:fetch:results:2025')
    ->everyMinute();
