<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class StoredUpload
{
    public static function disk(?string $path): FilesystemAdapter
    {
        if ($path && Storage::disk('private')->exists($path)) {
            return Storage::disk('private');
        }

        return Storage::disk('public');
    }

    public static function exists(?string $path): bool
    {
        return $path !== null && self::disk($path)->exists($path);
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        foreach (['private', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
