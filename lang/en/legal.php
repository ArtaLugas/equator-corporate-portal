<?php

return [

    'updated_label' => 'Last updated',
    'updated' => 'June 2026',
    'contact_cta' => 'Contact us',
    'contact_lead' => 'Questions about this policy, or want to exercise your rights?',

    /*
    |--------------------------------------------------------------------------
    | Privacy Policy
    |--------------------------------------------------------------------------
    | :company is replaced at render with the configured company name.
    */
    'privacy' => [
        'title' => 'Privacy Policy',
        'intro' => ':company operates this website and is responsible for the personal data described below. This policy explains what we collect, why, and the choices and rights you have under Indonesia’s Personal Data Protection Law (UU PDP) and, where relevant, the EU GDPR.',
        'sections' => [
            [
                'heading' => 'Who we are',
                'body' => [
                    'We are the controller of the personal data processed through this website. If you have any questions, you can reach us through the channels at the end of this policy.',
                ],
            ],
            [
                'heading' => 'Information we collect',
                'body' => ['We collect personal data in two ways:'],
                'list' => [
                    'Information you give us — when you submit the contact form we collect your name, email address, subject, and message.',
                    'Information collected automatically — when you visit the site we record limited technical data for security and aggregate analytics: your IP address, browser type (user agent), the pages you view, the referring page, and a timestamp.',
                    'When you submit an enquiry, we also record how you reached us — the landing page, the referring site, and any campaign parameters (UTM tags) or ad click identifiers (such as Google gclid or Facebook fbclid) in the link you followed — to understand which channels generate enquiries.',
                    'Cookies and similar technologies — described in our Cookie Policy.',
                ],
            ],
            [
                'heading' => 'How we use your information',
                'body' => ['We use personal data to:'],
                'list' => [
                    'respond to and manage your inquiry;',
                    'operate, secure, and maintain the website;',
                    'understand aggregate usage so we can improve our content;',
                    'comply with our legal obligations.',
                ],
            ],
            [
                'heading' => 'Legal basis for processing',
                'body' => ['We rely on the following legal bases:'],
                'list' => [
                    'Consent — when you submit the contact form and when you accept optional cookies.',
                    'Legitimate interest — to keep the site secure and to measure aggregate traffic, using the minimum data necessary and short retention.',
                    'Legal obligation — where we must retain or disclose data to comply with applicable law.',
                ],
            ],
            [
                'heading' => 'Cookies and tracking',
                'body' => [
                    'We use a small number of cookies and similar technologies. You can review the full list and change your choices at any time in our Cookie Policy.',
                ],
            ],
            [
                'heading' => 'Sharing and third parties',
                'body' => ['We do not sell your personal data. We share it only with service providers who process it on our behalf, under appropriate safeguards:'],
                'list' => [
                    'Cloudflare — bot and spam protection (Turnstile) on our forms.',
                    'Our email/SMTP provider (Brevo) — to deliver replies to your inquiries.',
                    'Our hosting provider — to operate the website.',
                ],
            ],
            [
                'heading' => 'International transfers',
                'body' => [
                    'Some of these providers may process data outside Indonesia. Where they do, we rely on appropriate safeguards and limit the data to the minimum necessary.',
                ],
            ],
            [
                'heading' => 'How long we keep data',
                'body' => [],
                'list' => [
                    'Contact messages — kept only as long as needed to handle your inquiry and our records, then deleted.',
                    'Visitor analytics — automatically deleted after 90 days.',
                ],
            ],
            [
                'heading' => 'How we protect your data',
                'body' => [
                    'We protect personal data with encryption in transit (HTTPS), access controls, and security headers. No method of transmission is completely secure, but we take measures appropriate to the risk.',
                ],
            ],
            [
                'heading' => 'Your rights',
                'body' => ['Under UU PDP — and the GDPR where it applies — you have the right to:'],
                'list' => [
                    'access the personal data we hold about you;',
                    'correct inaccurate or incomplete data;',
                    'request deletion of your data;',
                    'object to or restrict certain processing;',
                    'withdraw consent at any time;',
                    'lodge a complaint with the relevant supervisory authority.',
                ],
            ],
            [
                'heading' => 'Children’s privacy',
                'body' => [
                    'This website is intended for a professional audience and is not directed to children. We do not knowingly collect personal data from children.',
                ],
            ],
            [
                'heading' => 'Changes to this policy',
                'body' => [
                    'We may update this policy from time to time. The “last updated” date above reflects the current version, and material changes will be highlighted on this page.',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie Policy
    |--------------------------------------------------------------------------
    */
    'cookies' => [
        'title' => 'Cookie Policy',
        'intro' => 'This policy explains how :company uses cookies and similar technologies, and how you can control them. It complements our Privacy Policy.',
        'sections' => [
            [
                'heading' => 'What cookies are',
                'body' => [
                    'Cookies are small text files stored on your device when you visit a website. We also use similar technologies such as local storage. Together they help the site function, stay secure, and — with your consent — measure usage.',
                ],
            ],
            [
                'heading' => 'Categories we use',
                'body' => [],
                'list' => [
                    'Necessary — required for the site to work (sessions, security, anti-spam, and remembering your cookie choices). These cannot be switched off.',
                    'Analytics — help us understand aggregate usage. With your consent we use Google Analytics 4 (GA4); these cookies load only after you accept this category.',
                    'Marketing — not used at this time; reserved for any future advertising or campaign measurement, which would require your consent.',
                    'Preferences — remember choices that personalize your experience.',
                ],
            ],
            [
                'heading' => 'First-party analytics and your IP address',
                'body' => [
                    'For security and aggregate statistics we record limited technical data, including your IP address, for up to 90 days on the basis of our legitimate interest. You can object to this or request deletion using the contact details in our Privacy Policy.',
                    'When you submit a form, we also record how you reached us — the landing page, referrer, and any campaign or ad-click parameters (UTM, gclid, fbclid) in your link. See our Privacy Policy for details.',
                ],
            ],
            [
                'heading' => 'Managing your preferences',
                'body' => [
                    'You can accept, reject, or customize optional cookies using the banner shown on your first visit, and change your choice at any time via “Cookie Preferences” in the footer. You can also block or delete cookies through your browser settings, though some necessary cookies are required for the site to work.',
                ],
            ],
            [
                'heading' => 'Changes to this policy',
                'body' => [
                    'We may update this policy from time to time. The “last updated” date above reflects the current version.',
                ],
            ],
        ],
        'table' => [
            'caption' => 'Cookies and similar technologies we use',
            'columns' => [
                'name' => 'Name',
                'provider' => 'Provider',
                'purpose' => 'Purpose',
                'category' => 'Category',
                'duration' => 'Duration',
            ],
            'rows' => [
                [
                    'name' => 'laravel_session',
                    'provider' => 'Equator (first-party)',
                    'purpose' => 'Maintains your session and security state.',
                    'category' => 'Necessary',
                    'duration' => 'Session (up to 60 minutes)',
                ],
                [
                    'name' => 'XSRF-TOKEN',
                    'provider' => 'Equator (first-party)',
                    'purpose' => 'Protects forms against cross-site request forgery.',
                    'category' => 'Necessary',
                    'duration' => 'Session',
                ],
                [
                    'name' => 'cf_clearance / Turnstile',
                    'provider' => 'Cloudflare',
                    'purpose' => 'Distinguishes humans from bots on our contact and login forms.',
                    'category' => 'Necessary (security)',
                    'duration' => 'Set by Cloudflare',
                ],
                [
                    'name' => 'equator_cookie_consent',
                    'provider' => 'Equator (first-party)',
                    'purpose' => 'Stores your cookie preferences so we do not ask again.',
                    'category' => 'Necessary',
                    'duration' => '180 days',
                ],
                [
                    'name' => '_ga, _ga_*',
                    'provider' => 'Google Analytics (GA4)',
                    'purpose' => 'Distinguishes visitors to measure aggregate site usage. Set only after you accept Analytics cookies.',
                    'category' => 'Analytics',
                    'duration' => 'Up to 2 years',
                ],
            ],
        ],
    ],

];
