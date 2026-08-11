<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_calendar_notes', function (Blueprint $table) {
            $table->bigIncrements('note_id');
            $table->string('owner_type', 20);
            $table->unsignedBigInteger('owner_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('note_date');
            $table->time('note_time')->nullable();
            $table->string('color', 20)->default('primary');
            $table->timestamps();

            $table->index(['owner_type', 'owner_id', 'note_date'], 'lms_calendar_note_owner_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_calendar_notes');
    }
};
