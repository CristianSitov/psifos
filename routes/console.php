<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('app:fetch:results:2025')
    ->everyMinute();
Schedule::command('app:fetch:finals:2025')
    ->everyMinute();
