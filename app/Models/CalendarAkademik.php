<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CalendarAkademik extends Model
{
    use HasFactory;

    protected $table = 'calender_akademik';

    protected $fillable = [
        'jurusan_id',
        'nama_file',
        'path',
        'status',
    ];

    /**
     * Relasi ke tabel jurusan.
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    /**
     * Scope untuk mendapatkan kalender akademik yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }

    public function storagePath(): ?string
    {
        if (! $this->path) {
            return null;
        }

        $urlPath = parse_url((string) $this->path, PHP_URL_PATH);
        $path = ltrim(str_replace('\\', '/', is_string($urlPath) ? $urlPath : (string) $this->path), '/');

        foreach (['public/', 'storage/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        return ltrim($path, '/');
    }

    public function fileExists(): bool
    {
        $path = $this->storagePath();

        return $path !== null && $path !== '' && Storage::disk('public')->exists($path);
    }
}
