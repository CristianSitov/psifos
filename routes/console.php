<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('app:fetch:hours:2025')
    ->hourly();
Schedule::command('app:fetch:results:2025')
    ->everyMinute();
