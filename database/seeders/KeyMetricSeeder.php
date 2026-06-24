<?php

namespace Database\Seeders;

use App\Models\KeyMetric;
use Illuminate\Database\Seeder;

class KeyMetricSeeder extends Seeder
{
    public function run(): void
    {
        $metrics = [
            ['value' => '15+', 'label' => 'Years Experience', 'icon' => 'briefcase'],
            ['value' => '200+', 'label' => 'Projects Delivered', 'icon' => 'award'],
            ['value' => '50+', 'label' => 'Expert Consultants', 'icon' => 'users'],
            ['value' => '6', 'label' => 'Countries Served', 'icon' => 'globe'],
        ];

        foreach ($metrics as $i => $metric) {
            KeyMetric::updateOrCreate(
                ['label_en' => $metric['label']],
                [
                    'value' => $metric['value'],
                    'icon' => $metric['icon'],
                    'display_order' => $i + 1,
                    'status' => 'active',
                ]
            );
        }
    }
}
