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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->string('avatar', 500)->nullable();
            $table->enum('role', [
                'super_admin',
                'admin',
            ])->default('admin');
            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->index('role');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
