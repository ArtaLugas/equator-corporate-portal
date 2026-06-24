<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Office Locations: add nullable name_en/_id
 * (varchar 191) and address_en/_id (text), mirroring the legacy columns.
 * Legacy `name` is NOT NULL, so relax it to nullable during the migration
 * window; legacy `address` is already nullable. Explicit, frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->string('name', 191)->nullable()->change();

            $table->string('name_en', 191)->nullable()->after('name');
            $table->string('name_id', 191)->nullable()->after('name_en');

            $table->text('address_en')->nullable()->after('address');
            $table->text('address_id')->nullable()->after('address_en');
        });
    }

    public function down(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_id', 'address_en', 'address_id']);

            $table->string('name', 191)->nullable(false)->change();
        });
    }
};
