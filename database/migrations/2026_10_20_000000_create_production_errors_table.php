<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_errors', function (Blueprint $table) {
            $table->id();
            $table->char('fingerprint', 64)->unique();
            $table->string('exception_class')->nullable();
            $table->text('message');
            $table->string('file', 500)->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->longText('trace')->nullable();
            $table->unsignedSmallInteger('http_status')->default(500);
            $table->string('method', 10)->nullable();
            $table->string('path', 1000)->nullable();
            $table->string('route_name')->nullable();
            $table->string('actor_guard', 20)->nullable();
            $table->string('actor_type', 50)->nullable();
            $table->string('actor_id', 100)->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_identifier')->nullable();
            $table->json('context')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at');
            $table->timestamps();

            $table->index('last_seen_at');
            $table->index(['actor_guard', 'actor_id']);
            $table->index('http_status');
        });

        Schema::create('production_error_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_error_id')
                ->constrained('production_errors')
                ->cascadeOnDelete();
            $table->string('actor_guard', 20)->nullable();
            $table->string('actor_type', 50)->nullable();
            $table->string('actor_id', 100)->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_identifier')->nullable();
            $table->json('context')->nullable();
            $table->dateTime('occurred_at');

            $table->index('occurred_at');
            $table->index(['actor_guard', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_error_events');
        Schema::dropIfExists('production_errors');
    }
};
