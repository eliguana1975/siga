<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('chat:prune-old-messages')->daily();
        $schedule->command('siga:notificaciones-operativas --limit=50')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('siga:alertas-operativas --limit=20 --json')->hourly()->withoutOverlapping();
        $schedule->command('siga:audit-operativo --dias-ot=30 --json')->dailyAt('06:30')->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
            'active.user' => \App\Http\Middleware\EnsureUserIsActive::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\PreventBrowserCache::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsActive::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePasswordChanged::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckPermission::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    
