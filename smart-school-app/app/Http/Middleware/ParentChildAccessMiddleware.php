<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Parent-Child Access Middleware
 * 
 * Prompt 320: Create Parent-Child Access Middleware
 * 
 * Restricts parents to their own children only.
 * Ensures parents cannot access other students.
 */
class ParentChildAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required.');
        }

        if (!$user->hasRole('parent')) {
            return $next($request);
        }

        $studentId = $this->extractStudentId($request);

        if (!$studentId) {
            return $next($request);
        }

        if (!$this->isChildOfParent($user, $studentId)) {
            return $this->unauthorized($request, 'You can only access information for your own children.');
        }

        return $next($request);
    }

    /**
     * Extract student ID from request.
     */
    protected function extractStudentId(Request $request): ?int
    {
        $studentId = $request->route('student') 
            ?? $request->route('student_id')
            ?? $request->route('child')
            ?? $request->input('student_id')
            ?? $request->query('student_id');

        if ($studentId instanceof \App\Models\Student) {
            return $studentId->id;
        }

        return $studentId ? (int) $studentId : null;
    }

    /**
     * Check if student is a child of the parent.
     */
    protected function isChildOfParent($user, int $studentId): bool
    {
        if (!method_exists($user, 'children')) {
            return $this->checkChildRelationship($user, $studentId);
        }

        return $user->children()->where('id', $studentId)->exists();
    }

    /**
     * Check child relationship via pivot table.
     */
    protected function checkChildRelationship($user, int $studentId): bool
    {
        return \DB::table('parent_student')
            ->where('parent_id', $user->id)
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * Return unauthorized response.
     */
    protected function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'child_access_denied',
            ], 403);
        }

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        return redirect()->route('parent.children.index')
            ->with('error', $message);
    }
}
