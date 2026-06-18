<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {

            $table->id();
            $table->string('platform', 50)->nullable();
            $table->string('url', 500)->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
