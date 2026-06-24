<?php

namespace Database\Seeders;

use App\Models\Team;
use Database\Seeders\Concerns\AppliesTranslations;
use Illuminate\Database\Seeder;

class TeamIdTranslationSeeder extends Seeder
{
    use AppliesTranslations;

    public function run(): void
    {
        $this->apply(Team::class, 'name', [
            'Rimun Wibowo' => [
                'position_id' => 'Direktur Utama',
            ],
            'Zakky M. Noor' => [
                'position_id' => 'Direktur Pelaksana',
            ],
            'M. Shiddiq I. N.' => [
                'position_id' => 'Direktur Keuangan',
            ],
            'Bambang S.' => [
                'position_id' => 'Kepala Keuangan dan Pajak',
            ],
            'Heru Widartono' => [
                'position_id' => 'Kepala Teknologi Informasi dan Media',
            ],
            'Mimin Kustini' => [
                'position_id' => 'Kepala Pengembangan Bisnis',
            ],
            'Rahayu Widayana' => [
                'position_id' => 'Kepala Manajemen Operasional',
            ],
            'Fellina Wahlia Z.' => [
                'position_id' => 'Kepala Sumber Daya Manusia',
            ],
            'Fatonah Herawati' => [
                'position_id' => 'Staf Keuangan',
            ],
            'Herman Riyanto' => [
                'position_id' => 'Staf Pajak',
            ],
            'Lugas R. Arta M.' => [
                'position_id' => 'Staf Teknologi Informasi',
            ],
            'Usmansyah' => [
                'position_id' => 'Staf Multimedia',
            ],
            'M. Fikri Ilyas' => [
                'position_id' => 'Staf Sosial',
            ],
            'Rizki M. S.' => [
                'position_id' => 'Staf Sosial',
            ],
            'Nada N. Syasita' => [
                'position_id' => 'Staf Lingkungan',
            ],
            'Irham Darmawan' => [
                'position_id' => 'Staf Lingkungan',
            ],
            'Pamuji Adnan Fuadi' => [
                'position_id' => 'Dukungan Manajemen Operasional',
            ],
        ]);
    }
}
