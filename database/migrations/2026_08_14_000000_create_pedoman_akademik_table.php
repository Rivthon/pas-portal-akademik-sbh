<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedoman_akademik', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('tahun_berlaku', 30)->nullable();
            $table->string('nama_file');
            $table->string('path');
            $table->boolean('status')->default(false)->index();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedoman_akademik');
    }
};
