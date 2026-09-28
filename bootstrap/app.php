<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middlewsare\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
             'auth'=>\App\Http\Middleware\Authenticate::class,
             'auth_admin'=> \App\Http\Middleware\AdminMiddleware::class,
             'jwt.auth'=> \App\Http\Middleware\JwtMiddleware::class,
        ]);
        $middleware->appendToGroup('auth', [
            'auth'=>\App\Http\Middleware\Authenticate::class,
        ]);
        $middleware->appendToGroup('auth_admin', [
             'auth'=>\App\Http\Middleware\Authenticate::class,
             'auth_admin'=> \App\Http\Middleware\AdminMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
      
                '/pay-via-ajax', '/success','/cancel','/fail','/ipn',
    ]);


    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
