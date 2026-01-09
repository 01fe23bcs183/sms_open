<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force Password Change Middleware
 * 
 * Prompt 315: Create Force Password Change Middleware
 * 
 * Forces users to update default or expired passwords.
 * Redirects users to password change screen.
 */
class ForcePasswordChange
{
    /**
     * Routes that are allowed without password change.
     */
    protected array $allowedRoutes = [
        'password.change',
        'password.change.update',
        'logout',
        'profile.password',
        'profile.password.update',
    ];

    /**
     * Route prefixes that are allowed.
     */
    protected array $allowedPrefixes = [
        'password',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$this->requiresPasswordChange($user)) {
            return $next($request);
        }

        if ($this->isAllowedRoute($request)) {
            return $next($request);
        }

        return $this->redirectToPasswordChange($request);
    }

    /**
     * Check if user requires password change.
     */
    protected function requiresPasswordChange($user): bool
    {
        if ($user->force_password_change ?? false) {
            return true;
        }

        if ($this->isPasswordExpired($user)) {
            return true;
        }

        return false;
    }

    /**
     * Check if password is expired.
     */
    protected function isPasswordExpired($user): bool
    {
        $expiryDays = config('auth.password_expiry_days', 0);

        if ($expiryDays <= 0) {
            return false;
        }

        $passwordChangedAt = $user->password_changed_at ?? $user->created_at;

        if (!$passwordChangedAt) {
            return false;
        }

        return $passwordChangedAt->addDays($expiryDays)->isPast();
    }

    /**
     * Check if current route is allowed.
     */
    protected function isAllowedRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, $this->allowedRoutes)) {
            return true;
        }

        foreach ($this->allowedPrefixes as $prefix) {
            if ($routeName && str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        if ($request->is('password/*', 'logout', 'api/*/auth/logout')) {
            return true;
        }

        return false;
    }

    /**
     * Redirect to password change page.
     */
    protected function redirectToPasswordChange(Request $request): Response
    {
        $message = 'You must change your password before continuing.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'password_change_required',
                'redirect' => route('password.change'),
            ], 403);
        }

        return redirect()->route('password.change')
            ->with('warning', $message);
    }
}
