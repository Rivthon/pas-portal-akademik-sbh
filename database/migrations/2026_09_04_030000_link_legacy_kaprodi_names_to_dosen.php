<?php

use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ProgramStudi::whereNull('kaprodi_dosen_id')->whereNotNull('kaprod')->each(function (ProgramStudi $programStudi) {
            $matches = Dosen::where('nama', $programStudi->kaprod)->pluck('dosen_id');
            if ($matches->count() === 1) {
                $programStudi->update(['kaprodi_dosen_id' => $matches->first()]);
            }
        });
    }

    public function down(): void
    {
        // Tautan yang mungkin sudah disunting admin tidak dihapus saat rollback.
    }
};
