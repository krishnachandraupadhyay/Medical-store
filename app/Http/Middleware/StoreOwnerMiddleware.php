<?php

namespace App\Http\Middleware;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StoreOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Authentication
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('store.login'))
                ->with('error', 'Please log in to access your Store Dashboard.');
        }

        $user = Auth::user();

        // 2. Check if user account is active
        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is inactive.'], 403);
            }

            return redirect()->route('store.login')
                ->withErrors(['email' => 'Your account is currently inactive. Please contact platform support.']);
        }

        // 3. Check if user is a Store Owner
        if ($user->role !== UserRole::STORE_OWNER) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. Store Owner privileges required.'], 403);
            }

            return redirect()->route('store.login')
                ->withErrors(['email' => 'Access denied. You do not have Store Owner privileges.']);
        }

        // 4. Check if user has an associated store
        if (! $user->store_id || ! $user->store) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. No medical store associated with this account.'], 403);
            }

            return redirect()->route('store.login')
                ->withErrors(['email' => 'Access denied. No medical store is associated with your account.']);
        }

        // 5. Check if store is Active
        $store = $user->store;
        if ($store->status !== StoreStatus::ACTIVE) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $statusText = strtolower($store->status->value);

            if ($request->expectsJson()) {
                return response()->json(['message' => "Medical store is currently {$statusText}."], 403);
            }

            return redirect()->route('store.login')
                ->withErrors(['email' => "Your medical store '{$store->name}' is currently {$statusText}. Please contact platform support."]);
        }

        return $next($request);
    }
}
