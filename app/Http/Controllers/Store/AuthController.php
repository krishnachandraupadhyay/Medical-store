<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreLoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the Store login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isStoreOwner() || $user->isStaff()) {
                return redirect()->route('store.dashboard');
            }
        }

        return view('store.auth.login');
    }

    /**
     * Handle an incoming Store authentication request.
     */
    public function login(StoreLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $user = User::with(['store', 'staffRole'])->where('email', $credentials['email'])->first();

        // 1. Verify existence and password match
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Invalid email address or password.'])
                ->onlyInput('email');
        }

        // 2. Verify user account is active
        if (! $user->isActive()) {
            return back()
                ->withErrors(['email' => 'Your account is deactivated. Please contact your store administrator.'])
                ->onlyInput('email');
        }

        // 3. Verify user has store access (Store Owner or Store Staff)
        if (! in_array($user->role, [UserRole::STORE_OWNER, UserRole::STORE_STAFF], true)) {
            return back()
                ->withErrors(['email' => 'Access denied. You do not have store access privileges.'])
                ->onlyInput('email');
        }

        // Check if assigned staff role is active
        if ($user->isStaff() && $user->role_id) {
            $staffRole = $user->staffRole;
            if ($staffRole && ! $staffRole->isActive()) {
                return back()
                    ->withErrors(['email' => 'Your assigned staff role is currently inactive. Please contact your store owner.'])
                    ->onlyInput('email');
            }
        }

        // 4. Verify user is associated with a valid store
        if (! $user->store_id || ! $user->store) {
            return back()
                ->withErrors(['email' => 'Access denied. No medical store is associated with this account.'])
                ->onlyInput('email');
        }

        // 5. Verify that the medical store is Active
        if ($user->store->status !== StoreStatus::ACTIVE) {
            $statusText = strtolower($user->store->status->value);

            return back()
                ->withErrors(['email' => "Your medical store '{$user->store->name}' is currently {$statusText}. Please contact platform support."])
                ->onlyInput('email');
        }

        // 6. Authenticate, record login timestamp, and regenerate session
        $user->update(['last_login_at' => now()]);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Audit Log
        $roleLabel = $user->isStoreOwner() ? 'Store Owner' : ($user->staffRole?->name ?? 'Staff');
        AuditLogger::log(
            AuditAction::LOGIN,
            AuditModule::AUTH,
            "{$roleLabel} {$user->name} logged into store {$user->store->name} ({$user->store->code}).",
            $user,
            null,
            null,
            $user
        );

        return redirect()->intended(route('store.dashboard'));
    }

    /**
     * Log the Store Owner out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLogger::log(
                AuditAction::LOGOUT,
                AuditModule::AUTH,
                "Store Owner {$user->name} logged out.",
                $user,
                null,
                null,
                $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.login')
            ->with('status', 'You have been safely signed out of your Store Dashboard.');
    }
}
