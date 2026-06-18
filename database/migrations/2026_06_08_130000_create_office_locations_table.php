<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->text('address')->nullable();
            $table->string('phone', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->text('map_embed')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->smallInteger('display_order')->default(1);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['status', 'display_order']);
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_locations');
    }
};
