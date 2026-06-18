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
        Schema::create('core_values', function (Blueprint $table) {
            $table->id();

            $table->string('title', 191)->nullable();

            $table->text('description')->nullable();

            $table->string('icon_class', 100)->nullable();

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
        Schema::dropIfExists('core_values');
    }
};
