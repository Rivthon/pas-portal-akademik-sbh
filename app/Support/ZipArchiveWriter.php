<?php

namespace App\Support;

use RuntimeException;

class ZipArchiveWriter
{
    private $handle;

    private array $entries = [];

    private string $path;

    private bool $finished = false;

    public function __construct()
    {
        $path = tempnam(storage_path('app'), 'arsip-krs-');
        if ($path === false) {
            throw new RuntimeException('Gagal membuat file ZIP sementara.');
        }

        $handle = fopen($path, 'w+b');
        if ($handle === false) {
            @unlink($path);
            throw new RuntimeException('Gagal membuka file ZIP sementara.');
        }

        $this->path = $path;
        $this->handle = $handle;
    }

    public function addFile(string $name, string $contents): void
    {
        $name = str_replace('\\', '/', ltrim($name, '/'));
        $offset = ftell($this->handle);
        $size = strlen($contents);
        $crc = crc32($contents);
        [$dosTime, $dosDate] = $this->dosTimestamp();

        $header = pack(
            'VvvvvvVVVvv',
            0x04034b50,
            20,
            0,
            0,
            $dosTime,
            $dosDate,
            $crc,
            $size,
            $size,
            strlen($name),
            0
        );

        fwrite($this->handle, $header.$name.$contents);
        $this->entries[] = compact('name', 'offset', 'size', 'crc', 'dosTime', 'dosDate');
    }

    public function finish(): string
    {
        $centralOffset = ftell($this->handle);

        foreach ($this->entries as $entry) {
            $header = pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                $entry['dosTime'],
                $entry['dosDate'],
                $entry['crc'],
                $entry['size'],
                $entry['size'],
                strlen($entry['name']),
                0,
                0,
                0,
                0,
                0,
                $entry['offset']
            );

            fwrite($this->handle, $header.$entry['name']);
        }

        $centralSize = ftell($this->handle) - $centralOffset;
        $entryCount = count($this->entries);
        fwrite($this->handle, pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $entryCount,
            $entryCount,
            $centralSize,
            $centralOffset,
            0
        ));
        fclose($this->handle);
        $this->handle = null;
        $this->finished = true;

        return $this->path;
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }

        if (! $this->finished && isset($this->path) && is_file($this->path)) {
            @unlink($this->path);
        }
    }

    private function dosTimestamp(): array
    {
        $now = getdate();
        $year = max(1980, (int) $now['year']);

        return [
            ((int) $now['hours'] << 11) | ((int) $now['minutes'] << 5) | ((int) $now['seconds'] >> 1),
            (($year - 1980) << 9) | ((int) $now['mon'] << 5) | (int) $now['mday'],
        ];
    }
}
