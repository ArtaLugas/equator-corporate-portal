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
        // Keys match FaqSeeder's question_en (compiled from the Company Profile).
        $this->apply(Faq::class, 'question_en', [
            'What is Equator and what does it do?' => [
                'question_id' => 'Apa itu Equator dan apa yang dikerjakannya?',
                'answer_id' => 'Equator adalah firma konsultan sosial dan lingkungan yang telah beroperasi sejak 1999, menghadirkan solusi berdampak tinggi, inklusif, dan berkelanjutan bagi proyek-proyek pembangunan di seluruh Indonesia dan sekitarnya. Kami berspesialisasi dalam perencanaan pengadaan tanah dan permukiman kembali, pelibatan pemangku kepentingan, analisis dampak lingkungan dan sosial, serta program berbasis komunitas — memadukan pengetahuan lokal dengan praktik terbaik global agar pembangunan menghormati hak asasi manusia, nilai-nilai masyarakat adat, dan integritas lingkungan.',
            ],
            'What services does Equator provide?' => [
                'question_id' => 'Layanan apa saja yang disediakan Equator?',
                'answer_id' => 'Kami menyediakan layanan konsultasi menyeluruh (end-to-end) sepanjang siklus proyek, yang tersusun dalam tiga tahap: Penilaian dan Perencanaan (seperti LARAP, ESIA, perizinan lingkungan, dan studi kelayakan), Implementasi dan Pendampingan (seperti pendampingan pengadaan tanah, dukungan perizinan, resolusi konflik, dan pengembangan kapasitas), serta Pemantauan dan Evaluasi (seperti pemantauan pengamanan internal dan eksternal, audit kepatuhan, dan evaluasi dampak). Pekerjaan kami mencakup konsultansi, riset, dan pengembangan kapasitas di bidang sosial maupun lingkungan.',
            ],
            'Do you prepare LARAP and land acquisition documents?' => [
                'question_id' => 'Apakah Anda menyusun LARAP dan dokumen pengadaan tanah?',
                'answer_id' => 'Ya. Land Acquisition and Resettlement Action Plan (LARAP) merupakan salah satu spesialisasi inti kami. Kami menyusun dan membantu mengimplementasikan perencanaan pengadaan tanah dan permukiman kembali — termasuk Program Pemulihan Penghidupan (LRP), Rencana Pelibatan Pemangku Kepentingan (SEP), Mekanisme Penanganan Keluhan (GRM), serta dokumen nasional seperti DPPT — untuk proyek infrastruktur dan pembangunan, baik berdasarkan regulasi nasional maupun standar internasional.',
            ],
            'Do you handle ESIA, AMDAL, and environmental permitting?' => [
                'question_id' => 'Apakah Anda menangani ESIA, AMDAL, dan perizinan lingkungan?',
                'answer_id' => 'Ya. Kami menyusun Analisis Dampak Lingkungan dan Sosial (ESIA), Analisis Dampak Sosial (SIA), serta dokumen perizinan lingkungan Indonesia seperti AMDAL/EIA, UKL-UPL, dan SPPL, bersama Rencana Pengelolaan Lingkungan dan Sosial (ESMP) serta kajian terkait. Kami mendampingi klien sepanjang proses perizinan dan persetujuan.',
            ],
            'Which standards and safeguard frameworks do you work with?' => [
                'question_id' => 'Standar dan kerangka pengamanan (safeguard) apa yang Anda gunakan?',
                'answer_id' => 'Kami menyelaraskan pekerjaan kami dengan regulasi nasional Indonesia sekaligus standar pengamanan internasional, termasuk Equator Principles, IFC Performance Standards, serta persyaratan lembaga pemberi pinjaman multilateral seperti Bank Dunia, ADB, AIIB, JICA, dan KfW. Hal ini memastikan proyek memenuhi kewajiban kepatuhan lokal sekaligus praktik baik internasional.',
            ],
            'Which sectors and clients do you typically work with?' => [
                'question_id' => 'Sektor dan klien apa saja yang biasanya Anda tangani?',
                'answer_id' => 'Kami bermitra dengan pemerintah, klien sektor swasta, lembaga pembangunan internasional, dan komunitas akar rumput. Pengalaman proyek kami mencakup energi (pembangkit listrik tenaga air dan panas bumi), pertambangan, sumber daya air dan bendungan, transportasi dan jalan tol, fasilitas kesehatan, serta kawasan industri — bekerja dengan klien seperti PT PLN, kementerian pemerintah, dan program-program berpendanaan internasional.',
            ],
            'Where does Equator operate, and where are your offices?' => [
                'question_id' => 'Di mana Equator beroperasi, dan di mana kantor Anda?',
                'answer_id' => 'Kami beroperasi di seluruh Indonesia dan kawasan sekitarnya. Kantor kami berada di Bogor (Jl. Letjen. Ibrahim Adjie 197, Bogor 16117) dan Jakarta Selatan (Centennial Tower, Jl. Jend. Gatot Subroto Kav. 24-25, Jakarta Selatan 12930). Melalui LPM Equator — jaringan cabang lokal yang dibentuk sejak 1999 — kami memiliki kehadiran dan kapasitas lapangan di seluruh provinsi di Indonesia.',
            ],
            'What certifications and credentials does Equator hold?' => [
                'question_id' => 'Sertifikasi dan kredensial apa yang dimiliki Equator?',
                'answer_id' => 'Equator adalah penyedia jasa profesional terdaftar (LPJP Registered) dan tersertifikasi ISO 9001:2015 (Manajemen Mutu) serta ISO 14001:2015 (Manajemen Lingkungan). Kami juga memiliki Sertifikat Badan Usaha Jasa Konsultansi Non-Konstruksi pada beragam klasifikasi layanan (KBLI), yang mencerminkan komitmen kami terhadap mutu, keselamatan, dan lingkungan.',
            ],
            'Do you provide training and capacity building?' => [
                'question_id' => 'Apakah Anda menyediakan pelatihan dan pengembangan kapasitas?',
                'answer_id' => 'Ya. Melalui Network of Learning Center for Environmental and Social Sustainability (NLC), kami menyelenggarakan program pengembangan kapasitas dan pelatihan yang terstruktur — termasuk pelatihan Analisis Dampak Lingkungan dan Sosial, pelatihan Land Acquisition and Resettlement Action Plan, pelatihan Rencana Masyarakat Adat (Indigenous Peoples Plan), dan pelatihan Rencana Pengelolaan Keanekaragaman Hayati — untuk memperkuat implementasi pengamanan (safeguard).',
            ],
            'Can you support monitoring and evaluation after a project is implemented?' => [
                'question_id' => 'Apakah Anda dapat mendukung pemantauan dan evaluasi setelah proyek diimplementasikan?',
                'answer_id' => 'Ya. Kami menyediakan layanan pemantauan dan evaluasi yang andal, meliputi pemantauan pengamanan internal dan eksternal, audit kepatuhan lingkungan, evaluasi dampak program, dan pemantauan emisi karbon — membantu klien menjaga akuntabilitas dan mempertahankan dampak jangka panjang.',
            ],
            'Do you manage stakeholder engagement, grievances, and indigenous peoples plans?' => [
                'question_id' => 'Apakah Anda menangani pelibatan pemangku kepentingan, penanganan keluhan, dan rencana masyarakat adat?',
                'answer_id' => 'Ya. Pelibatan yang inklusif merupakan inti dari pendekatan kami. Kami menyusun Rencana Pelibatan Pemangku Kepentingan (SEP), Mekanisme Penanganan Keluhan (GRM), dan Rencana Masyarakat Adat (IPP), serta memfasilitasi resolusi konflik — memastikan masyarakat terdampak didengar dan pembangunan menghormati hak asasi manusia serta nilai-nilai masyarakat adat.',
            ],
            'How do I get in touch or request a proposal?' => [
                'question_id' => 'Bagaimana cara menghubungi atau meminta proposal?',
                'answer_id' => 'Cara tercepat adalah menghubungi kami melalui situs web (www.equatorgroup.id) atau formulir kontak, melalui email di office@equatorgroup.id, atau telepon di +62 819-1111-7109. Sampaikan deskripsi singkat mengenai lingkup dan lokasi proyek Anda, dan tim kami akan mengatur diskusi lingkup pekerjaan serta menyiapkan proposal yang disesuaikan.',
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
