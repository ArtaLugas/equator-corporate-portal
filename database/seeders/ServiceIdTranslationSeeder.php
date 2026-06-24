<?php

namespace Database\Seeders;

use App\Models\Service;
use Database\Seeders\Concerns\AppliesTranslations;
use Illuminate\Database\Seeder;

class ServiceIdTranslationSeeder extends Seeder
{
    use AppliesTranslations;

    public function run(): void
    {
        $this->apply(Service::class, 'slug', [
            'land-acquisition-and-resettlement-plan-larap' => [
                'name_id' => 'Rencana Pengadaan Tanah dan Permukiman Kembali (LARAP)',
                'description_id' => '<p>Equator menyediakan layanan penyusunan dokumen LARAP yang komprehensif untuk menguraikan strategi pengadaan tanah dan permukiman kembali secara terpaksa.<br>Layanan ini membantu klien mematuhi peraturan nasional dan standar safeguard internasional sekaligus melindungi hak dan penghidupan masyarakat terdampak.</p>',
            ],
            'environmental-and-social-impact-assessment-esia' => [
                'name_id' => 'Kajian Dampak Lingkungan dan Sosial (ESIA)',
                'description_id' => '<p>Equator menawarkan kajian terpadu atas potensi dampak lingkungan dan sosial sebelum pelaksanaan proyek, sesuai dengan standar safeguard internasional.<br>Layanan ini mendukung pengambilan keputusan yang tepat dengan mengidentifikasi risiko, peluang, dan strategi mitigasi sejak awal proses perencanaan.</p>',
            ],
            'environmental-permitting-amdal-ukl-upl-sppl-etc' => [
                'name_id' => 'Perizinan Lingkungan (AMDAL, UKL-UPL, SPPL, dll.)',
                'description_id' => '<p>Equator mendukung penyusunan dokumen teknis perizinan lingkungan sesuai dengan peraturan Indonesia.<br>Layanan ini memastikan pelaksanaan proyek yang legal sekaligus memitigasi risiko lingkungan melalui pendekatan ilmiah dan partisipatif.</p>',
            ],
            'national-land-acquisition-document-dppt' => [
                'name_id' => 'Dokumen Perencanaan Pengadaan Tanah Nasional (DPPT)',
                'description_id' => '<p>Equator menyusun Dokumen Perencanaan Pengadaan Tanah (DPPT) sesuai dengan peraturan Indonesia sebagai dokumen perencanaan resmi untuk pengadaan tanah.<br>Layanan ini membantu pemrakarsa proyek mematuhi peraturan perundang-undangan pengadaan tanah nasional sekaligus meletakkan dasar bagi proses koordinasi dan persetujuan para pemangku kepentingan.</p>',
            ],
            'feasibility-study' => [
                'name_id' => 'Studi Kelayakan',
                'description_id' => '<p>Equator menyediakan studi kelayakan yang menilai kelayakan teknis, ekonomi, sosial, dan lingkungan dari proyek yang diusulkan.<br>Layanan ini membantu klien menentukan risiko, potensi manfaat, dan pertimbangan utama sebelum berkomitmen pada investasi berskala besar.</p>',
            ],
            'indigenous-peoples-plan-ipp' => [
                'name_id' => 'Rencana Masyarakat Adat (IPP)',
                'description_id' => '<p>Equator menyusun rencana perlindungan dan pemberdayaan bagi Masyarakat Adat berdasarkan prinsip FPIC dan hak budaya.<br>Layanan ini mengintegrasikan perspektif Masyarakat Adat ke dalam rancangan proyek dan memastikan mereka memperoleh manfaat pembangunan yang setara.</p>',
            ],
            'livelihood-restoration-program-lrp' => [
                'name_id' => 'Program Pemulihan Penghidupan (LRP)',
                'description_id' => '<p>Equator menyelenggarakan program pemulihan dan pengembangan penghidupan bagi masyarakat yang terdampak pengadaan tanah atau permukiman kembali.<br>Layanan ini memastikan keberlanjutan ekonomi dan sosial melalui pendekatan partisipatif dan pemanfaatan potensi lokal.</p>',
            ],
            'stakeholder-engagement-plan-sep' => [
                'name_id' => 'Rencana Pelibatan Pemangku Kepentingan (SEP)',
                'description_id' => '<p>Equator menyusun strategi terstruktur untuk pelibatan pemangku kepentingan yang inklusif dan bermakna sepanjang siklus proyek.<br>Layanan ini membantu membangun kepercayaan dan komunikasi yang efektif dengan masyarakat lokal, pemerintah, dan pemangku kepentingan utama lainnya.</p>',
            ],
            'grievance-redress-mechanism-grm' => [
                'name_id' => 'Mekanisme Penanganan Keluhan (GRM)',
                'description_id' => '<p>Equator merancang dan menerapkan mekanisme penanganan keluhan yang responsif, transparan, dan mudah diakses bagi masyarakat.<br>Layanan ini meningkatkan akuntabilitas proyek dan mendorong penyelesaian konflik secara dini melalui dialog yang konstruktif.</p>',
            ],
            'environmental-and-social-management-plan-esmp' => [
                'name_id' => 'Rencana Pengelolaan Lingkungan dan Sosial (ESMP)',
                'description_id' => '<p>Equator menyusun rencana pengelolaan yang praktis untuk memitigasi dampak sosial dan lingkungan selama tahap konstruksi dan operasi.<br>Layanan ini memungkinkan klien menerapkan langkah-langkah mitigasi secara konsisten dan selaras dengan komitmen proyek.</p>',
            ],
            'carbon-footprint-assessment' => [
                'name_id' => 'Kajian Jejak Karbon',
                'description_id' => '<p>Equator melakukan kajian terperinci atas emisi karbon di sepanjang siklus hidup proyek untuk mengukur dampak lingkungan.<br>Layanan ini mendukung klien dalam mengidentifikasi sumber emisi, menetapkan target penurunan, dan menyelaraskan dengan tujuan iklim atau kerangka pelaporan ESG.</p>',
            ],
            'biodiversity-management-plan' => [
                'name_id' => 'Rencana Pengelolaan Keanekaragaman Hayati',
                'description_id' => '<p>Equator menyusun rencana untuk mengkaji, melindungi, dan memulihkan keanekaragaman hayati di area proyek, terutama pada ekosistem yang sensitif.<br>Layanan ini memastikan risiko terhadap keanekaragaman hayati dimitigasi melalui strategi konservasi yang selaras dengan kerangka regulasi dan keberlanjutan global.</p>',
            ],
            'cultural-heritage-management-plan' => [
                'name_id' => 'Rencana Pengelolaan Warisan Budaya',
                'description_id' => '<p>Equator mengidentifikasi dan melindungi warisan budaya berwujud maupun tak berwujud yang berpotensi terdampak oleh kegiatan proyek.<br>Layanan ini mendukung pelestarian identitas lokal dan memastikan kepatuhan terhadap kebijakan perlindungan budaya, terutama di area yang memiliki nilai penting bagi Masyarakat Adat.</p>',
            ],
            'air-quality-and-emission-studies' => [
                'name_id' => 'Studi Kualitas Udara dan Emisi',
                'description_id' => '<p>Equator melakukan studi baseline dan prediktif mengenai kualitas udara, emisi partikulat, dan dispersi polusi di dalam dan di sekitar lokasi proyek.<br>Layanan ini memberikan wawasan berbasis data untuk mendukung kepatuhan terhadap baku mutu kualitas udara dan menentukan langkah-langkah mitigasi selama perencanaan.</p>',
            ],
            'gis-data-creation-and-analysis' => [
                'name_id' => 'Pembuatan dan Analisis Data GIS',
                'description_id' => '<p>Equator membangun dan menganalisis dataset geospasial untuk mendukung perencanaan dan analisis dampak berbasis bukti.<br>Layanan ini memungkinkan pengambilan keputusan spasial melalui pemetaan penggunaan lahan, risiko lingkungan, batas administrasi, dan indikator sosial.</p>',
            ],
            'remote-sensing-and-topographic-data-collection' => [
                'name_id' => 'Penginderaan Jauh dan Pengumpulan Data Topografi',
                'description_id' => '<p>Equator memanfaatkan teknologi drone, satelit, dan berbasis darat untuk mengumpulkan data medan dan bentang alam yang akurat bagi perencanaan proyek tahap awal.<br>Layanan ini meningkatkan pemahaman atas karakteristik spasial dan mengurangi kesalahan perencanaan melalui pemodelan topografi yang presisi.</p>',
            ],
            'landscape-architecture-planning' => [
                'name_id' => 'Perencanaan Arsitektur Lanskap',
                'description_id' => '<p>Equator menyediakan strategi desain lanskap yang menyelaraskan elemen lingkungan, budaya, dan visual di area pembangunan.<br>Layanan ini menciptakan ruang yang fungsional, estetis, dan berkelanjutan&mdash;terutama pada area ekowisata, ruang terbuka hijau publik, dan zona penyangga.</p>',
            ],
            'urban-design-planning' => [
                'name_id' => 'Perencanaan Desain Perkotaan',
                'description_id' => '<p>Equator mendukung pengembangan ruang perkotaan yang terpadu, inklusif, dan berkelanjutan melalui kerangka desain perkotaan yang strategis.<br>Layanan ini mencakup penataan zonasi penggunaan lahan, perencanaan mobilitas, dan infrastruktur yang responsif terhadap masyarakat guna mendorong pertumbuhan perkotaan yang berketahanan.</p>',
            ],
            'social-impact-assessment-related-to-land-acquisition' => [
                'name_id' => 'Kajian Dampak Sosial Terkait Pengadaan Tanah',
                'description_id' => '<p>Kami menyediakan analisis mendalam atas dampak sosial yang ditimbulkan oleh pengadaan tanah proyek. Fokus kami adalah mengidentifikasi risiko sosial, mengevaluasi perubahan penghidupan masyarakat, dan menyusun strategi mitigasi yang selaras dengan peraturan nasional dan standar internasional. Kami memastikan proyek Anda berjalan sembari menjaga stabilitas sosial dan memperoleh dukungan penuh dari masyarakat lokal</p>',
            ],
            'stakeholder-consultation-and-census-of-project-affected-persons-paps' => [
                'name_id' => 'Konsultasi Pemangku Kepentingan dan Sensus Warga Terdampak Proyek (PAPs)',
                'description_id' => '<p style="text-align: justify;">Layanan ini mengadopsi pendekatan inklusif melalui konsultasi publik yang transparan dan sensus menyeluruh atas warga terdampak proyek (PAPs). Kami melakukan pengumpulan data yang akurat untuk memastikan setiap individu memperoleh informasi yang jelas, hak-hak mereka terlindungi, serta memfasilitasi proses kompensasi yang adil dan rehabilitasi sosial guna meminimalkan konflik di lapangan.</p>',
            ],
            'environmental-and-social-modeling' => [
                'name_id' => 'Pemodelan Lingkungan dan Sosial',
                'description_id' => '<p style="text-align: justify;">Layanan Pemodelan Lingkungan dan Sosial kami memanfaatkan perangkat prediktif canggih untuk memproyeksikan dampak jangka panjang dari proyek industri dan infrastruktur. Kami menyediakan simulasi berbasis data untuk dispersi lingkungan, pengelolaan sumber daya air, dan perubahan sosial-ekonomi. Dengan mengubah data yang kompleks menjadi wawasan yang dapat ditindaklanjuti, kami membantu Anda memitigasi risiko dan memastikan keselarasan lingkungan dan sosial.</p>',
            ],
            'free-prior-and-informed-consent-fpic-processes' => [
                'name_id' => 'Proses Persetujuan atas Dasar Informasi Awal Tanpa Paksaan (FPIC)',
                'description_id' => '<p style="text-align: justify;">Kami menyediakan fasilitasi ahli untuk proses Pelibatan Pemangku Kepentingan dan Persetujuan atas Dasar Informasi Awal Tanpa Paksaan (FPIC), dengan memastikan keselarasan terhadap standar internasional. Pendekatan kami menekankan transparansi, kepekaan budaya, dan saling menghormati, sehingga memberdayakan masyarakat lokal untuk berpartisipasi dalam pengambilan keputusan. Dengan memperoleh dukungan masyarakat yang tulus, kami memitigasi risiko sosial dan mendorong stabilitas proyek jangka panjang.</p>',
            ],
            'biodiversity-and-environmental-modeling' => [
                'name_id' => 'Pemodelan Keanekaragaman Hayati dan Lingkungan',
                'description_id' => '<p style="text-align: justify;">Layanan Pemodelan Keanekaragaman Hayati dan Lingkungan kami menjembatani kesenjangan antara ekologi lapangan dan ilmu prediktif. Kami memanfaatkan GIS dan perangkat lunak ekologi canggih untuk memodelkan distribusi spesies, kesesuaian habitat, dan ketahanan ekosistem. Dengan menyediakan data baseline yang terperinci dan simulasi dampak, kami memungkinkan klien mengambil keputusan yang tepat untuk meminimalkan jejak ekologis dan mematuhi standar keanekaragaman hayati internasional (seperti IFC Performance Standard 6).</p>',
            ],
            'formulation-of-environmental-carrying-and-assimilative-capacity-penyusunan-daya-dukung-dan-daya-tampung-lingkungan-hidup-d3tlh' => [
                'name_id' => 'Penyusunan Daya Dukung dan Daya Tampung Lingkungan Hidup (D3TLH)',
                'description_id' => '<p style="text-align: justify;" data-start="108" data-end="523">Penyusunan Daya Dukung dan Daya Tampung Lingkungan Hidup (D3TLH) merupakan layanan penting yang bertujuan menilai kemampuan lingkungan dalam menopang aktivitas manusia sembari menjaga keseimbangan ekologisnya. Proses ini memberikan dasar ilmiah dan regulasi bagi perencanaan tata ruang yang berkelanjutan, pengendalian pembangunan, dan pengelolaan lingkungan.</p>
<p style="text-align: justify;" data-start="525" data-end="905">Layanan ini melibatkan analisis menyeluruh atas komponen lingkungan, termasuk lahan, air, udara, dan ekosistem, untuk menentukan tingkat pemanfaatan maksimum yang dapat dipertahankan tanpa menyebabkan degradasi lingkungan. Layanan ini juga mengevaluasi kapasitas lingkungan dalam menyerap polutan dan limbah, sehingga memastikan kegiatan pembangunan tetap berada dalam ambang batas yang dapat diterima.</p>
<p style="text-align: justify;" data-start="907" data-end="1249">Sejalan dengan kerangka regulasi Indonesia, penyusunan D3TLH mengintegrasikan analisis data spasial, kajian kualitas lingkungan, dan pendekatan pemodelan untuk menghasilkan keluaran yang akurat dan andal. Hal ini mencakup identifikasi batas lingkungan, rekomendasi zonasi, dan ambang batas bagi pemanfaatan sumber daya serta beban polutan.</p>
<p style="text-align: justify;" data-start="1251" data-end="1628">Komponen utama dari layanan ini adalah pelibatan pemangku kepentingan, di mana instansi pemerintah terkait dan pemangku kepentingan lokal dilibatkan untuk memastikan hasilnya selaras dengan prioritas pembangunan daerah dan arah kebijakan. Hasilnya menjadi rujukan penting bagi para pengambil keputusan dalam menyusun rencana tata ruang (RTRW), kebijakan lingkungan, dan izin pembangunan.</p>
<p style="text-align: justify;" data-start="1630" data-end="1865" data-is-last-node="" data-is-only-node="">Secara keseluruhan, penyusunan D3TLH mendukung pembangunan berkelanjutan dengan menyediakan kerangka yang jelas untuk menyeimbangkan pertumbuhan ekonomi dengan perlindungan lingkungan, meminimalkan risiko eksploitasi berlebihan, dan memastikan ketahanan ekologis jangka panjang.</p>',
            ],
            'provincial-environmental-protection-and-management-plan-rencana-perlindungan-pengelolaan-lingkungan-hidup-rpplh' => [
                'name_id' => 'Rencana Perlindungan dan Pengelolaan Lingkungan Hidup Provinsi (RPPLH)',
                'description_id' => '<p style="text-align: justify;" data-start="256" data-end="689">Rencana Perlindungan dan Pengelolaan Lingkungan Hidup (RPPLH) merupakan instrumen perencanaan strategis yang disusun untuk mengarahkan tata kelola lingkungan yang berkelanjutan di tingkat provinsi. Rencana ini berfungsi sebagai kerangka menyeluruh untuk menyeimbangkan perlindungan lingkungan dengan pembangunan sosial-ekonomi, sehingga memastikan sumber daya alam dikelola secara bertanggung jawab dan dalam batas-batas ekologis.</p>
<p style="text-align: justify;" data-start="691" data-end="1050">Penyusunan RPPLH melibatkan kajian terpadu atas kondisi lingkungan, termasuk status sumber daya alam, kualitas lingkungan, jasa ekosistem, dan tekanan yang ada akibat kegiatan pembangunan. Proses ini mengidentifikasi isu lingkungan utama, risiko, dan daya dukung yang harus dipertimbangkan dalam perencanaan kewilayahan jangka panjang.</p>
<p style="text-align: justify;" data-start="1052" data-end="1382">Selaras dengan kerangka regulasi nasional, RPPLH memberikan arahan bagi kebijakan, program, dan prioritas lingkungan dalam periode perencanaan yang ditetapkan. Rencana ini menetapkan tujuan strategis, target perlindungan, dan langkah-langkah pengelolaan, termasuk perencanaan konservasi, pengendalian pencemaran, dan pemanfaatan sumber daya secara berkelanjutan.</p>
<p style="text-align: justify;" data-start="1384" data-end="1758">Komponen penting dari RPPLH adalah pelibatan multipihak, yang melibatkan pemerintah provinsi, instansi teknis, dan pemangku kepentingan terkait untuk memastikan rencana tersebut mencerminkan prioritas daerah dan dapat diimplementasikan lintas sektor. RPPLH juga berfungsi sebagai rujukan utama bagi perencanaan tata ruang (RTRW), perencanaan pembangunan (RPJMD), dan proses perizinan lingkungan.</p>
<p style="text-align: justify;" data-start="1760" data-end="1986">Secara keseluruhan, RPPLH berfungsi sebagai alat pengambilan keputusan yang mengintegrasikan pertimbangan lingkungan ke dalam pembangunan kewilayahan, mendukung keberlanjutan jangka panjang, ketahanan, dan kepatuhan terhadap regulasi di tingkat provinsi.</p>',
            ],
            'land-acquisition-assistance-national-regulation' => [
                'name_id' => 'Pendampingan Pengadaan Tanah (Regulasi Nasional)',
                'description_id' => '<p>Equator memfasilitasi keseluruhan proses pengadaan tanah berdasarkan peraturan nasional Indonesia, meliputi tahap perencanaan, persiapan (Penlok), pelaksanaan, dan penyerahan hasil.<br>Layanan ini memastikan kepatuhan hukum melalui koordinasi dengan instansi terkait dan mendukung kompensasi yang adil dan tepat waktu bagi pihak yang terdampak.</p>',
            ],
            'land-acquisition-process-business-to-business' => [
                'name_id' => 'Proses Pengadaan Tanah (Business-to-Business)',
                'description_id' => '<p>Equator mendukung pengadaan tanah melalui pendekatan business-to-business, yang melibatkan negosiasi langsung dengan pemilik tanah atau pemegang hak.<br>Layanan ini memastikan proses yang partisipatif dan berbasis kesepakatan yang selaras dengan kebutuhan sektor swasta sembari menghormati hak masyarakat.</p>',
            ],
            'permitting-support-kkpr-iui-iki-kek-etc' => [
                'name_id' => 'Dukungan Perizinan (KKPR, IUI, IKI, KEK, dll.)',
                'description_id' => '<p>Equator membantu pengurusan perizinan seperti KKPR, IUI, IKI, dan KEK guna memungkinkan pengembangan proyek dalam kerangka hukum dan perencanaan tata ruang.<br>Layanan ini memastikan kepatuhan terhadap regulasi dan kelancaran koordinasi dengan instansi pemerintah di tingkat nasional maupun daerah.</p>',
            ],
            'capacity-building' => [
                'name_id' => 'Penguatan Kapasitas',
                'description_id' => '<p>Equator menyediakan program pelatihan dan pembelajaran mengenai Environmental and Social Safeguards (ESS) bekerja sama dengan Network of Learning Centers (NLC).<br>Layanan ini memperkuat kapasitas pemrakarsa proyek dan pemangku kepentingan dalam memahami dan menerapkan safeguard secara efektif.</p>',
            ],
            'conflict-resolution-management' => [
                'name_id' => 'Pengelolaan Penyelesaian Konflik',
                'description_id' => '<p>Equator menawarkan layanan fasilitasi dan mediasi untuk menyelesaikan sengketa yang muncul selama pengadaan tanah atau pelaksanaan proyek.<br>Layanan ini mendorong solusi yang adil dan peka budaya serta mendukung pelibatan yang damai di antara para pemangku kepentingan proyek.</p>',
            ],
            'waste-and-wastewater-management' => [
                'name_id' => 'Pengelolaan Limbah dan Air Limbah',
                'description_id' => '<p>Equator mendukung penerapan sistem pengelolaan limbah dan air limbah yang efektif dan memenuhi baku mutu lingkungan.<br>Layanan ini mengurangi risiko pencemaran dan mendorong operasi proyek yang ramah lingkungan dan berkelanjutan.</p>',
            ],
            'internal-safeguards-monitoring-and-evaluation' => [
                'name_id' => 'Pemantauan dan Evaluasi Safeguard Internal',
                'description_id' => '<p>Equator melakukan pemantauan rutin atas penerapan safeguard sosial untuk memastikan kepatuhan terhadap rencana dan kebijakan.<br>Layanan ini menyediakan data berbasis bukti untuk mendukung pengelolaan adaptif dan perbaikan berkelanjutan.</p>',
            ],
            'external-safeguards-monitoring-and-evaluation' => [
                'name_id' => 'Pemantauan dan Evaluasi Safeguard Eksternal',
                'description_id' => '<p>Equator menyediakan pemantauan dan evaluasi safeguard sosial secara independen untuk memverifikasi kepatuhan dan menilai efektivitasnya.<br>Layanan ini kerap digunakan oleh lembaga pemberi pinjaman dan pemerintah untuk memastikan transparansi, akuntabilitas, dan tindakan korektif jika diperlukan.</p>',
            ],
            'environmental-compliance-audit' => [
                'name_id' => 'Audit Kepatuhan Lingkungan',
                'description_id' => '<p>Equator melakukan audit untuk menilai kepatuhan proyek terhadap peraturan lingkungan, izin, dan rencana pengelolaan.<br>Layanan ini mengidentifikasi kesenjangan dan memberikan rekomendasi tindakan korektif secara sistematis.</p>',
            ],
            'program-impact-evaluation' => [
                'name_id' => 'Evaluasi Dampak Program',
                'description_id' => '<p>Equator melakukan evaluasi untuk menilai dampak jangka menengah dan jangka panjang dari program sosial atau lingkungan.<br>Layanan ini membantu mengukur efektivitas, efisiensi, dan keberlanjutan guna mendukung pembelajaran dan replikasi.</p>',
            ],
            'carbon-emission-monitoring' => [
                'name_id' => 'Pemantauan Emisi Karbon',
                'description_id' => '<p>Equator menawarkan layanan pemantauan dan pelaporan emisi karbon untuk mendukung strategi mitigasi iklim dan kepatuhan ESG.<br>Layanan ini membantu klien mencapai target keberlanjutan dan melaporkan kinerja gas rumah kaca secara akurat.</p>',
            ],
            'environmental-social-health-and-safety-monitoring-and-compliance-auditing-eshs' => [
                'name_id' => 'Pemantauan dan Audit Kepatuhan Lingkungan, Sosial, Kesehatan, dan Keselamatan (ESHS)',
                'description_id' => '<p style="text-align: justify;">Layanan Pemantauan dan Audit Kepatuhan ESHS kami memastikan proyek Anda menjaga standar tertinggi dalam pengelolaan lingkungan, tanggung jawab sosial, serta kesehatan dan keselamatan kerja. Kami melakukan evaluasi sistematis untuk memverifikasi kepatuhan terhadap persyaratan regulasi dan praktik terbaik internasional. Dengan menyediakan temuan audit yang dapat ditindaklanjuti dan pemantauan berkelanjutan, kami membantu Anda meminimalkan liabilitas, meningkatkan efisiensi operasional, dan menjaga reputasi Anda sebagai pengembang yang bertanggung jawab.</p>',
            ],
        ]);
    }
}
