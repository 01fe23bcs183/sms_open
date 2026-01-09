<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * File Upload Service
 * 
 * Prompt 389: Create File Upload Service
 * 
 * Centralized file upload validation and storage service.
 * Handles uploads, naming, directory structure, and cleanup.
 * 
 * Features:
 * - Public and private file uploads
 * - Image optimization and resizing
 * - Unique filename generation with timestamps/UUID
 * - File replacement with old file cleanup
 * - Validation based on config/uploads.php rules
 */
class FileUploadService
{
    /**
     * Upload a file to public storage.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return array
     */
    public function uploadPublic(UploadedFile $file, string $folder, array $options = []): array
    {
        return $this->upload($file, $folder, 'public_uploads', $options);
    }

    /**
     * Upload a file to private storage.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return array
     */
    public function uploadPrivate(UploadedFile $file, string $folder, array $options = []): array
    {
        return $this->upload($file, $folder, 'private_uploads', $options);
    }

    /**
     * Upload a file to the specified disk.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $disk
     * @param array $options
     * @return array
     */
    protected function upload(UploadedFile $file, string $folder, string $disk, array $options = []): array
    {
        $filename = $this->generateFilename($file, $options);
        $path = "{$folder}/{$filename}";

        if ($this->isImage($file) && $this->shouldOptimizeImage($options)) {
            $this->uploadOptimizedImage($file, $path, $disk, $options);
        } else {
            Storage::disk($disk)->putFileAs($folder, $file, $filename);
        }

        $result = [
            'path' => $path,
            'disk' => $disk,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
        ];

        if ($disk === 'public_uploads' || $disk === 'public') {
            $result['url'] = Storage::disk($disk)->url($path);
        }

        return $result;
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk = 'public_uploads'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Replace an existing file with a new one.
     *
     * @param UploadedFile $file
     * @param string|null $oldPath
     * @param string $folder
     * @param string $disk
     * @param array $options
     * @return array
     */
    public function replace(UploadedFile $file, ?string $oldPath, string $folder, string $disk = 'public_uploads', array $options = []): array
    {
        if ($oldPath) {
            $this->delete($oldPath, $disk);
        }

        return $this->upload($file, $folder, $disk, $options);
    }

    /**
     * Generate a unique filename.
     *
     * @param UploadedFile $file
     * @param array $options
     * @return string
     */
    protected function generateFilename(UploadedFile $file, array $options = []): string
    {
        if (isset($options['filename'])) {
            return $options['filename'] . '.' . $file->getClientOriginalExtension();
        }

        $prefix = $options['prefix'] ?? '';
        $timestamp = now()->format('Ymd_His');
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();

        if ($prefix) {
            return "{$prefix}_{$timestamp}_{$uuid}.{$extension}";
        }

        return "{$timestamp}_{$uuid}.{$extension}";
    }

    /**
     * Check if file is an image.
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Check if image should be optimized.
     *
     * @param array $options
     * @return bool
     */
    protected function shouldOptimizeImage(array $options): bool
    {
        return isset($options['width']) || isset($options['height']) || isset($options['quality']);
    }

    /**
     * Upload and optimize an image.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $disk
     * @param array $options
     * @return void
     */
    protected function uploadOptimizedImage(UploadedFile $file, string $path, string $disk, array $options): void
    {
        $width = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        $quality = $options['quality'] ?? 85;

        if (class_exists('Intervention\Image\Laravel\Facades\Image')) {
            $image = Image::read($file);

            if ($width && $height) {
                $image->cover($width, $height);
            } elseif ($width) {
                $image->scale(width: $width);
            } elseif ($height) {
                $image->scale(height: $height);
            }

            $encoded = $image->toJpeg($quality);
            Storage::disk($disk)->put($path, $encoded);
        } else {
            Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));
        }
    }

    /**
     * Validate a file against upload rules.
     *
     * @param UploadedFile $file
     * @param string $type
     * @return array
     */
    public function validate(UploadedFile $file, string $type): array
    {
        $rules = config("uploads.rules.{$type}", config('uploads.rules.default', []));
        $errors = [];

        if (isset($rules['max_size'])) {
            $maxSizeBytes = $rules['max_size'] * 1024;
            if ($file->getSize() > $maxSizeBytes) {
                $errors[] = "File size exceeds maximum allowed size of {$rules['max_size']}KB";
            }
        }

        if (isset($rules['mimes'])) {
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedMimes = array_map('strtolower', $rules['mimes']);
            if (!in_array($extension, $allowedMimes)) {
                $errors[] = "File type '{$extension}' is not allowed. Allowed types: " . implode(', ', $rules['mimes']);
            }
        }

        if (isset($rules['mime_types'])) {
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $rules['mime_types'])) {
                $errors[] = "MIME type '{$mimeType}' is not allowed";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get validation rules for a specific upload type.
     *
     * @param string $type
     * @return array
     */
    public function getValidationRules(string $type): array
    {
        $rules = config("uploads.rules.{$type}", config('uploads.rules.default', []));
        $laravelRules = ['required', 'file'];

        if (isset($rules['max_size'])) {
            $laravelRules[] = 'max:' . $rules['max_size'];
        }

        if (isset($rules['mimes'])) {
            $laravelRules[] = 'mimes:' . implode(',', $rules['mimes']);
        }

        return $laravelRules;
    }

    /**
     * Get file info from storage.
     *
     * @param string $path
     * @param string $disk
     * @return array|null
     */
    public function getFileInfo(string $path, string $disk = 'public_uploads'): ?array
    {
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return [
            'path' => $path,
            'disk' => $disk,
            'size' => Storage::disk($disk)->size($path),
            'last_modified' => Storage::disk($disk)->lastModified($path),
            'mime_type' => Storage::disk($disk)->mimeType($path),
            'url' => $disk === 'public_uploads' || $disk === 'public' 
                ? Storage::disk($disk)->url($path) 
                : null,
        ];
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function exists(string $path, string $disk = 'public_uploads'): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get the full path to a file.
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    public function getFullPath(string $path, string $disk = 'public_uploads'): string
    {
        return Storage::disk($disk)->path($path);
    }

    /**
     * Get the URL for a public file.
     *
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public function getUrl(string $path, string $disk = 'public_uploads'): ?string
    {
        if ($disk !== 'public_uploads' && $disk !== 'public') {
            return null;
        }

        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Move a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @param string $disk
     * @return bool
     */
    public function move(string $from, string $to, string $disk = 'public_uploads'): bool
    {
        if (!Storage::disk($disk)->exists($from)) {
            return false;
        }

        return Storage::disk($disk)->move($from, $to);
    }

    /**
     * Copy a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @param string $disk
     * @return bool
     */
    public function copy(string $from, string $to, string $disk = 'public_uploads'): bool
    {
        if (!Storage::disk($disk)->exists($from)) {
            return false;
        }

        return Storage::disk($disk)->copy($from, $to);
    }

    /**
     * Get all files in a directory.
     *
     * @param string $directory
     * @param string $disk
     * @return array
     */
    public function listFiles(string $directory, string $disk = 'public_uploads'): array
    {
        return Storage::disk($disk)->files($directory);
    }

    /**
     * Delete all files in a directory.
     *
     * @param string $directory
     * @param string $disk
     * @return bool
     */
    public function deleteDirectory(string $directory, string $disk = 'public_uploads'): bool
    {
        return Storage::disk($disk)->deleteDirectory($directory);
    }

    /**
     * Create a directory if it doesn't exist.
     *
     * @param string $directory
     * @param string $disk
     * @return bool
     */
    public function ensureDirectoryExists(string $directory, string $disk = 'public_uploads'): bool
    {
        if (!Storage::disk($disk)->exists($directory)) {
            return Storage::disk($disk)->makeDirectory($directory);
        }

        return true;
    }

    /**
     * Upload student photo with standard settings.
     *
     * @param UploadedFile $file
     * @param int|null $studentId
     * @return array
     */
    public function uploadStudentPhoto(UploadedFile $file, ?int $studentId = null): array
    {
        $options = [
            'width' => config('uploads.dimensions.student_photo.width', 300),
            'height' => config('uploads.dimensions.student_photo.height', 300),
            'quality' => config('uploads.dimensions.student_photo.quality', 85),
        ];

        if ($studentId) {
            $options['prefix'] = "student_{$studentId}";
        }

        return $this->uploadPublic($file, 'students/photos', $options);
    }

    /**
     * Upload student document with standard settings.
     *
     * @param UploadedFile $file
     * @param int|null $studentId
     * @return array
     */
    public function uploadStudentDocument(UploadedFile $file, ?int $studentId = null): array
    {
        $options = [];

        if ($studentId) {
            $options['prefix'] = "student_{$studentId}";
        }

        return $this->uploadPrivate($file, 'students/documents', $options);
    }

    /**
     * Upload teacher photo with standard settings.
     *
     * @param UploadedFile $file
     * @param int|null $teacherId
     * @return array
     */
    public function uploadTeacherPhoto(UploadedFile $file, ?int $teacherId = null): array
    {
        $options = [
            'width' => config('uploads.dimensions.teacher_photo.width', 300),
            'height' => config('uploads.dimensions.teacher_photo.height', 300),
            'quality' => config('uploads.dimensions.teacher_photo.quality', 85),
        ];

        if ($teacherId) {
            $options['prefix'] = "teacher_{$teacherId}";
        }

        return $this->uploadPublic($file, 'teachers/photos', $options);
    }

    /**
     * Upload teacher document with standard settings.
     *
     * @param UploadedFile $file
     * @param int|null $teacherId
     * @return array
     */
    public function uploadTeacherDocument(UploadedFile $file, ?int $teacherId = null): array
    {
        $options = [];

        if ($teacherId) {
            $options['prefix'] = "teacher_{$teacherId}";
        }

        return $this->uploadPrivate($file, 'teachers/documents', $options);
    }

    /**
     * Upload library book cover with standard settings.
     *
     * @param UploadedFile $file
     * @param int|null $bookId
     * @return array
     */
    public function uploadBookCover(UploadedFile $file, ?int $bookId = null): array
    {
        $options = [
            'width' => config('uploads.dimensions.book_cover.width', 400),
            'height' => config('uploads.dimensions.book_cover.height', 600),
            'quality' => config('uploads.dimensions.book_cover.quality', 85),
        ];

        if ($bookId) {
            $options['prefix'] = "book_{$bookId}";
        }

        return $this->uploadPublic($file, 'library/covers', $options);
    }

    /**
     * Upload homework attachment.
     *
     * @param UploadedFile $file
     * @param int|null $homeworkId
     * @return array
     */
    public function uploadHomeworkAttachment(UploadedFile $file, ?int $homeworkId = null): array
    {
        $options = [];

        if ($homeworkId) {
            $options['prefix'] = "homework_{$homeworkId}";
        }

        return $this->uploadPrivate($file, 'homework', $options);
    }

    /**
     * Upload study material.
     *
     * @param UploadedFile $file
     * @param int|null $materialId
     * @return array
     */
    public function uploadStudyMaterial(UploadedFile $file, ?int $materialId = null): array
    {
        $options = [];

        if ($materialId) {
            $options['prefix'] = "material_{$materialId}";
        }

        return $this->uploadPrivate($file, 'study_materials', $options);
    }

    /**
     * Upload notice/message attachment.
     *
     * @param UploadedFile $file
     * @param string $type
     * @param int|null $itemId
     * @return array
     */
    public function uploadCommunicationAttachment(UploadedFile $file, string $type = 'notice', ?int $itemId = null): array
    {
        $options = [];

        if ($itemId) {
            $options['prefix'] = "{$type}_{$itemId}";
        }

        return $this->uploadPrivate($file, "communications/{$type}", $options);
    }

    /**
     * Upload fee payment proof.
     *
     * @param UploadedFile $file
     * @param int|null $transactionId
     * @return array
     */
    public function uploadPaymentProof(UploadedFile $file, ?int $transactionId = null): array
    {
        $options = [];

        if ($transactionId) {
            $options['prefix'] = "transaction_{$transactionId}";
        }

        return $this->uploadPrivate($file, 'fees/proofs', $options);
    }

    /**
     * Upload transport vehicle document.
     *
     * @param UploadedFile $file
     * @param int|null $vehicleId
     * @return array
     */
    public function uploadVehicleDocument(UploadedFile $file, ?int $vehicleId = null): array
    {
        $options = [];

        if ($vehicleId) {
            $options['prefix'] = "vehicle_{$vehicleId}";
        }

        return $this->uploadPrivate($file, 'transport/documents', $options);
    }

    /**
     * Upload hostel room image.
     *
     * @param UploadedFile $file
     * @param int|null $roomId
     * @return array
     */
    public function uploadHostelRoomImage(UploadedFile $file, ?int $roomId = null): array
    {
        $options = [
            'width' => config('uploads.dimensions.hostel_room.width', 800),
            'height' => config('uploads.dimensions.hostel_room.height', 600),
            'quality' => config('uploads.dimensions.hostel_room.quality', 85),
        ];

        if ($roomId) {
            $options['prefix'] = "room_{$roomId}";
        }

        return $this->uploadPublic($file, 'hostel/rooms', $options);
    }

    /**
     * Upload school logo.
     *
     * @param UploadedFile $file
     * @return array
     */
    public function uploadSchoolLogo(UploadedFile $file): array
    {
        $options = [
            'filename' => 'logo',
            'width' => config('uploads.dimensions.school_logo.width', 500),
            'height' => config('uploads.dimensions.school_logo.height', 500),
            'quality' => config('uploads.dimensions.school_logo.quality', 90),
        ];

        return $this->uploadPublic($file, 'school', $options);
    }
}
