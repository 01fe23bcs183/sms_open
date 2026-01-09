<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two-Factor Middleware
 * 
 * Prompt 316: Create Two-Factor Middleware
 * 
 * Requires 2FA verification for sensitive actions.
 * Blocks access if 2FA is not verified.
 */
class TwoFactorMiddleware
{
    /**
     * Session key for 2FA verification.
     */
    protected const SESSION_KEY = 'two_factor_verified';

    /**
     * Session key for trusted device.
     */
    protected const TRUSTED_DEVICE_KEY = 'two_factor_trusted_device';

    /**
     * 2FA verification expiry in minutes.
     */
    protected const VERIFICATION_EXPIRY = 30;

    /**
     * Routes that are allowed without 2FA.
     */
    protected array $allowedRoutes = [
        'two-factor.challenge',
        'two-factor.verify',
        'two-factor.resend',
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

        if (!$this->userHas2FAEnabled($user)) {
            return $next($request);
        }

        if ($this->isAllowedRoute($request)) {
            return $next($request);
        }

        if ($this->isTrustedDevice($request)) {
            return $next($request);
        }

        if ($this->isVerified($request)) {
            return $next($request);
        }

        return $this->requireVerification($request);
    }

    /**
     * Check if user has 2FA enabled.
     */
    protected function userHas2FAEnabled($user): bool
    {
        return $user->two_factor_enabled ?? false;
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

        if ($request->is('two-factor/*', 'logout')) {
            return true;
        }

        return false;
    }

    /**
     * Check if device is trusted.
     */
    protected function isTrustedDevice(Request $request): bool
    {
        $trustedToken = $request->cookie(self::TRUSTED_DEVICE_KEY);

        if (!$trustedToken) {
            return false;
        }

        $user = $request->user();
        $expectedToken = hash('sha256', $user->id . $user->email . config('app.key'));

        return hash_equals($expectedToken, $trustedToken);
    }

    /**
     * Check if 2FA is verified for this session.
     */
    protected function isVerified(Request $request): bool
    {
        $verifiedAt = session(self::SESSION_KEY);

        if (!$verifiedAt) {
            return false;
        }

        $expiresAt = now()->subMinutes(self::VERIFICATION_EXPIRY);

        return $verifiedAt > $expiresAt;
    }

    /**
     * Require 2FA verification.
     */
    protected function requireVerification(Request $request): Response
    {
        $message = 'Two-factor authentication verification required.';

        session()->put('url.intended', $request->url());

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'two_factor_required',
                'redirect' => route('two-factor.challenge'),
            ], 403);
        }

        return redirect()->route('two-factor.challenge')
            ->with('info', $message);
    }

    /**
     * Mark 2FA as verified.
     */
    public static function markVerified(): void
    {
        session()->put(self::SESSION_KEY, now());
    }

    /**
     * Clear 2FA verification.
     */
    public static function clearVerification(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Create trusted device cookie.
     */
    public static function createTrustedDeviceCookie($user, int $days = 30): \Symfony\Component\HttpFoundation\Cookie
    {
        $token = hash('sha256', $user->id . $user->email . config('app.key'));

        return cookie(
            self::TRUSTED_DEVICE_KEY,
            $token,
            $days * 24 * 60,
            '/',
            null,
            true,
            true
        );
    }
}
