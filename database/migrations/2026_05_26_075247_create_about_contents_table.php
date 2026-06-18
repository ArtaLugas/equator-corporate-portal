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
        Schema::create('about_contents', function (Blueprint $table) {

            $table->id();

            $table->foreignId('section_id')
                ->constrained('about_sections')
                ->cascadeOnDelete();

            $table->string('title', 191)->nullable();

            $table->longText('content')->nullable();

            $table->string('image', 500)->nullable();

            $table->smallInteger('display_order')->default(1);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();

            $table->index([
                'section_id',
            ]);

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
        Schema::dropIfExists('about_contents');
    }
};
