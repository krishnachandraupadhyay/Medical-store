<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckStaffPermission;
use App\Http\Middleware\CheckSubscriptionFeature;
use App\Http\Middleware\StoreOwnerMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('store') || $request->is('store/*')) {
                return route('store.login');
            }

            return route('super-admin.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user && $user->isStoreOwner()) {
                return route('store.dashboard');
            }

            return route('super-admin.dashboard');
        });

        $middleware->web(append: [
            CheckMaintenanceMode::class,
        ]);

        $middleware->alias([
            'super_admin' => SuperAdminMiddleware::class,
            'store_owner' => StoreOwnerMiddleware::class,
            'feature' => CheckSubscriptionFeature::class,
            'permission' => CheckStaffPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
