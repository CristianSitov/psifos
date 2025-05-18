<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('app:fetch:hours:2025')
    ->hourlyAt(1);
Schedule::command('app:fetch:results:2025')
    ->everyMinute();
Schedule::command('app:fetch:finals')
    ->everyMinute()
    ->between('18:00', '06:00')
    ->timezone('UTC');
