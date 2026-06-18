<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Brevo SMTP / outgoing mail configuration is stored here so it can be
     * managed from the CMS Settings module instead of being hardcoded.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            $columns = [
                'mail_host' => fn () => $table->string('mail_host', 191)->nullable(),
                'mail_port' => fn () => $table->string('mail_port', 10)->nullable(),
                'mail_username' => fn () => $table->string('mail_username', 191)->nullable(),
                'mail_password' => fn () => $table->text('mail_password')->nullable(),
                'mail_encryption' => fn () => $table->string('mail_encryption', 10)->nullable(),
                'mail_from_address' => fn () => $table->string('mail_from_address', 191)->nullable(),
                'mail_from_name' => fn () => $table->string('mail_from_name', 191)->nullable(),
                'office_email' => fn () => $table->string('office_email', 191)->nullable(),
            ];

            foreach ($columns as $name => $definition) {
                if (! Schema::hasColumn('settings', $name)) {
                    $definition();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            $columns = [
                'mail_host', 'mail_port', 'mail_username', 'mail_password',
                'mail_encryption', 'mail_from_address', 'mail_from_name', 'office_email',
            ];

            foreach ($columns as $name) {
                if (Schema::hasColumn('settings', $name)) {
                    $table->dropColumn($name);
                }
            }
        });
    }
};
