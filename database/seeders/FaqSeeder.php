<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * FAQ content for the public FAQ section, compiled from the official
     * Equator Company Profile (social & environmental consulting, est. 1999).
     * Idempotent: keyed on `question_en`, so re-running updates instead of
     * duplicating. Indonesian translations are handled separately (admin /
     * IndonesianTranslationSeeder).
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'What is Equator and what does it do?',
                'answer' => 'Equator is a social and environmental consulting firm that has been operating since 1999, delivering high-impact, inclusive, and sustainable solutions for development projects across Indonesia and beyond. We specialise in land acquisition and resettlement planning, stakeholder engagement, environmental and social impact assessments, and community-driven programs — combining local knowledge with global best practice so that development respects human rights, indigenous values, and environmental integrity.',
            ],
            [
                'question' => 'What services does Equator provide?',
                'answer' => 'We provide end-to-end consulting across the full project lifecycle, organised into three stages: Assessment and Planning (such as LARAP, ESIA, environmental permitting, and feasibility studies), Implementation and Assistance (such as land acquisition assistance, permitting support, conflict resolution, and capacity building), and Monitoring and Evaluation (such as internal and external safeguards monitoring, compliance audits, and impact evaluation). Our work spans consultancy, research, and capacity building in both social and environmental fields.',
            ],
            [
                'question' => 'Do you prepare LARAP and land acquisition documents?',
                'answer' => 'Yes. Land Acquisition and Resettlement Action Plans (LARAP) are one of our core specialisations. We prepare and help implement land acquisition and resettlement planning — including Livelihood Restoration Programs (LRP), Stakeholder Engagement Plans (SEP), Grievance Redress Mechanisms (GRM), and national documents such as DPPT — for infrastructure and development projects under both national regulations and international standards.',
            ],
            [
                'question' => 'Do you handle ESIA, AMDAL, and environmental permitting?',
                'answer' => 'Yes. We prepare Environmental and Social Impact Assessments (ESIA), Social Impact Assessments (SIA), and Indonesian environmental permitting documents such as AMDAL/EIA, UKL-UPL, and SPPL, along with Environmental and Social Management Plans (ESMP) and related studies. We support clients throughout the permitting and approval process.',
            ],
            [
                'question' => 'Which standards and safeguard frameworks do you work with?',
                'answer' => 'We align our work with both Indonesian national regulations and international safeguard standards, including the Equator Principles, IFC Performance Standards, and the requirements of multilateral lenders such as the World Bank, ADB, AIIB, JICA, and KfW. This ensures projects meet local compliance obligations as well as international good practice.',
            ],
            [
                'question' => 'Which sectors and clients do you typically work with?',
                'answer' => 'We partner with governments, private sector clients, international development agencies, and grassroots communities. Our project experience spans energy (hydropower and geothermal), mining, water resources and dams, transportation and toll roads, healthcare facilities, and industrial areas — working with clients such as PT PLN, government ministries, and internationally funded programs.',
            ],
            [
                'question' => 'Where does Equator operate, and where are your offices?',
                'answer' => 'We operate across Indonesia and the wider region. Our offices are in Bogor (Jl. Letjen. Ibrahim Adjie 197, Bogor 16117) and South Jakarta (Centennial Tower, Jl. Jend. Gatot Subroto Kav. 24-25, South Jakarta 12930). Through LPM Equator — a nationwide network of local branches established since 1999 — we maintain a presence and field capacity across all Indonesian provinces.',
            ],
            [
                'question' => 'What certifications and credentials does Equator hold?',
                'answer' => 'Equator is a registered professional service provider (LPJP Registered) and is certified to ISO 9001:2015 (Quality Management) and ISO 14001:2015 (Environmental Management). We also hold Non-Construction Consulting Business Certification across a broad range of service classifications (KBLI), reflecting our commitment to quality, safety, and the environment.',
            ],
            [
                'question' => 'Do you provide training and capacity building?',
                'answer' => 'Yes. Through our Network of Learning Center for Environmental and Social Sustainability (NLC), we deliver structured capacity-building programs and training — including Environmental and Social Impact Assessment training, Land Acquisition and Resettlement Action Plan training, Indigenous Peoples Plan training, and Biodiversity Management Plan training — to strengthen safeguard implementation.',
            ],
            [
                'question' => 'Can you support monitoring and evaluation after a project is implemented?',
                'answer' => 'Yes. We provide robust monitoring and evaluation services, including internal and external safeguards monitoring, environmental compliance audits, program impact evaluation, and carbon emission monitoring — helping clients maintain accountability and sustain long-term impact.',
            ],
            [
                'question' => 'Do you manage stakeholder engagement, grievances, and indigenous peoples plans?',
                'answer' => 'Yes. Inclusive engagement is central to our approach. We develop Stakeholder Engagement Plans (SEP), Grievance Redress Mechanisms (GRM), and Indigenous Peoples Plans (IPP), and we facilitate conflict resolution — ensuring affected communities are heard and that development respects human rights and indigenous values.',
            ],
            [
                'question' => 'How do I get in touch or request a proposal?',
                'answer' => 'The quickest way is to contact us through our website (www.equatorgroup.id) or the contact form, by email at office@equatorgroup.id, or by phone at +62 819-1111-7109. Share a brief description of your project scope and location, and our team will arrange a scoping discussion and prepare a tailored proposal.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['question_en' => $faq['question']],
                [
                    'answer_en' => $faq['answer'],
                    'display_order' => $index + 1,
                ]
            );
        }
    }
}
