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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->string('name', 191)->nullable();

            $table->string('email', 191)->nullable();

            $table->string('subject', 191)->nullable();

            $table->text('message')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->text('user_agent')->nullable();

            $table->enum('status', [
                'unread',
                'read',
                'replied',
                'spam',
            ])->default('unread');

            $table->timestamp('created_at')->nullable();

            $table->index('status');

            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
