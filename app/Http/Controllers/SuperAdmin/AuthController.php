<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\LoginRequest;
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
     * Display the Super Admin login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('super-admin.dashboard');
            }
        }

        return view('super-admin.auth.login');
    }

    /**
     * Handle an incoming Super Admin authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        // 1. Verify existence and password match
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Invalid email address or password.'])
                ->onlyInput('email');
        }

        // 2. Verify account is active
        if (! $user->isActive()) {
            return back()
                ->withErrors(['email' => 'This account has been deactivated. Please contact system support.'])
                ->onlyInput('email');
        }

        // 3. Verify user has SUPER_ADMIN role
        if ($user->role !== UserRole::SUPER_ADMIN) {
            return back()
                ->withErrors(['email' => 'Access denied. You do not have Super Administrator privileges.'])
                ->onlyInput('email');
        }

        // 4. Authenticate and regenerate session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditLogger::log(
            AuditAction::LOGIN,
            AuditModule::AUTH,
            "Super Admin {$user->name} logged in successfully.",
            $user,
            null,
            null,
            $user
        );

        return redirect()->intended(route('super-admin.dashboard'));
    }

    /**
     * Log the Super Admin out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLogger::log(
                AuditAction::LOGOUT,
                AuditModule::AUTH,
                "Super Admin {$user->name} logged out.",
                $user,
                null,
                null,
                $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login')
            ->with('status', 'You have been safely signed out of the Super Admin portal.');
    }
}
