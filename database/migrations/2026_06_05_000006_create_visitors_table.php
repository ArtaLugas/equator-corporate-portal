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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();

            $table->string('ip_address', 45)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 100)->nullable();

            $table->timestamp('visited_at')->nullable();

            $table->index('visited_at');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
