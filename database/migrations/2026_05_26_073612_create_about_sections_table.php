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
        Schema::create('about_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191);
            $table->smallInteger('display_order')->default(1);
            $table->enum('status', [
                'active',
                'inactive',
            ]);
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
        Schema::dropIfExists('about_sections');
    }
};
