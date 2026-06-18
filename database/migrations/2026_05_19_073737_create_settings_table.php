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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 191)->nullable();
            $table->string('tagline', 255)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('favicon', 500)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone', 191)->nullable();
            $table->text('address')->nullable();
            $table->text('google_maps_embed')->nullable();
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
