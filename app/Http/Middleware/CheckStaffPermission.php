<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('store.login'));
        }

        // Super Admin and Store Owner have full operational privileges
        if ($user->isSuperAdmin() || $user->isStoreOwner()) {
            return $next($request);
        }

        if (! $user->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is inactive.'], 403);
            }

            abort(403, 'Account is inactive.');
        }

        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Access denied. You lack the required '{$permission}' permission.",
                ], 403);
            }

            abort(403, "Access denied. You lack the required '{$permission}' permission.");
        }

        return $next($request);
    }
}
