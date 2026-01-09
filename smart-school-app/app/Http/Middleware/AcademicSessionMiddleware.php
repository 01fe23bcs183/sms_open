<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Academic Session Middleware
 * 
 * Prompt 311: Create Academic Session Middleware
 * 
 * Ensures requests are tied to the active academic session.
 * Loads current session and blocks when missing.
 */
class AcademicSessionMiddleware
{
    /**
     * Cache key for current academic session.
     */
    protected const CACHE_KEY = 'current_academic_session';

    /**
     * Cache TTL in seconds (10 minutes).
     */
    protected const CACHE_TTL = 600;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $this->getCurrentSession();

        if (!$session) {
            return $this->noSessionConfigured($request);
        }

        $request->attributes->set('academic_session', $session);
        $request->attributes->set('academic_session_id', $session->id);

        app()->instance('academic_session', $session);

        return $next($request);
    }

    /**
     * Get the current academic session.
     */
    protected function getCurrentSession(): ?\App\Models\AcademicSession
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return \App\Models\AcademicSession::where('is_current', true)
                ->where('status', 'active')
                ->first();
        });
    }

    /**
     * Return response when no session is configured.
     */
    protected function noSessionConfigured(Request $request): Response
    {
        $message = 'No active academic session is configured. Please contact the administrator.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'no_academic_session',
            ], 503);
        }

        $user = $request->user();
        if ($user && $user->hasRole('admin')) {
            return redirect()->route('admin.academic-sessions.index')
                ->with('warning', 'Please set an active academic session to continue.');
        }

        return response()->view('errors.no-session', [
            'message' => $message,
        ], 503);
    }

    /**
     * Clear the cached academic session.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
