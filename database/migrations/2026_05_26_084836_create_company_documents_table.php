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
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->string('file', 500);
            $table->string('thumbnail', 500)->nullable();
            $table->string('document_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('download_count')->default(0);
            $table->smallInteger('display_order')->default(0);
            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('company_documents');
    }
};
