<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standalone tables → globally unique display_order.
     * Soft-delete tables → unique(display_order, deleted_at) so trashed rows
     * don't block active ones. Child tables → unique per parent.
     */
    private array $standalone = [
        'about_histories', 'core_values', 'faqs',
        'hero_banners', 'key_metrics', 'social_links',
    ];

    private array $softDelete = ['company_documents', 'partners', 'teams'];

    private array $children = [
        'about_contents' => 'section_id',
        'project_images' => 'project_id',
    ];

    public function up(): void
    {
        // ---- Standalone (incl. soft-delete): renumber rows to be unique ----
        foreach (array_merge($this->standalone, $this->softDelete) as $table) {
            $this->renumber($table);
        }

        foreach ($this->standalone as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->unique('display_order'));
        }

        // Soft-delete tables: a plain unique would let trashed rows block
        // active ones, and (display_order, deleted_at) fails in MySQL because
        // NULL deleted_at values are treated as distinct. Use a generated
        // column that is NULL for trashed rows, so only ACTIVE rows are forced
        // to have a unique display_order.
        foreach ($this->softDelete as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->smallInteger('active_order')
                    ->nullable()
                    ->storedAs('CASE WHEN deleted_at IS NULL THEN display_order ELSE NULL END');
            });
            Schema::table($table, fn (Blueprint $t) => $t->unique('active_order'));
        }

        // ---- Child tables: renumber per parent, then composite unique ----
        foreach ($this->children as $table => $parent) {
            $this->renumberPerParent($table, $parent);
            Schema::table($table, fn (Blueprint $t) => $t->unique([$parent, 'display_order']));
        }
    }

    public function down(): void
    {
        foreach ($this->standalone as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropUnique($table.'_display_order_unique'));
        }
        foreach ($this->softDelete as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropUnique($table.'_active_order_unique'));
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('active_order'));
        }
        foreach ($this->children as $table => $parent) {
            Schema::table($table, fn (Blueprint $t) => $t->dropUnique($table.'_'.$parent.'_display_order_unique'));
        }
    }

    private function renumber(string $table): void
    {
        $rows = DB::table($table)->orderBy('display_order')->orderBy('id')->get(['id']);
        $order = 1;
        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update(['display_order' => $order++]);
        }
    }

    private function renumberPerParent(string $table, string $parent): void
    {
        $parentIds = DB::table($table)->distinct()->pluck($parent);
        foreach ($parentIds as $pid) {
            $rows = DB::table($table)->where($parent, $pid)->orderBy('display_order')->orderBy('id')->get(['id']);
            $order = 1;
            foreach ($rows as $row) {
                DB::table($table)->where('id', $row->id)->update(['display_order' => $order++]);
            }
        }
    }
};
