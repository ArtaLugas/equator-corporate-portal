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
        Schema::create('hero_banners', function (Blueprint $table) {
            $table->id();

            $table->string('title', 191)->nullable();

            $table->string('subtitle', 255)->nullable();

            $table->string('image', 500)->nullable();

            $table->string('button_text', 100)->nullable();

            $table->string('button_link', 500)->nullable();

            $table->smallInteger('display_order')->default(1);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();

            $table->index([
                'status',
                'display_order',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_banners');
    }
};
