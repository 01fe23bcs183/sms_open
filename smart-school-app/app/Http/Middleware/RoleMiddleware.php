<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role Middleware
 * 
 * Prompt 308: Create Role Middleware
 * 
 * Restricts routes by user role using Spatie Permission.
 * Blocks requests when user lacks required role.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more role names
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        return $this->unauthorized($request, 'You do not have the required role to access this resource.');
    }

    /**
     * Return unauthorized response based on request type.
     */
    protected function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'unauthorized',
            ], 403);
        }

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        abort(403, $message);
    }
}
