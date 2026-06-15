<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'room.auth' => \App\Http\Middleware\ValidateRoomToken::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

if (isset($_ENV['VERCEL']) || env('VERCEL')) {
    $app->useStoragePath('/tmp/storage');
    foreach (['framework/views', 'framework/cache/data', 'framework/sessions', 'logs'] as $dir) {
        $path = '/tmp/storage/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}

return $app;
