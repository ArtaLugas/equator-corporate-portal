<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original create_jobs_table migration created only `jobs`, but
 * config/queue.php configures the failed-job store as database-uuids on a
 * `failed_jobs` table. Without this table, a job that exhausts its retries
 * (tries=3 on the mail jobs) cannot be recorded — the failure is lost and
 * cannot be inspected (`queue:failed`) or retried (`queue:retry`). This adds the
 * standard Laravel failed_jobs table so failed emails/notifications are captured.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('failed_jobs')) {
            return;
        }

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
