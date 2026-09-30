<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lms_tugas', 'cakupan')) {
            Schema::table('lms_tugas', function (Blueprint $table) {
                $table->string('cakupan', 20)->default('semua')->after('tipe')->index();
            });
        }

        if (Schema::hasTable('lms_tugas_mahasiswa')) {
            $hasMahasiswaForeign = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'lms_tugas_mahasiswa')
                ->where('CONSTRAINT_NAME', 'lms_tugas_mahasiswa_mahasiswa_id_foreign')
                ->exists();

            if (! $hasMahasiswaForeign) {
                DB::statement('ALTER TABLE lms_tugas_mahasiswa MODIFY mahasiswa_id INT NOT NULL');
                Schema::table('lms_tugas_mahasiswa', function (Blueprint $table) {
                    $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->cascadeOnDelete();
                });
            }

            return;
        }

        Schema::create('lms_tugas_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tugas_id');
            $table->integer('mahasiswa_id');
            $table->timestamps();

            $table->unique(['tugas_id', 'mahasiswa_id'], 'lms_tugas_mahasiswa_unique');
            $table->foreign('tugas_id')->references('tugas_id')->on('lms_tugas')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_tugas_mahasiswa');

        if (Schema::hasColumn('lms_tugas', 'cakupan')) {
            Schema::table('lms_tugas', function (Blueprint $table) {
                $table->dropIndex(['cakupan']);
                $table->dropColumn('cakupan');
            });
        }
    }
};
