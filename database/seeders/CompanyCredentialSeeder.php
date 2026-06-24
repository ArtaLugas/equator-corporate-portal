<?php

namespace Database\Seeders;

use App\Models\CompanyCredential;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Real Equator (PT EGI) credentials. Idempotent: keyed on slug, child items are
 * re-synced on each run. Following the gold standard, only the default-locale
 * (*_en) source is written; the Indonesian locale falls back to it — correct
 * here because KBLI / LPJP are Indonesian regulatory terms with no English form.
 */
class CompanyCredentialSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->credentials() as $order => $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['slug'] = Str::slug($data['title_en']);
            $data['display_order'] = $order + 1;
            $data['featured'] = true;
            $data['status'] = 'active';

            $credential = CompanyCredential::updateOrCreate(['slug' => $data['slug']], $data);

            // Re-sync items idempotently.
            $credential->items()->delete();

            foreach ($items as $i => $title) {
                $credential->items()->create([
                    'title_en' => $title,
                    'display_order' => $i,
                ]);
            }
        }
    }

    private function credentials(): array
    {
        return [
            [
                'category' => 'iso',
                'title_en' => 'ISO 9001:2015 — Quality Management System',
                'issuer_en' => null,
                'description_en' => '<p>Certified Quality Management System (ISO 9001:2015), evidencing consistent, standards-based delivery across our services.</p>',
            ],
            [
                'category' => 'iso',
                'title_en' => 'ISO 14001:2015 — Environmental Management System',
                'issuer_en' => null,
                'description_en' => '<p>Certified Environmental Management System (ISO 14001:2015), reflecting our commitment to managing environmental responsibilities systematically.</p>',
            ],
            [
                'category' => 'lpjp',
                'title_en' => 'Sertifikat Badan Usaha Jasa Konsultansi Non-Konstruksi',
                'issuer_en' => 'LPJP',
                'description_en' => '<p>LPJP-registered business entity certificate for non-construction consultancy services, covering the service classifications below.</p>',
                'items' => [
                    'Layanan Pendidikan',
                    'Layanan Kesehatan',
                    'Layanan Pengembangan Pertanian dan Pedesaan',
                    'Layanan Transportasi',
                    'Layanan Jasa Survey',
                    'Layanan Jasa Studi, Penelitian dan Bantuan Teknik',
                    'Layanan Jasa Konsultansi Manajemen',
                    'Layanan Telematika',
                    'Layanan Kepariwisataan',
                    'Layanan Perindustrian dan Perdagangan',
                    'Layanan Pertambangan dan Energi',
                    'Layanan Keuangan',
                    'Layanan Kependudukan',
                    'Layanan Jasa Khusus',
                ],
            ],
            [
                'category' => 'kbli',
                'title_en' => 'KBLI PT EGI',
                'issuer_en' => 'OSS (Online Single Submission)',
                'description_en' => '<p>Registered business classifications (KBLI) of PT EGI.</p>',
                'items' => [
                    'KBLI 70209 Aktivitas Konsultasi Manajemen Lainnya',
                    'KBLI 71102 Aktivitas Keinsinyuran dan Konsultasi Teknis YBDI',
                    'KBLI 74909 Aktivitas Profesional, Ilmiah dan Teknis Lainnya YTDL',
                    'KBLI 71101 Aktivitas Arsitektur',
                    'KBLI 71202 Jasa Pengujian Laboratorium',
                    'KBLI 73202 Jajak Pendapat Masyarakat',
                    'KBLI 72109 Penelitian dan Pengembangan Ilmu Pengetahuan Alam dan Teknologi Rekayasa Lainnya',
                    'KBLI 46421 Perdagangan Besar Alat Tulis dan Gambar',
                    'KBLI 60290 Aktivitas Teknologi Informasi dan Jasa Komputer Lainnya',
                    'KBLI 46422 Perdagangan Besar Barang Percetakan dan Penerbitan Dalam Berbagai Bentuk',
                    'KBLI 73201 Penelitian Pasar',
                ],
            ],
        ];
    }
}
