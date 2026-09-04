<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_id')->unique();
            $table->string('program_studi_id', 20)->index();
            $table->string('submitted_by_dosen_id', 30)->nullable();
            $table->string('status', 20)->default('submitted')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->string('reviewed_by_dosen_id', 30)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });
        Schema::create('kaprodi_absensi_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_id')->unique();
            $table->string('program_studi_id', 20)->index();
            $table->string('verified_by_dosen_id', 30);
            $table->timestamp('verified_at');
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
        });
        Schema::create('khs_publications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ta_id');
            $table->string('program_studi_id', 20);
            $table->unsignedBigInteger('published_by_user_id')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();
            $table->unique(['ta_id', 'program_studi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khs_publications');
        Schema::dropIfExists('kaprodi_absensi_verifications');
        Schema::dropIfExists('nilai_submissions');
    }
};
