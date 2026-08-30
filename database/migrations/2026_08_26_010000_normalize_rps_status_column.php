<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rps', 'status')) {
            Schema::table('rps', function (Blueprint $table) {
                $table->integer('status')->default(1)->after('file');
            });

            return;
        }

        DB::table('rps')->whereNull('status')->update(['status' => 1]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `rps` MODIFY `status` INT NOT NULL DEFAULT 1');
        } else {
            Schema::table('rps', function (Blueprint $table) {
                $table->integer('status')->default(1)->change();
            });
        }
    }

    public function down(): void
    {
        // Default dipertahankan agar upload RPS lama tidak kembali gagal.
    }
};
