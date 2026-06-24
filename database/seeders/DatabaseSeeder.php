<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database from the legacy Equator data
     * (exported to database/seeders/data/*.json).
     */
    public function run(): void
    {
        $this->call([
            // Accounts & settings
            AdminSeeder::class,
            SettingSeeder::class,

            // Services
            ServiceCategorySeeder::class,
            ServiceSeeder::class,

            // News
            NewsCategorySeeder::class,
            NewsSeeder::class,

            // Company content
            PartnerSeeder::class,
            TeamSeeder::class,
            HeroBannerSeeder::class,
            CoreValueSeeder::class,
            AboutHistorySeeder::class,
            ProjectSeeder::class,
            KeyMetricSeeder::class,
            OfficeLocationSeeder::class,
            FaqSeeder::class,
            CompanyCredentialSeeder::class,

            // Inbox
            MessageSeeder::class,
        ]);
    }
}
