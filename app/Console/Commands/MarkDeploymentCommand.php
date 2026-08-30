<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MarkDeploymentCommand extends Command
{
    protected $signature = 'system:mark-deployed';

    protected $description = 'Mencatat waktu deployment PAS terakhir';

    public function handle(): int
    {
        Storage::disk('local')->put('monitoring/deployment.json', json_encode([
            'deployed_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'app_version' => env('APP_VERSION'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Waktu deployment berhasil dicatat.');

        return self::SUCCESS;
    }
}
