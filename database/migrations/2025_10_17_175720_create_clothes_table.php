<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clothes', function (Blueprint $table) {
            $table->id();
            $table->ulid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('category')->nullable();
            $table->string('occasion')->nullable();
            $table->string('season')->nullable();
            $table->json('color_palette')->nullable();
            $table->string('source_path');
            $table->string('image_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->json('ai_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'occasion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clothes');
    }
};
