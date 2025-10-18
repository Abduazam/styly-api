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
        Schema::create('outfits', function (Blueprint $table) {
            $table->id();
            $table->ulid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('tag');
            $table->string('status')->default('draft');
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->json('prompt')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'tag']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfits');
    }
};
