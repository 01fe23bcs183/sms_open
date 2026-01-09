<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * File Access Middleware
 * 
 * Prompt 319: Create File Access Middleware
 * 
 * Restricts access to private documents.
 * Ensures only authorized roles can download files.
 */
class FileAccessMiddleware
{
    /**
     * Public file types that don't require authorization.
     */
    protected array $publicTypes = [
        'logo',
        'favicon',
        'banner',
        'public',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $type  The file type being accessed
     */
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        if ($type && in_array($type, $this->publicTypes)) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            return $this->unauthorized($request, 'Authentication required to access this file.');
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $fileId = $request->route('file') ?? $request->route('id');
        $documentType = $type ?? $request->route('type');

        if (!$this->canAccessFile($user, $fileId, $documentType, $request)) {
            return $this->unauthorized($request, 'You do not have permission to access this file.');
        }

        return $next($request);
    }

    /**
     * Check if user can access the file.
     */
    protected function canAccessFile($user, $fileId, ?string $type, Request $request): bool
    {
        switch ($type) {
            case 'student_document':
                return $this->canAccessStudentDocument($user, $fileId);

            case 'homework':
                return $this->canAccessHomework($user, $fileId);

            case 'study_material':
                return $this->canAccessStudyMaterial($user, $fileId);

            case 'report':
                return $this->canAccessReport($user, $fileId);

            case 'fee_receipt':
                return $this->canAccessFeeReceipt($user, $fileId);

            default:
                return $this->canAccessGenericFile($user, $fileId, $request);
        }
    }

    /**
     * Check access to student documents.
     */
    protected function canAccessStudentDocument($user, $fileId): bool
    {
        if ($user->hasRole('teacher')) {
            return true;
        }

        if ($user->hasRole('student')) {
            $document = \App\Models\StudentDocument::find($fileId);
            return $document && $document->student_id === $user->student?->id;
        }

        if ($user->hasRole('parent')) {
            $document = \App\Models\StudentDocument::find($fileId);
            return $document && $user->children()->where('id', $document->student_id)->exists();
        }

        return false;
    }

    /**
     * Check access to homework files.
     */
    protected function canAccessHomework($user, $fileId): bool
    {
        if ($user->hasRole('teacher')) {
            return true;
        }

        if ($user->hasRole('student')) {
            $homework = \App\Models\Homework::find($fileId);
            if (!$homework) {
                return false;
            }

            $student = $user->student;
            return $student && 
                   $homework->class_id === $student->class_id && 
                   $homework->section_id === $student->section_id;
        }

        return false;
    }

    /**
     * Check access to study materials.
     */
    protected function canAccessStudyMaterial($user, $fileId): bool
    {
        if ($user->hasRole(['teacher', 'student'])) {
            return true;
        }

        return false;
    }

    /**
     * Check access to reports.
     */
    protected function canAccessReport($user, $fileId): bool
    {
        if ($user->hasRole(['teacher', 'accountant'])) {
            return true;
        }

        return false;
    }

    /**
     * Check access to fee receipts.
     */
    protected function canAccessFeeReceipt($user, $fileId): bool
    {
        if ($user->hasRole('accountant')) {
            return true;
        }

        if ($user->hasRole('student')) {
            $transaction = \App\Models\FeesTransaction::find($fileId);
            return $transaction && $transaction->student_id === $user->student?->id;
        }

        if ($user->hasRole('parent')) {
            $transaction = \App\Models\FeesTransaction::find($fileId);
            return $transaction && $user->children()->where('id', $transaction->student_id)->exists();
        }

        return false;
    }

    /**
     * Check access to generic files.
     */
    protected function canAccessGenericFile($user, $fileId, Request $request): bool
    {
        if ($request->hasValidSignature()) {
            return true;
        }

        return $user->hasAnyRole(['admin', 'teacher', 'accountant']);
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
                'error' => 'file_access_denied',
            ], 403);
        }

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        abort(403, $message);
    }
}
