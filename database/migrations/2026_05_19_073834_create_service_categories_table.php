<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {

            $table->id();

            $table->string('name', 191)->nullable();

            $table->string('slug', 191)->unique();

            $table->text('description')->nullable();

            $table->string('image', 500)->nullable();

            $table->string('meta_title', 191)->nullable();

            $table->string('meta_description', 320)->nullable();

            $table->string('meta_keywords', 255)->nullable();

            $table->smallInteger('display_order')->default(1);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();

            $table->softDeletes();

            $table->index('slug');

            $table->index([
                'status',
                'display_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
