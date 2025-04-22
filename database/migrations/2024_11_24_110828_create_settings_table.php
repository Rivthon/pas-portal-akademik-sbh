<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Nama aplikasi
            $table->string('logo')->nullable(); // Logo aplikasi
            $table->string('favicon')->nullable(); // Favicon aplikasi
            $table->string('footer_name')->nullable(); // Footer aplikasi
            $table->text('copyright')->nullable(); // Hak cipta
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
}
