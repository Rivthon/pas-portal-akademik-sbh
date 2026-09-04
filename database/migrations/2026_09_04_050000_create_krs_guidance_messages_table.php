<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('krs_guidance_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('dosen_id', 50);
            $table->unsignedBigInteger('ta_id');
            $table->string('sender_type', 20);
            $table->text('message');
            $table->timestamps();

            $table->index(['mahasiswa_id', 'ta_id'], 'krs_guidance_student_ta_index');
            $table->index(['dosen_id', 'ta_id'], 'krs_guidance_dosen_ta_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('krs_guidance_messages');
    }
};
