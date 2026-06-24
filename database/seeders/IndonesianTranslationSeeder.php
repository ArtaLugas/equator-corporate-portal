<?php

namespace Database\Seeders;

use App\Models\AboutContent;
use App\Models\AboutSection;
use App\Models\CoreValue;
use App\Models\Faq;
use Database\Seeders\Concerns\AppliesTranslations;
use Illuminate\Database\Seeder;

/**
 * Fills the Indonesian (*_id) translation columns for existing public content,
 * matching records by a stable identifier (slug / key / source title / question).
 *
 * Idempotent and re-runnable on any environment that shares the same source
 * content (dev, staging, production):
 *
 *     php artisan db:seed --class=IndonesianTranslationSeeder
 *
 * Updates go through Eloquent model ->update(), so the HasTranslations trait
 * sanitizes HTML fields (CoreValue.description, AboutContent.content) per locale.
 * It NEVER touches *_en or the fallback mechanism.
 *
 * Translations cover the full set of EN fields that exist on each record (so the
 * all-or-nothing invariant is satisfied and each record reaches 100%).
 *
 * Phase coverage: Option C (FAQ, Core Values, About) — Option B modules
 * (Services, Projects, News, Teams) are appended below as they are translated.
 */
class IndonesianTranslationSeeder extends Seeder
{
    use AppliesTranslations;

    public function run(): void
    {
        // Option C — small modules (translations inline below).
        $this->faqs();
        $this->coreValues();
        $this->aboutSections();
        $this->aboutContents();

        // Option B — large modules (one seeder file each). Idempotent.
        $this->call([
            ServiceIdTranslationSeeder::class,
            ProjectIdTranslationSeeder::class,
            NewsIdTranslationSeeder::class,
            TeamIdTranslationSeeder::class,
        ]);
    }

    private function faqs(): void
    {
        $this->apply(Faq::class, 'question_en', [
            'What services does Equator Group offer?' => [
                'question_id' => 'Layanan apa saja yang ditawarkan Equator Group?',
                'answer_id' => 'Kami menyediakan layanan konsultasi geospasial dan lingkungan yang terintegrasi, meliputi survei topografi dan hidrografi, pemetaan LiDAR dan udara, manajemen data spasial dan GIS, serta Analisis Dampak Lingkungan dan Sosial (ESIA). Tim multidisiplin kami mendukung proyek mulai dari studi kelayakan awal hingga kepatuhan dan pemantauan.',
            ],
            'Which industries and sectors do you typically work with?' => [
                'question_id' => 'Industri dan sektor apa saja yang biasanya Anda tangani?',
                'answer_id' => 'Klien kami mencakup infrastruktur, pertambangan dan energi, pembangunan kelautan dan pesisir, perkebunan dan agribisnis, instansi pemerintah, serta kontraktor rekayasa berskala besar. Setiap penugasan disusun sesuai dengan persyaratan regulasi dan teknis pada sektor terkait.',
            ],
            'How do I request a proposal or quotation?' => [
                'question_id' => 'Bagaimana cara meminta proposal atau penawaran harga?',
                'answer_id' => 'Cara tercepat adalah mengajukan permintaan melalui halaman Kontak kami disertai deskripsi singkat mengenai lingkup, lokasi, dan jadwal proyek Anda. Konsultan kami umumnya merespons dalam satu hari kerja untuk mengatur diskusi lingkup pekerjaan dan menyiapkan proposal yang disesuaikan.',
            ],
            'How long does a typical survey or assessment take?' => [
                'question_id' => 'Berapa lama waktu yang dibutuhkan untuk survei atau kajian pada umumnya?',
                'answer_id' => 'Jangka waktu bergantung pada luas area, kondisi lapangan, dan keluaran yang dibutuhkan. Survei topografi yang terfokus dapat memakan waktu satu hingga dua minggu, sementara ESIA lengkap bisa berlangsung beberapa bulan karena adanya pengumpulan data dasar, konsultasi pemangku kepentingan, dan peninjauan regulasi. Kami menyertakan jadwal indikatif dalam setiap proposal.',
            ],
            'Do you handle permits and regulatory compliance?' => [
                'question_id' => 'Apakah Anda menangani perizinan dan kepatuhan regulasi?',
                'answer_id' => 'Ya. Tim lingkungan kami menyiapkan dokumentasi yang selaras dengan regulasi nasional dan standar pengamanan internasional (seperti persyaratan AIIB dan Bank Dunia), serta mendampingi klien sepanjang proses perizinan dan persetujuan, termasuk koordinasi dengan instansi yang berwenang.',
            ],
            'What areas or regions do you operate in?' => [
                'question_id' => 'Di wilayah atau daerah mana saja Anda beroperasi?',
                'answer_id' => 'Kami beroperasi di seluruh Indonesia dan kawasan sekitarnya melalui jaringan kantor regional kami. Untuk proyek di lokasi terpencil atau lepas pantai, kami memobilisasi tim dan peralatan lapangan khusus sesuai kebutuhan. Lokasi kantor kami dapat ditemukan pada halaman Kontak.',
            ],
            'What technology and equipment do you use?' => [
                'question_id' => 'Teknologi dan peralatan apa yang Anda gunakan?',
                'answer_id' => 'Kami menggunakan penerima GNSS kelas survei, total station, echosounder multibeam dan singlebeam, LiDAR teristrial dan udara, serta platform UAV/drone untuk citra udara. Data diproses dan disampaikan menggunakan perangkat lunak GIS dan CAD berstandar industri untuk memastikan akurasi dan interoperabilitas.',
            ],
            'In what formats are the final deliverables provided?' => [
                'question_id' => 'Dalam format apa keluaran akhir disampaikan?',
                'answer_id' => 'Keluaran disesuaikan dengan kebutuhan Anda dan umumnya mencakup gambar CAD (DWG/DXF), layer GIS (SHP/GeoPackage), point cloud, ortofoto, model kontur dan terrain digital, serta laporan teknis lengkap dalam format PDF. Kami dapat menyelaraskan keluaran dengan sistem koordinat dan standar data yang Anda inginkan.',
            ],
            'Can you support ongoing monitoring after a project is completed?' => [
                'question_id' => 'Apakah Anda dapat mendukung pemantauan berkelanjutan setelah proyek selesai?',
                'answer_id' => 'Tentu. Kami menawarkan pemantauan lingkungan secara berkala, pemantauan deformasi dan penurunan tanah, serta pembaruan data dalam skema pemeliharaan atau retainer, sehingga membantu Anda tetap patuh dan memantau perubahan sepanjang umur aset Anda.',
            ],
            'How can I get in touch with your team?' => [
                'question_id' => 'Bagaimana cara menghubungi tim Anda?',
                'answer_id' => 'Anda dapat menghubungi kami melalui formulir kontak di situs web kami, melalui telepon, atau email — detailnya tercantum di bagian Lokasi Kantor pada halaman Kontak. Untuk proyek baru, formulir kontak adalah jalur tercepat menuju spesialis yang tepat.',
            ],
        ]);
    }

    private function coreValues(): void
    {
        $this->apply(CoreValue::class, 'title_en', [
            'Growth' => [
                'title_id' => 'Pertumbuhan',
                'description_id' => '<p>Kami terus mengembangkan tim, solusi, dan dampak kami untuk tetap unggul di dunia yang terus berubah.</p>',
            ],
            'Respect' => [
                'title_id' => 'Rasa Hormat',
                'description_id' => '<p>Kami menghargai setiap individu, komunitas, dan lingkungan tempat kami bekerja untuk membangun kepercayaan dan inklusivitas.</p>',
            ],
            'Agility' => [
                'title_id' => 'Kelincahan',
                'description_id' => '<p>Kami beradaptasi dengan cepat dan efektif untuk memenuhi kebutuhan yang terus berkembang dan menangkap peluang baru.</p>',
            ],
            'Collaboration' => [
                'title_id' => 'Kolaborasi',
                'description_id' => '<p>Kami menciptakan solusi yang lebih baik bersama-sama dengan bekerja erat bersama klien, mitra, dan komunitas.</p>',
            ],
            'Integrity' => [
                'title_id' => 'Integritas',
                'description_id' => '<p>Kami menjunjung tinggi etika, transparansi, dan akuntabilitas dalam setiap hal yang kami lakukan.</p>',
            ],
            'Sustainability' => [
                'title_id' => 'Keberlanjutan',
                'description_id' => '<p>Kami mengupayakan dampak yang langgeng dan bermanfaat bagi masyarakat, planet, dan generasi mendatang.</p>',
            ],
        ]);
    }

    private function aboutSections(): void
    {
        $this->apply(AboutSection::class, 'slug', [
            'who-we-are' => ['name_id' => 'Siapa Kami'],
            'vision-mission' => ['name_id' => 'Visi & Misi'],
        ]);
    }

    private function aboutContents(): void
    {
        $this->apply(AboutContent::class, 'key', [
            'safeguarding_sustainable_future' => [
                'title_id' => 'Menjaga Masa Depan Berkelanjutan',
                'content_id' => '<p style="line-height:1.75;text-align:justify;">Equator adalah firma konsultan sosial dan lingkungan yang berkomitmen menghadirkan solusi berdampak tinggi, inklusif, dan berkelanjutan bagi proyek-proyek pembangunan di seluruh Indonesia dan sekitarnya. Dengan keahlian yang berlandaskan standar pengamanan internasional sekaligus regulasi lokal, kami membantu klien menavigasi tantangan sosial dan lingkungan yang kompleks seraya mendorong pembangunan yang bertanggung jawab.</p><p style="text-align:justify;">Kami berspesialisasi dalam perencanaan pengadaan tanah dan permukiman kembali, pelibatan pemangku kepentingan, analisis dampak lingkungan dan sosial, serta program berbasis komunitas. Tim kami memadukan pengetahuan lokal dengan praktik terbaik global untuk memastikan pembangunan menghormati hak asasi manusia, nilai-nilai masyarakat adat, dan integritas lingkungan.</p><p style="text-align:justify;">Didorong oleh integritas, inovasi, dan dampak, Equator bermitra dengan pemerintah, klien sektor swasta, lembaga pembangunan internasional, dan komunitas akar rumput untuk membentuk proyek yang tidak hanya layak, tetapi juga adil dan berkelanjutan.</p>',
            ],
            'vision' => [
                'title_id' => 'Visi',
                'content_id' => '<p>Memimpin dalam menjaga manusia, planet, kemakmuran, dan prinsip demi mewujudkan masa depan yang berkelanjutan dan tangguh.</p>',
            ],
            'mission' => [
                'title_id' => 'Misi',
                'content_id' => '<ul><li>Memberdayakan negara dan dunia usaha untuk berkembang secara berkelanjutan, serta memastikan ketangguhan jangka panjang dalam menghadapi tantangan global.</li><li>Menyediakan solusi konsultasi inovatif dalam keberlanjutan sosial dan lingkungan melalui layanan konsultansi khusus, riset, dan pengembangan kapasitas.</li></ul>',
            ],
        ]);
    }
}
