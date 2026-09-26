<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_materi', function (Blueprint $table) {
            $table->string('tipe', 30)->default('lainnya')->change();
        });
    }

    public function down(): void
    {
        Schema::table('lms_materi', function (Blueprint $table) {
            $table->enum('tipe', ['pdf', 'ppt', 'doc', 'video', 'youtube'])->change();
        });
    }
};
