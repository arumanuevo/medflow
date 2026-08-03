<?php
// bootstrap/app.php
// bootstrap/app.php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            
            // ✅ Nuevos middlewares para colaboración y tokens
            'workspace.access' => \App\Http\Middleware\CheckWorkspaceAccess::class,
            'token.access' => \App\Http\Middleware\CheckTokenAccess::class,
            
            // ✅ Middleware de roles (si no lo tienes)
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'subscription.limits' => \App\Http\Middleware\CheckSubscriptionLimits::class,
            'subscription.gate' => \App\Http\Middleware\CheckSubscriptionGate::class,
        ]);
    
        $middleware->web(
            append: [
                // \App\Http\Middleware\InjectSanctumToken::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();