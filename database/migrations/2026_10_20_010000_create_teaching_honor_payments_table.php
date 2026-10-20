<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_honor_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->index();
            $table->integer('dosen_id')->index();
            $table->unsignedBigInteger('ta_id')->nullable()->index();
            $table->date('period_start');
            $table->date('period_end')->index();
            $table->string('meeting_type', 10);
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedBigInteger('schedule_id');
            $table->date('meeting_date');
            $table->string('course_name')->nullable();
            $table->string('study_program_name')->nullable();
            $table->string('class_name', 50)->nullable();
            $table->text('topic')->nullable();
            $table->dateTime('paid_at');
            // Database lama hasil impor VPS tidak selalu mempunyai index PRIMARY pada users.id,
            // sehingga relasi dijaga oleh aplikasi dan kolom tetap di-index untuk lookup audit.
            $table->unsignedBigInteger('paid_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['meeting_type', 'meeting_id'], 'honor_meeting_once_unique');
            $table->index(['dosen_id', 'ta_id', 'period_start', 'period_end'], 'honor_period_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_honor_payments');
    }
};
