<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Login Controller
 * 
 * Prompt 90: Create custom Login Controller with role-based redirection.
 * Extends the default Breeze authentication with role-specific dashboard routing.
 */
class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * Authenticates the user and redirects to role-appropriate dashboard.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        return redirect()->intended($this->getRedirectPath($user));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get the redirect path based on user's role.
     */
    protected function getRedirectPath($user): string
    {
        $roles = $user->getRoleNames();
        
        if ($roles->contains('admin')) {
            return route('admin.dashboard', absolute: false);
        }
        
        if ($roles->contains('teacher')) {
            return route('teacher.dashboard', absolute: false);
        }
        
        if ($roles->contains('student')) {
            return route('student.dashboard', absolute: false);
        }
        
        if ($roles->contains('parent')) {
            return route('parent.dashboard', absolute: false);
        }
        
        if ($roles->contains('accountant')) {
            return route('accountant.dashboard', absolute: false);
        }
        
        if ($roles->contains('librarian')) {
            return route('librarian.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }

    /**
     * Get the dashboard route name for a given role.
     */
    public static function getDashboardRoute(string $role): string
    {
        return match($role) {
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'student' => 'student.dashboard',
            'parent' => 'parent.dashboard',
            'accountant' => 'accountant.dashboard',
            'librarian' => 'librarian.dashboard',
            default => 'dashboard',
        };
    }

    /**
     * Check if the user has access to a specific dashboard.
     */
    public static function canAccessDashboard($user, string $dashboard): bool
    {
        $roles = $user->getRoleNames();
        
        return match($dashboard) {
            'admin' => $roles->contains('admin'),
            'teacher' => $roles->contains('teacher'),
            'student' => $roles->contains('student'),
            'parent' => $roles->contains('parent'),
            'accountant' => $roles->contains('accountant'),
            'librarian' => $roles->contains('librarian'),
            default => true,
        };
    }
}
