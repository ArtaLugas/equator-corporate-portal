<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {

            $table->id();

            $table->foreignId('category_id')
                ->constrained('service_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 191)->nullable();

            $table->string('slug', 191)->unique();

            $table->string('short_description', 255)->nullable();

            $table->longText('description')->nullable();

            $table->string('image', 500)->nullable();

            $table->string('meta_title', 191)->nullable();

            $table->string('meta_description', 320)->nullable();

            $table->string('meta_keywords', 255)->nullable();

            $table->enum('status', [
                'draft',
                'published',
            ])->default('draft');

            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->softDeletes();

            $table->index('slug');

            $table->index([
                'status',
                'is_featured',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
