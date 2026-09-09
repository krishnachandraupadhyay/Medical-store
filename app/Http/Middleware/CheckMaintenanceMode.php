<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenance = (bool) $this->settingService->get('maintenance_mode', false);

        if (! $isMaintenance) {
            return $next($request);
        }

        // Allow Super Admin access
        if ($request->user() && $request->user()->role === UserRole::SUPER_ADMIN) {
            return $next($request);
        }

        // Allow Super Admin login routes and asset requests
        if ($request->is('super-admin/login*') || $request->is('login*') || $request->is('up')) {
            return $next($request);
        }

        $message = (string) $this->settingService->get('maintenance_message', 'The system is undergoing scheduled maintenance.');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'maintenance',
                'message' => $message,
            ], 503);
        }

        return response()->view('errors.maintenance', [
            'message' => $message,
        ], 503);
    }
}
