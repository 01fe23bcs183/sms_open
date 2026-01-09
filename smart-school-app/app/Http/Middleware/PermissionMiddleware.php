<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permission Middleware
 * 
 * Prompt 309: Create Permission Middleware
 * 
 * Restricts routes by fine-grained permissions using Spatie Permission.
 * Validates user permissions for specific actions.
 */
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions  One or more permission names
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required.');
        }

        if (empty($permissions)) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        return $this->unauthorized($request, 'You do not have the required permission to perform this action.');
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
                'error' => 'forbidden',
            ], 403);
        }

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        abort(403, $message);
    }
}
