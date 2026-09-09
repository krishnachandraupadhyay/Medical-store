<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('super-admin.login'))
                ->with('error', 'Please log in to access the Super Admin portal.');
        }

        $user = Auth::user();

        // Check if account is active
        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is inactive.'], 403);
            }

            return redirect()->route('super-admin.login')
                ->withErrors(['email' => 'Your account is currently inactive. Please contact system support.']);
        }

        // Check if user has SUPER_ADMIN role
        if ($user->role !== UserRole::SUPER_ADMIN) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. Super Admin privileges required.'], 403);
            }

            return redirect()->route('super-admin.login')
                ->withErrors(['email' => 'Access denied. You do not have Super Admin privileges.']);
        }

        return $next($request);
    }
}
