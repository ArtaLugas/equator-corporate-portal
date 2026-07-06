<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|==============================================================================
|  ██  STOP — PRODUCTION-READINESS MIGRATION. DO NOT RUN CASUALLY.  ██
|==============================================================================
|
|  This is the i18n **Phase C (CONTRACT)** migration: FINAL, DESTRUCTIVE, GATED.
|  It DROPS the legacy single-language columns (the multilingual rollback safety
|  net) and enforces NOT NULL on the required *_en columns.
|
|  ⛔ DO NOT move this file into `database/migrations/`. It lives in
|     `database/migrations/phase-c/` ON PURPOSE so Laravel's auto-discovery
|     (`migrate`, `migrate:fresh`, RefreshDatabase) NEVER runs it. Moving it
|     would make a routine deploy silently destroy the rollback path.
|     Relocating it requires explicit written approval.
|
|  ⛔ DO NOT run it during normal development or deployment. It executes ONLY at
|     go-live, explicitly, AFTER the pre-flight checklist below is satisfied:
|
|         php artisan migrate --path=database/migrations/phase-c --force
|
|  ──────────────────────────────────────────────────────────────────────────
|  PRE-FLIGHT CHECKLIST — every box MUST be ✅ before executing (full runbook:
|  docs/phase-c-execution.md):
|
|    [ ] DATABASE BACKUP taken immediately before, and its restore verified.
|    [ ] MAINTENANCE MODE enabled (`php artisan down`).
|    [ ] STAGING VALIDATION done on a copy of production data: EN renders
|        identically to pre-i18n, ID content filled, ID→EN fallback works on
|        every module; admin CMS create/edit/translation-status all OK.
|    [ ] UAT signed off by the business owner.
|    [ ] ROLLBACK VERIFIED on staging: `migrate:rollback --path=database/
|        migrations/phase-c` re-creates the legacy columns and restores data.
|    [ ] No remaining reads/writes of any legacy column anywhere (app code,
|        seeders, exports, reports, queued jobs).
|    [ ] DEPLOYMENT APPROVAL recorded (final go-ahead).
|
|  If ANY box is unchecked → do NOT run. Keep the legacy columns as the rollback
|  safety net until go-live is fully approved.
|==============================================================================
*/

/**
 * Mechanics (see docs/multilingual-guide.md §7 and docs/phase-c-execution.md):
 *
 *   up()   1. Enforces NOT NULL on the default-locale (*_en) column of each
 *             module's REQUIRED anchor field(s) only — optional translatable
 *             fields stay nullable.
 *          2. Drops every legacy single-language column.
 *
 *   down() True, lossless rollback: re-creates the legacy columns (nullable,
 *          matching the post-Phase-A/B state), copies *_en back into them, and
 *          relaxes the enforced *_en columns to nullable — restoring the
 *          pre-Phase-C schema.
 */
return new class extends Migration
{
    /**
     * Frozen snapshot of the translatable schema at the time Phase C was authored.
     * NOT read from config — an explicit historical record. Per table:
     *   'fields'   => legacy field => column type (mirrors the original/Phase-A types)
     *   'required' => fields whose *_en column becomes NOT NULL (the required anchors)
     */
    private array $schema = [
        'services' => [
            'fields' => [
                'name' => 'string:191', 'short_description' => 'string:255', 'description' => 'longText',
                'meta_title' => 'string:191', 'meta_description' => 'string:320', 'meta_keywords' => 'string:255',
            ],
            'required' => ['name'],
        ],
        'projects' => [
            'fields' => [
                'name' => 'string:191', 'short_description' => 'string:255', 'description' => 'longText',
                'meta_title' => 'string:191', 'meta_description' => 'string:320', 'meta_keywords' => 'string:255',
            ],
            'required' => ['name'],
        ],
        'news' => [
            'fields' => [
                'title' => 'string:191', 'content' => 'longText',
                'meta_title' => 'string:191', 'meta_description' => 'string:320', 'meta_keywords' => 'string:255',
            ],
            'required' => ['title'],
        ],
        'news_categories' => [
            // Only the display name is translatable; slug stays single-language.
            'fields' => ['name' => 'string:191'],
            'required' => ['name'],
        ],
        'about_sections' => [
            'fields' => ['name' => 'string:191'],
            'required' => ['name'],
        ],
        'about_contents' => [
            // title + content are both optional → no NOT NULL enforcement.
            'fields' => ['title' => 'string:191', 'content' => 'longText'],
            'required' => [],
        ],
        'faqs' => [
            'fields' => ['question' => 'text', 'answer' => 'text'],
            'required' => ['question', 'answer'],
        ],
        'core_values' => [
            'fields' => ['title' => 'string:191', 'description' => 'text'],
            'required' => ['title'],
        ],
        'teams' => [
            // name is single-language (never had *_en columns); not listed here.
            'fields' => ['position' => 'string:191', 'bio' => 'text'],
            'required' => ['position'],
        ],
        'service_categories' => [
            'fields' => [
                'name' => 'string:191', 'description' => 'text',
                'meta_title' => 'string:191', 'meta_description' => 'string:320', 'meta_keywords' => 'string:255',
            ],
            'required' => ['name'],
        ],
        'hero_banners' => [
            // subtitle & button_text are optional → no NOT NULL enforcement.
            'fields' => ['title' => 'string:191', 'subtitle' => 'string:255', 'button_text' => 'string:100'],
            'required' => ['title'],
        ],
        'key_metrics' => [
            // only label is translatable; value stays single-language.
            'fields' => ['label' => 'string:191'],
            'required' => ['label'],
        ],
        'about_histories' => [
            'fields' => ['title' => 'string:191', 'description' => 'text'],
            'required' => ['title'],
        ],
        'company_documents' => [
            'fields' => ['title' => 'string:191', 'description' => 'text'],
            'required' => ['title'],
        ],
        'office_locations' => [
            // address is optional → only name is enforced NOT NULL.
            'fields' => ['name' => 'string:191', 'address' => 'text'],
            'required' => ['name'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->schema as $table => $def) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // 1) Enforce NOT NULL on the required-anchor *_en columns (guard nulls first).
            foreach ($def['required'] as $field) {
                $this->enforceNotNull($table, "{$field}_en", $this->sqlType($def['fields'][$field]));
            }

            // 2) Drop the legacy single-language columns.
            $legacy = array_values(array_filter(
                array_keys($def['fields']),
                fn ($field) => Schema::hasColumn($table, $field)
            ));

            if ($legacy !== []) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($legacy));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->schema as $table => $def) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // 1) Re-create the legacy columns (nullable — the post-Phase-A/B state).
            Schema::table($table, function (Blueprint $t) use ($table, $def) {
                foreach ($def['fields'] as $field => $type) {
                    if (! Schema::hasColumn($table, $field)) {
                        $this->blueprintColumn($t, $field, $type)->nullable();
                    }
                }
            });

            // 2) Copy each *_en column back into its legacy column.
            $copy = [];
            foreach (array_keys($def['fields']) as $field) {
                $copy[$field] = DB::raw("`{$field}_en`");
            }
            if ($copy !== []) {
                DB::table($table)->update($copy);
            }

            // 3) Relax the enforced *_en columns back to nullable.
            foreach ($def['required'] as $field) {
                $this->relaxNullable($table, "{$field}_en", $this->sqlType($def['fields'][$field]));
            }
        }
    }

    /** Make a column NOT NULL (MySQL), emptying any NULLs first so the change cannot fail. */
    private function enforceNotNull(string $table, string $column, string $sqlType): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)->whereNull($column)->update([$column => '']);
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$sqlType} NOT NULL");
    }

    /** Make a column nullable again (MySQL) — used on rollback. */
    private function relaxNullable(string $table, string $column, string $sqlType): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$sqlType} NULL");
    }

    /** Map the snapshot type to a MySQL column type. */
    private function sqlType(string $type): string
    {
        return match (true) {
            $type === 'longText' => 'LONGTEXT',
            $type === 'text' => 'TEXT',
            str_starts_with($type, 'string:') => 'VARCHAR('.substr($type, 7).')',
            default => 'VARCHAR(255)',
        };
    }

    /** Map the snapshot type to a Blueprint column (for rollback re-creation). */
    private function blueprintColumn(Blueprint $table, string $field, string $type)
    {
        return match (true) {
            $type === 'longText' => $table->longText($field),
            $type === 'text' => $table->text($field),
            str_starts_with($type, 'string:') => $table->string($field, (int) substr($type, 7)),
            default => $table->string($field),
        };
    }
};
