<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name_en' => 'Head Office — Jakarta',
                'address_en' => "Equator Tower, Jl. Jenderal Sudirman Kav. 52-53\nJakarta Selatan 12190, Indonesia",
                'phone' => '+62 21 5150 1234',
                'email' => 'jakarta@equatorgroup.co.id',
                'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.0!2d106.806!3d-6.224!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sJakarta!5e0!3m2!1sen!2sid!4v0" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                'is_primary' => true,
                'display_order' => 1,
                'status' => 'active',
            ],
            [
                'name_en' => 'Branch Office — Surabaya',
                'address_en' => "Pakuwon Center, Jl. Embong Malang No. 1-5\nSurabaya 60261, Indonesia",
                'phone' => '+62 31 6000 5678',
                'email' => 'surabaya@equatorgroup.co.id',
                'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.0!2d112.738!3d-7.265!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sSurabaya!5e0!3m2!1sen!2sid!4v0" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                'is_primary' => false,
                'display_order' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($locations as $data) {
            OfficeLocation::updateOrCreate(['name_en' => $data['name_en']], $data);
        }
    }
}
