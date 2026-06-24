<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Dummy FAQ content for the public FAQ section.
     * Idempotent: keyed on `question`, so re-running updates instead of duplicating.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'What services does Equator Group offer?',
                'answer' => 'We provide integrated geospatial and environmental consulting services, including topographic and hydrographic surveys, LiDAR and aerial mapping, GIS and spatial data management, and Environmental and Social Impact Assessments (ESIA). Our multidisciplinary team supports projects from initial feasibility through to compliance and monitoring.',
            ],
            [
                'question' => 'Which industries and sectors do you typically work with?',
                'answer' => 'Our clients span infrastructure, mining and energy, marine and coastal development, plantation and agribusiness, government agencies, and large engineering contractors. Each engagement is scoped to the regulatory and technical requirements of the relevant sector.',
            ],
            [
                'question' => 'How do I request a proposal or quotation?',
                'answer' => 'The fastest way is to submit an inquiry through our Contact page with a short description of your project scope, location, and timeline. Our consultants typically respond within one business day to arrange a scoping discussion and prepare a tailored proposal.',
            ],
            [
                'question' => 'How long does a typical survey or assessment take?',
                'answer' => 'Timelines depend on the size of the area, field conditions, and the deliverables required. A focused topographic survey may take one to two weeks, while a full ESIA can run several months due to baseline data collection, stakeholder consultation, and regulatory review. We provide an indicative schedule in every proposal.',
            ],
            [
                'question' => 'Do you handle permits and regulatory compliance?',
                'answer' => 'Yes. Our environmental team prepares documentation aligned with national regulations and international safeguard standards (such as AIIB and World Bank requirements), and we assist clients throughout the permitting and approval process, including liaison with the relevant authorities.',
            ],
            [
                'question' => 'What areas or regions do you operate in?',
                'answer' => 'We operate across Indonesia and the wider region through our network of regional offices. For projects in remote or offshore locations, we mobilise dedicated field teams and equipment as required. You can find our office locations on the Contact page.',
            ],
            [
                'question' => 'What technology and equipment do you use?',
                'answer' => 'We use survey-grade GNSS receivers, total stations, multibeam and single-beam echosounders, terrestrial and airborne LiDAR, and UAV/drone platforms for aerial imagery. Data is processed and delivered using industry-standard GIS and CAD software to ensure accuracy and interoperability.',
            ],
            [
                'question' => 'In what formats are the final deliverables provided?',
                'answer' => 'Deliverables are tailored to your needs and commonly include CAD drawings (DWG/DXF), GIS layers (SHP/GeoPackage), point clouds, orthophotos, contour and digital terrain models, and comprehensive technical reports in PDF. We can align outputs to your preferred coordinate system and data standards.',
            ],
            [
                'question' => 'Can you support ongoing monitoring after a project is completed?',
                'answer' => 'Absolutely. We offer periodic environmental monitoring, deformation and settlement monitoring, and data updates under maintenance or retainer arrangements, helping you stay compliant and track changes over the lifetime of your asset.',
            ],
            [
                'question' => 'How can I get in touch with your team?',
                'answer' => 'You can reach us through the contact form on our website, by phone, or by email — details are listed under Office Locations on the Contact page. For new projects, the contact form is the quickest route to the right specialist.',
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
