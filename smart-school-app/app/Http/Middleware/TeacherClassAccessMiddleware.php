<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teacher-Class Access Middleware
 * 
 * Prompt 321: Create Teacher-Class Access Middleware
 * 
 * Restricts teachers to their assigned classes.
 * Validates teacher assignments before data access.
 */
class TeacherClassAccessMiddleware
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

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if (!$user->hasRole('teacher')) {
            return $next($request);
        }

        $classId = $this->extractClassId($request);
        $sectionId = $this->extractSectionId($request);

        if (!$classId && !$sectionId) {
            return $next($request);
        }

        if (!$this->hasClassAccess($user, $classId, $sectionId)) {
            return $this->unauthorized($request, 'You do not have access to this class.');
        }

        return $next($request);
    }

    /**
     * Extract class ID from request.
     */
    protected function extractClassId(Request $request): ?int
    {
        $classId = $request->route('class') 
            ?? $request->route('class_id')
            ?? $request->input('class_id')
            ?? $request->query('class_id');

        if ($classId instanceof \App\Models\SchoolClass) {
            return $classId->id;
        }

        return $classId ? (int) $classId : null;
    }

    /**
     * Extract section ID from request.
     */
    protected function extractSectionId(Request $request): ?int
    {
        $sectionId = $request->route('section') 
            ?? $request->route('section_id')
            ?? $request->input('section_id')
            ?? $request->query('section_id');

        if ($sectionId instanceof \App\Models\Section) {
            return $sectionId->id;
        }

        return $sectionId ? (int) $sectionId : null;
    }

    /**
     * Check if teacher has access to the class/section.
     */
    protected function hasClassAccess($user, ?int $classId, ?int $sectionId): bool
    {
        $query = \DB::table('class_subjects')
            ->where('teacher_id', $user->id);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        return $query->exists();
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
                'error' => 'class_access_denied',
            ], 403);
        }

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        return redirect()->route('teacher.dashboard')
            ->with('error', $message);
    }
}
