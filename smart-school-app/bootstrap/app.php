<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Session 30: SetLocale middleware (Prompt 305)
        // Session 31: Register Middleware (Prompt 322)
        
        // Global middleware for web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\LocaleMiddleware::class,
            \App\Http\Middleware\TimezoneMiddleware::class,
        ]);

        // Middleware aliases for route-level usage
        $middleware->alias([
            // Core Access Middleware (Prompts 308-314)
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'module' => \App\Http\Middleware\ModuleAccessMiddleware::class,
            'academic.session' => \App\Http\Middleware\AcademicSessionMiddleware::class,
            'school.context' => \App\Http\Middleware\SchoolContextMiddleware::class,
            'locale' => \App\Http\Middleware\LocaleMiddleware::class,
            'timezone' => \App\Http\Middleware\TimezoneMiddleware::class,
            
            // Security Middleware (Prompts 315-321)
            'password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'two-factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'api.throttle' => \App\Http\Middleware\ApiThrottleMiddleware::class,
            'audit' => \App\Http\Middleware\AuditLogMiddleware::class,
            'file.access' => \App\Http\Middleware\FileAccessMiddleware::class,
            'parent.child' => \App\Http\Middleware\ParentChildAccessMiddleware::class,
            'teacher.class' => \App\Http\Middleware\TeacherClassAccessMiddleware::class,
        ]);

        // API middleware group
        $middleware->api(append: [
            \App\Http\Middleware\ApiThrottleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
