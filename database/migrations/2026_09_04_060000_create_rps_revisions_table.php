<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rps_id');
            $table->integer('kurikulum_id');
            $table->string('jenis_kelas', 30);
            $table->integer('previous_dosen_id')->nullable();
            $table->integer('uploaded_by_dosen_id');
            $table->string('previous_file_name')->nullable();
            $table->string('new_file_name')->nullable();
            $table->timestamps();

            $table->index(['kurikulum_id', 'jenis_kelas'], 'rps_revisions_course_class_index');
            $table->index(['uploaded_by_dosen_id', 'created_at'], 'rps_revisions_uploader_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_revisions');
    }
};
