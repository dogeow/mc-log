<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // 每秒运行一次日志处理
        $schedule->command('minecraft:process-logs')
                ->everySecond()
                ->sendOutputTo(storage_path('logs/process.log'), true);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
