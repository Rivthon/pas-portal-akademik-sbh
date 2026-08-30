<?php

return [
    'backup' => [
        'disk' => env('SYSTEM_BACKUP_DISK', 'private'),
        'directory' => env('SYSTEM_BACKUP_DIRECTORY', 'system-backups'),
        'retention_days' => (int) env('SYSTEM_BACKUP_RETENTION_DAYS', 14),
        'schedule' => env('SYSTEM_BACKUP_SCHEDULE', '01:30'),
        'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
        'include_uploads' => env('SYSTEM_BACKUP_INCLUDE_UPLOADS', true),
    ],

    'monitoring' => [
        'scheduler_stale_minutes' => (int) env('SYSTEM_SCHEDULER_STALE_MINUTES', 5),
        'backup_stale_hours' => (int) env('SYSTEM_BACKUP_STALE_HOURS', 36),
        'error_limit' => (int) env('SYSTEM_ERROR_LOG_LIMIT', 20),
    ],
];
