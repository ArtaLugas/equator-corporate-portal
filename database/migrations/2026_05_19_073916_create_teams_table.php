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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            $table->string('name', 191)->nullable();

            $table->string('position', 191)->nullable();

            $table->string('photo', 500)->nullable();

            $table->text('bio')->nullable();

            $table->string('linkedin_url', 500)->nullable();

            $table->smallInteger('display_order')->default(0);

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
        Schema::dropIfExists('teams');
    }
};
