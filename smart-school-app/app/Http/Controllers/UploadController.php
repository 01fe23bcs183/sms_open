<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Upload Controller
 * 
 * Prompt 300: Implement File Upload Endpoints
 * 
 * Handles file uploads for various modules including:
 * - Student photos and documents
 * - Teacher photos and documents
 * - Library book covers
 * - General document uploads
 * 
 * Supports image optimization and validation.
 */
class UploadController extends Controller
{
    /**
     * Allowed image mime types.
     *
     * @var array
     */
    protected array $allowedImageTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Allowed document mime types.
     *
     * @var array
     */
    protected array $allowedDocumentTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv',
    ];

    /**
     * Maximum file sizes in bytes.
     *
     * @var array
     */
    protected array $maxFileSizes = [
        'image' => 2 * 1024 * 1024, // 2MB
        'document' => 10 * 1024 * 1024, // 10MB
        'photo' => 1 * 1024 * 1024, // 1MB
    ];

    /**
     * Upload a student photo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function studentPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'student_id' => 'nullable|exists:students,id',
        ]);

        return $this->uploadImage(
            $request->file('photo'),
            'students/photos',
            [
                'width' => 300,
                'height' => 300,
                'quality' => 85,
            ]
        );
    }

    /**
     * Upload a student document.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function studentDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'student_id' => 'nullable|exists:students,id',
            'document_type' => 'nullable|string|max:100',
        ]);

        return $this->uploadDocument(
            $request->file('document'),
            'students/documents'
        );
    }

    /**
     * Upload a teacher photo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teacherPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        return $this->uploadImage(
            $request->file('photo'),
            'teachers/photos',
            [
                'width' => 300,
                'height' => 300,
                'quality' => 85,
            ]
        );
    }

    /**
     * Upload a teacher document.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teacherDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'teacher_id' => 'nullable|exists:users,id',
            'document_type' => 'nullable|string|max:100',
        ]);

        return $this->uploadDocument(
            $request->file('document'),
            'teachers/documents'
        );
    }

    /**
     * Upload a library book cover.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bookCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'book_id' => 'nullable|exists:library_books,id',
        ]);

        return $this->uploadImage(
            $request->file('cover'),
            'library/covers',
            [
                'width' => 400,
                'height' => 600,
                'quality' => 85,
            ]
        );
    }

    /**
     * Upload a general document.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function document(Request $request): JsonResponse
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png|max:10240',
            'folder' => 'nullable|string|max:100',
        ]);

        $folder = $request->input('folder', 'documents');
        $folder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $folder);

        return $this->uploadDocument(
            $request->file('document'),
            $folder
        );
    }

    /**
     * Upload school logo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function schoolLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        return $this->uploadImage(
            $request->file('logo'),
            'school',
            [
                'width' => 500,
                'height' => 500,
                'quality' => 90,
                'filename' => 'logo',
            ]
        );
    }

    /**
     * Upload notice/event attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attachment(Request $request): JsonResponse
    {
        $request->validate([
            'attachment' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240',
            'type' => 'nullable|in:notice,event,homework,assignment',
        ]);

        $type = $request->input('type', 'attachments');

        return $this->uploadDocument(
            $request->file('attachment'),
            "attachments/{$type}"
        );
    }

    /**
     * Upload multiple files.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function multiple(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240',
            'folder' => 'nullable|string|max:100',
        ]);

        $folder = $request->input('folder', 'uploads');
        $folder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $folder);

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                $isImage = in_array($file->getMimeType(), $this->allowedImageTypes);
                
                if ($isImage) {
                    $result = $this->processImageUpload($file, $folder);
                } else {
                    $result = $this->processDocumentUpload($file, $folder);
                }
                
                $uploadedFiles[] = $result;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->successResponse([
            'uploaded' => $uploadedFiles,
            'errors' => $errors,
            'total_uploaded' => count($uploadedFiles),
            'total_errors' => count($errors),
        ], count($errors) > 0 ? 'Some files failed to upload' : 'Files uploaded successfully');
    }

    /**
     * Delete an uploaded file.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Security: Ensure path is within allowed directories
        $allowedPrefixes = [
            'students/',
            'teachers/',
            'library/',
            'documents/',
            'attachments/',
            'school/',
            'uploads/',
        ];

        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            return $this->errorResponse('Invalid file path', [], 403);
        }

        if (!Storage::disk('public')->exists($path)) {
            return $this->notFoundResponse('File not found');
        }

        Storage::disk('public')->delete($path);

        return $this->successResponse(null, 'File deleted successfully');
    }

    /**
     * Upload and process an image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return JsonResponse
     */
    protected function uploadImage($file, string $folder, array $options = []): JsonResponse
    {
        try {
            $result = $this->processImageUpload($file, $folder, $options);
            return $this->successResponse($result, 'Image uploaded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload image: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Process image upload with optimization.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return array
     */
    protected function processImageUpload($file, string $folder, array $options = []): array
    {
        $width = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        $quality = $options['quality'] ?? 85;
        $filename = $options['filename'] ?? null;

        // Generate filename
        if (!$filename) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        } else {
            $filename = $filename . '.' . $file->getClientOriginalExtension();
        }

        $path = "{$folder}/{$filename}";

        // Check if Intervention Image is available
        if (class_exists('Intervention\Image\Laravel\Facades\Image') && ($width || $height)) {
            // Process with Intervention Image
            $image = Image::read($file);
            
            if ($width && $height) {
                $image->cover($width, $height);
            } elseif ($width) {
                $image->scale(width: $width);
            } elseif ($height) {
                $image->scale(height: $height);
            }

            $encoded = $image->toJpeg($quality);
            Storage::disk('public')->put($path, $encoded);
        } else {
            // Store without processing
            Storage::disk('public')->putFileAs($folder, $file, $filename);
        }

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload a document.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return JsonResponse
     */
    protected function uploadDocument($file, string $folder): JsonResponse
    {
        try {
            $result = $this->processDocumentUpload($file, $folder);
            return $this->successResponse($result, 'Document uploaded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload document: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Process document upload.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return array
     */
    protected function processDocumentUpload($file, string $folder): array
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "{$folder}/{$filename}";

        Storage::disk('public')->putFileAs($folder, $file, $filename);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
        ];
    }

    /**
     * Get file info.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        if (!Storage::disk('public')->exists($path)) {
            return $this->notFoundResponse('File not found');
        }

        return $this->successResponse([
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'size' => Storage::disk('public')->size($path),
            'last_modified' => Storage::disk('public')->lastModified($path),
            'mime_type' => Storage::disk('public')->mimeType($path),
        ], 'File info retrieved');
    }
}
