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
        Schema::create('key_metrics', function (Blueprint $table) {
            $table->id();

            $table->string('icon', 100)->nullable();
            $table->string('value', 50);
            $table->string('label', 191);
            $table->smallInteger('display_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index(['status', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_metrics');
    }
};
