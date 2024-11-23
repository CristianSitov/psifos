<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('app:fetch:hours')
    ->hourlyAt(1);
Schedule::command('app:fetch:results')
    ->everyMinute();
