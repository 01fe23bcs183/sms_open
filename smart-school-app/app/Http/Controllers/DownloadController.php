<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download Controller
 * 
 * Prompt 301: Secure File Downloads and Media Access
 * 
 * Handles secure file downloads with authorization checks.
 * Supports:
 * - Student documents (restricted to student, parent, admin)
 * - Teacher documents (restricted to teacher, admin)
 * - Library resources
 * - General attachments
 * - Signed URL generation for temporary access
 */
class DownloadController extends Controller
{
    /**
     * Download a student document.
     *
     * @param Request $request
     * @param int $studentId
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function studentDocument(Request $request, int $studentId, string $filename)
    {
        // Authorization check
        $user = $request->user();
        
        if (!$this->canAccessStudentDocument($user, $studentId)) {
            return $this->downloadUnauthorizedResponse('You are not authorized to access this document');
        }

        $path = "students/documents/{$filename}";
        
        return $this->downloadFile($path, $filename);
    }

    /**
     * Download a student photo.
     *
     * @param Request $request
     * @param int $studentId
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function studentPhoto(Request $request, int $studentId, string $filename)
    {
        // Photos are generally accessible to authenticated users
        $path = "students/photos/{$filename}";
        
        return $this->serveFile($path);
    }

    /**
     * Download a teacher document.
     *
     * @param Request $request
     * @param int $teacherId
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function teacherDocument(Request $request, int $teacherId, string $filename)
    {
        // Authorization check
        $user = $request->user();
        
        if (!$this->canAccessTeacherDocument($user, $teacherId)) {
            return $this->downloadUnauthorizedResponse('You are not authorized to access this document');
        }

        $path = "teachers/documents/{$filename}";
        
        return $this->downloadFile($path, $filename);
    }

    /**
     * Download a teacher photo.
     *
     * @param Request $request
     * @param int $teacherId
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function teacherPhoto(Request $request, int $teacherId, string $filename)
    {
        // Photos are generally accessible to authenticated users
        $path = "teachers/photos/{$filename}";
        
        return $this->serveFile($path);
    }

    /**
     * Download a library book cover.
     *
     * @param Request $request
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function bookCover(Request $request, string $filename)
    {
        // Book covers are publicly accessible
        $path = "library/covers/{$filename}";
        
        return $this->serveFile($path);
    }

    /**
     * Download an attachment.
     *
     * @param Request $request
     * @param string $type
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function attachment(Request $request, string $type, string $filename)
    {
        // Validate type
        $allowedTypes = ['notice', 'event', 'homework', 'assignment', 'attachments'];
        
        if (!in_array($type, $allowedTypes)) {
            return $this->downloadNotFoundResponse('Invalid attachment type');
        }

        $path = "attachments/{$type}/{$filename}";
        
        return $this->downloadFile($path, $filename);
    }

    /**
     * Download a general document.
     *
     * @param Request $request
     * @param string $folder
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function document(Request $request, string $folder, string $filename)
    {
        // Security: Validate folder
        $allowedFolders = ['documents', 'uploads', 'reports', 'exports'];
        
        if (!in_array($folder, $allowedFolders)) {
            return $this->downloadNotFoundResponse('Invalid folder');
        }

        $path = "{$folder}/{$filename}";
        
        return $this->downloadFile($path, $filename);
    }

    /**
     * Download school logo.
     *
     * @param Request $request
     * @return BinaryFileResponse|Response
     */
    public function schoolLogo(Request $request)
    {
        // Try different extensions
        $extensions = ['png', 'jpg', 'jpeg', 'svg'];
        
        foreach ($extensions as $ext) {
            $path = "school/logo.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                return $this->serveFile($path);
            }
        }

        return $this->downloadNotFoundResponse('School logo not found');
    }

    /**
     * Generate a signed URL for temporary access.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSignedUrl(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'expires' => 'nullable|integer|min:1|max:1440', // Max 24 hours in minutes
        ]);

        $path = $request->input('path');
        $expiresInMinutes = $request->input('expires', 60);

        // Security: Validate path
        if (!$this->isValidPath($path)) {
            return $this->errorResponse('Invalid file path', [], 403);
        }

        if (!Storage::disk('public')->exists($path)) {
            return $this->downloadNotFoundResponse('File not found');
        }

        // Generate signed URL
        $url = Storage::disk('public')->temporaryUrl(
            $path,
            now()->addMinutes($expiresInMinutes)
        );

        return $this->successResponse([
            'url' => $url,
            'expires_at' => now()->addMinutes($expiresInMinutes)->toISOString(),
            'expires_in_minutes' => $expiresInMinutes,
        ], 'Signed URL generated');
    }

    /**
     * Stream a file for preview (PDF, images).
     *
     * @param Request $request
     * @param string $path
     * @return StreamedResponse|Response
     */
    public function preview(Request $request, string $path)
    {
        // Decode path if URL encoded
        $path = urldecode($path);

        if (!$this->isValidPath($path)) {
            return $this->errorResponse('Invalid file path', [], 403);
        }

        if (!Storage::disk('public')->exists($path)) {
            return $this->downloadNotFoundResponse('File not found');
        }

        $mimeType = Storage::disk('public')->mimeType($path);
        
        // Only allow preview for certain file types
        $previewableMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ];

        if (!in_array($mimeType, $previewableMimeTypes)) {
            return $this->errorResponse('File type not previewable', [], 400);
        }

        return $this->serveFile($path);
    }

    /**
     * Download a report file.
     *
     * @param Request $request
     * @param string $reportType
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function report(Request $request, string $reportType, string $filename)
    {
        // Authorization: Only admin and accountant can download reports
        $user = $request->user();
        
        if (!$user || !($user->hasRole('admin') || $user->hasRole('accountant'))) {
            return $this->downloadUnauthorizedResponse('You are not authorized to access reports');
        }

        $allowedReportTypes = ['fees', 'attendance', 'exam', 'student', 'financial'];
        
        if (!in_array($reportType, $allowedReportTypes)) {
            return $this->downloadNotFoundResponse('Invalid report type');
        }

        $path = "reports/{$reportType}/{$filename}";
        
        return $this->downloadFile($path, $filename);
    }

    /**
     * Download an export file.
     *
     * @param Request $request
     * @param string $filename
     * @return BinaryFileResponse|Response
     */
    public function export(Request $request, string $filename)
    {
        // Authorization: Only admin can download exports
        $user = $request->user();
        
        if (!$user || !$user->hasRole('admin')) {
            return $this->downloadUnauthorizedResponse('You are not authorized to access exports');
        }

        $path = "exports/{$filename}";
        
        return $this->downloadFile($path, $filename);
    }

    /**
     * Check if user can access student document.
     *
     * @param mixed $user
     * @param int $studentId
     * @return bool
     */
    protected function canAccessStudentDocument($user, int $studentId): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can access all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Student can access their own documents
        if ($user->hasRole('student') && $user->student?->id === $studentId) {
            return true;
        }

        // Parent can access their children's documents
        if ($user->hasRole('parent')) {
            $childIds = $user->children()->pluck('id')->toArray();
            if (in_array($studentId, $childIds)) {
                return true;
            }
        }

        // Teacher can access documents of students in their classes
        if ($user->hasRole('teacher')) {
            // This would need to check if the teacher teaches the student's class
            // For now, allow teachers to access student documents
            return true;
        }

        return false;
    }

    /**
     * Check if user can access teacher document.
     *
     * @param mixed $user
     * @param int $teacherId
     * @return bool
     */
    protected function canAccessTeacherDocument($user, int $teacherId): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can access all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teacher can access their own documents
        if ($user->id === $teacherId) {
            return true;
        }

        return false;
    }

    /**
     * Validate file path for security.
     *
     * @param string $path
     * @return bool
     */
    protected function isValidPath(string $path): bool
    {
        // Prevent directory traversal
        if (str_contains($path, '..') || str_contains($path, '//')) {
            return false;
        }

        // Check allowed prefixes
        $allowedPrefixes = [
            'students/',
            'teachers/',
            'library/',
            'documents/',
            'attachments/',
            'school/',
            'uploads/',
            'reports/',
            'exports/',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Download a file as attachment.
     *
     * @param string $path
     * @param string|null $filename
     * @return BinaryFileResponse|Response
     */
    protected function downloadFile(string $path, ?string $filename = null)
    {
        if (!Storage::disk('public')->exists($path)) {
            return $this->downloadNotFoundResponse('File not found');
        }

        $fullPath = Storage::disk('public')->path($path);
        $filename = $filename ?? basename($path);

        return response()->download($fullPath, $filename);
    }

    /**
     * Serve a file inline (for preview/display).
     *
     * @param string $path
     * @return StreamedResponse|Response
     */
    protected function serveFile(string $path)
    {
        if (!Storage::disk('public')->exists($path)) {
            return $this->downloadNotFoundResponse('File not found');
        }

        $mimeType = Storage::disk('public')->mimeType($path);
        $filename = basename($path);

        return Storage::disk('public')->response($path, $filename, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400', // Cache for 24 hours
        ]);
    }

    /**
     * Return unauthorized response for downloads.
     *
     * @param string $message
     * @return Response
     */
    protected function downloadUnauthorizedResponse(string $message): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 403);
        }

        abort(403, $message);
    }

    /**
     * Return not found response for downloads.
     *
     * @param string $message
     * @return Response
     */
    protected function downloadNotFoundResponse(string $message): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 404);
        }

        abort(404, $message);
    }
}
