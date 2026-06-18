<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Peta konversi Bootstrap Icons -> Lucide (lucide.dev).
     * Kunci = nilai lama (bi), nilai = nama ikon Lucide.
     */
    private array $map = [
        // Key Metrics
        'bi bi-briefcase' => 'briefcase',
        'bi bi-award' => 'award',
        'bi bi-people' => 'users',
        'bi bi-globe-americas' => 'globe',
        // Core Values
        'bi bi-arrow-repeat' => 'refresh-cw',
        'bi bi-heart-fill' => 'heart',
        'bi bi-lightning-charge-fill' => 'zap',
        'bi bi-people-fill' => 'users',
        'bi bi-shield-check' => 'shield-check',
        'bi bi-infinity' => 'infinity',
        // Social Links
        'bi bi-instagram' => 'instagram',
        'bi bi-linkedin' => 'linkedin',
        'bi bi-youtube' => 'youtube',
        'bi bi-facebook' => 'facebook',
        'bi bi-twitter' => 'twitter',
        'bi bi-twitter-x' => 'twitter',
        'bi bi-link-45deg' => 'link',
    ];

    public function up(): void
    {
        $this->apply('key_metrics', 'icon', $this->map);
        $this->apply('core_values', 'icon', $this->map);
        $this->apply('social_links', 'icon_class', $this->map);
    }

    public function down(): void
    {
        $reverse = array_flip($this->map);

        $this->apply('key_metrics', 'icon', $reverse);
        $this->apply('core_values', 'icon', $reverse);
        $this->apply('social_links', 'icon_class', $reverse);
    }

    /**
     * Terapkan peta konversi pada satu kolom tabel.
     */
    private function apply(string $table, string $column, array $map): void
    {
        foreach ($map as $from => $to) {
            DB::table($table)
                ->where($column, $from)
                ->update([$column => $to]);
        }
    }
};
