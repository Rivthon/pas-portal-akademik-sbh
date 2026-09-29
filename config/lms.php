<?php

return [
    'uploads' => [
        // Samakan dengan upload_max_filesize dan post_max_size pada VPS.
        'max_kilobytes' => (int) env('LMS_UPLOAD_MAX_KB', 51200),
        'extensions' => [
            'pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx',
            'zip', 'rar', 'jpg', 'jpeg', 'png', 'mp4', 'avi', 'mov', 'mkv',
        ],
        'document_extensions' => [
            'pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx',
            'zip', 'rar', 'jpg', 'jpeg', 'png',
        ],
    ],
    'temporary_task_upload' => [
        // Dapat dimatikan sewaktu-waktu tanpa mengubah kode.
        'enabled' => env('LMS_TEMPORARY_TASK_UPLOAD_ENABLED', true),
        'max_kilobytes' => (int) env('LMS_TASK_UPLOAD_MAX_KB', 10240),
        'expires_minutes' => (int) env('LMS_TEMPORARY_UPLOAD_EXPIRES_MINUTES', 120),
    ],
];
