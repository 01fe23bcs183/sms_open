<?php

/**
 * Upload Validation Rules Configuration
 * 
 * Prompt 391: Add Upload Validation Rules Map
 * 
 * Standardizes upload rules across modules.
 * Provides per-module size/type restrictions.
 * Used by FileUploadService for validation.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Upload Settings
    |--------------------------------------------------------------------------
    |
    | These settings are used when no specific module rules are defined.
    |
    */

    'default' => [
        'max_size' => 5120, // 5MB in KB
        'mimes' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module-Specific Upload Rules
    |--------------------------------------------------------------------------
    |
    | Define upload rules for each module. Each rule set can include:
    | - max_size: Maximum file size in KB
    | - mimes: Allowed file extensions
    | - mime_types: Allowed MIME types (optional, for stricter validation)
    |
    */

    'rules' => [

        /*
        |--------------------------------------------------------------------------
        | Student Module
        |--------------------------------------------------------------------------
        */

        'student_photo' => [
            'max_size' => 2048, // 2MB
            'mimes' => ['jpg', 'jpeg', 'png'],
            'mime_types' => ['image/jpeg', 'image/png'],
        ],

        'student_document' => [
            'max_size' => 10240, // 10MB
            'mimes' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Teacher/Staff Module
        |--------------------------------------------------------------------------
        */

        'teacher_photo' => [
            'max_size' => 2048, // 2MB
            'mimes' => ['jpg', 'jpeg', 'png'],
            'mime_types' => ['image/jpeg', 'image/png'],
        ],

        'teacher_document' => [
            'max_size' => 10240, // 10MB
            'mimes' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Library Module
        |--------------------------------------------------------------------------
        */

        'book_cover' => [
            'max_size' => 2048, // 2MB
            'mimes' => ['jpg', 'jpeg', 'png'],
            'mime_types' => ['image/jpeg', 'image/png'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Academic Module
        |--------------------------------------------------------------------------
        */

        'homework_attachment' => [
            'max_size' => 10240, // 10MB
            'mimes' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
        ],

        'study_material' => [
            'max_size' => 20480, // 20MB
            'mimes' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp4', 'mp3', 'zip'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'video/mp4',
                'audio/mpeg',
                'application/zip',
            ],
        ],

        'assignment_submission' => [
            'max_size' => 10240, // 10MB
            'mimes' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'application/zip',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Communication Module
        |--------------------------------------------------------------------------
        */

        'notice_attachment' => [
            'max_size' => 5120, // 5MB
            'mimes' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
        ],

        'message_attachment' => [
            'max_size' => 5120, // 5MB
            'mimes' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
        ],

        'event_attachment' => [
            'max_size' => 5120, // 5MB
            'mimes' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Finance Module
        |--------------------------------------------------------------------------
        */

        'payment_proof' => [
            'max_size' => 5120, // 5MB
            'mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
            'mime_types' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
            ],
        ],

        'expense_receipt' => [
            'max_size' => 5120, // 5MB
            'mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
            'mime_types' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Transport Module
        |--------------------------------------------------------------------------
        */

        'vehicle_document' => [
            'max_size' => 5120, // 5MB
            'mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
            'mime_types' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Hostel Module
        |--------------------------------------------------------------------------
        */

        'hostel_room_image' => [
            'max_size' => 2048, // 2MB
            'mimes' => ['jpg', 'jpeg', 'png'],
            'mime_types' => ['image/jpeg', 'image/png'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Settings Module
        |--------------------------------------------------------------------------
        */

        'school_logo' => [
            'max_size' => 2048, // 2MB
            'mimes' => ['jpg', 'jpeg', 'png', 'svg'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/svg+xml'],
        ],

        'school_favicon' => [
            'max_size' => 512, // 512KB
            'mimes' => ['ico', 'png'],
            'mime_types' => ['image/x-icon', 'image/png'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Import/Export Module
        |--------------------------------------------------------------------------
        */

        'import_file' => [
            'max_size' => 10240, // 10MB
            'mimes' => ['csv', 'xls', 'xlsx'],
            'mime_types' => [
                'text/csv',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | General Documents
        |--------------------------------------------------------------------------
        */

        'general_document' => [
            'max_size' => 10240, // 10MB
            'mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'jpg', 'jpeg', 'png'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'text/csv',
                'image/jpeg',
                'image/png',
            ],
        ],

        'general_image' => [
            'max_size' => 2048, // 2MB
            'mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'mime_types' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Image Dimensions
    |--------------------------------------------------------------------------
    |
    | Default dimensions for image optimization.
    |
    */

    'dimensions' => [

        'student_photo' => [
            'width' => 300,
            'height' => 300,
            'quality' => 85,
        ],

        'teacher_photo' => [
            'width' => 300,
            'height' => 300,
            'quality' => 85,
        ],

        'book_cover' => [
            'width' => 400,
            'height' => 600,
            'quality' => 85,
        ],

        'school_logo' => [
            'width' => 500,
            'height' => 500,
            'quality' => 90,
        ],

        'hostel_room' => [
            'width' => 800,
            'height' => 600,
            'quality' => 85,
        ],

        'thumbnail' => [
            'width' => 150,
            'height' => 150,
            'quality' => 80,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | Default storage paths for different file types.
    |
    */

    'paths' => [

        // Public paths (accessible via URL)
        'public' => [
            'student_photos' => 'students/photos',
            'teacher_photos' => 'teachers/photos',
            'book_covers' => 'library/covers',
            'school_assets' => 'school',
            'hostel_images' => 'hostel/rooms',
        ],

        // Private paths (require authorization)
        'private' => [
            'student_documents' => 'students/documents',
            'teacher_documents' => 'teachers/documents',
            'homework' => 'homework',
            'study_materials' => 'study_materials',
            'communications' => 'communications',
            'payment_proofs' => 'fees/proofs',
            'expense_receipts' => 'expenses/receipts',
            'vehicle_documents' => 'transport/documents',
            'reports' => 'reports',
            'exports' => 'exports',
            'imports' => 'imports',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Settings for temporary file cleanup.
    |
    */

    'cleanup' => [
        'temp_folder' => 'temp',
        'max_age_days' => 7, // Delete temp files older than 7 days
        'orphan_check_enabled' => true,
    ],

];
