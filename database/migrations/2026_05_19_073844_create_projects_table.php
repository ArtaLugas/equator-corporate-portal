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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('name', 191)->nullable();

            $table->string('slug', 191)->unique();

            $table->string('short_description', 255)->nullable();

            $table->longText('description')->nullable();

            $table->string('client_name', 191)->nullable();

            $table->string('location', 191)->nullable();

            $table->string('country', 100)->nullable();

            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

            $table->enum('status', [
                'planned',
                'ongoing',
                'completed',
            ])->default('planned');

            $table->string('featured_image', 500)->nullable();

            $table->string('meta_title', 191)->nullable();

            $table->string('meta_description', 320)->nullable();

            $table->string('meta_keywords', 255)->nullable();

            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->softDeletes();

            $table->index('slug');

            $table->index('country');

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
        Schema::dropIfExists('projects');
    }
};
