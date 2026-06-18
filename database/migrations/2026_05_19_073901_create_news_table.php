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
        Schema::create('news', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('news_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('title', 191)->nullable();

            $table->string('slug', 191)->unique();

            $table->text('excerpt')->nullable();

            $table->longText('content')->nullable();

            $table->string('image', 500)->nullable();

            $table->string('meta_title', 191)->nullable();

            $table->string('meta_description', 320)->nullable();

            $table->string('meta_keywords', 255)->nullable();

            $table->enum('status', [
                'draft',
                'published',
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->integer('views_count')->default(0);

            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->softDeletes();

            $table->index('slug');

            $table->index([
                'status',
                'published_at',
            ]);

            $table->index([
                'status',
                'is_featured',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
