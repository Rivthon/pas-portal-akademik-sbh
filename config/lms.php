<?php

return [
    'temporary_task_upload' => [
        // Dapat dimatikan sewaktu-waktu tanpa mengubah kode.
        'enabled' => env('LMS_TEMPORARY_TASK_UPLOAD_ENABLED', true),
        'max_kilobytes' => (int) env('LMS_TASK_UPLOAD_MAX_KB', 10240),
        'expires_minutes' => (int) env('LMS_TEMPORARY_UPLOAD_EXPIRES_MINUTES', 120),
    ],
];
