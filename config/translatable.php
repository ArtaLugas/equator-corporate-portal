<?php

/*
|--------------------------------------------------------------------------
| Translatable Registry
|--------------------------------------------------------------------------
|
| The ONLY place translatable fields are declared. Consumed by:
|   - the HasTranslations trait (resolution, fillable expansion, search)
|   - the migration generator / phased migrations (column scaffolding)
|   - admin FormRequests (per-locale validation rules)
|
| Shape:  '<table>' => ['fields' => [...], 'html' => [...]]
|   - 'fields' : every column that becomes <field>_<locale> per locale.
|   - 'html'   : subset of 'fields' that holds rich text; sanitized with
|                Purifier on write for every locale column.
|
| NOT translated (stay single-column): slug, image, status, is_featured,
| email, phone, dates, foreign keys, display_order, icon. Slug is generated
| from the default-locale field so public URLs stay stable.
|
| Columns are added per-table in phased migrations (expand → backfill →
| contract); declaring a table here does not by itself alter the schema.
|
*/

return [

    'services' => [
        'fields' => ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
        'html' => ['description'],
    ],

    'projects' => [
        'fields' => ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
        'html' => ['description'],
    ],

    'news' => [
        'fields' => ['title', 'content', 'meta_title', 'meta_description', 'meta_keywords'],
        'html' => ['content'],
    ],

    'about_sections' => [
        'fields' => ['name'],
        'html' => [],
    ],

    'about_contents' => [
        'fields' => ['title', 'content'],
        'html' => ['content'],
    ],

    'faqs' => [
        // answer is plain text (admin textarea, rendered escaped via {!! nl2br(e()) !!}),
        // so it is NOT an HTML field — sanitizing it would double-encode entities.
        'fields' => ['question', 'answer'],
        'html' => [],
    ],

    'core_values' => [
        // description is rich text (admin WYSIWYG, rendered with {!! !!} on the
        // About page) → sanitized per locale by the trait.
        'fields' => ['title', 'description'],
        'html' => ['description'],
    ],

    'teams' => [
        // 'name' is a person's name — intentionally NOT translated.
        'fields' => ['position', 'bio'],
        'html' => [],
    ],

    'service_categories' => [
        'fields' => ['name', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
        'html' => ['description'],
    ],

    'hero_banners' => [
        // homepage hero text; subtitle & button_text are plain text.
        'fields' => ['title', 'subtitle', 'button_text'],
        'html' => [],
    ],

    'key_metrics' => [
        // only the label is translated; 'value' (e.g. "200+") stays single.
        'fields' => ['label'],
        'html' => [],
    ],

    'about_histories' => [
        'fields' => ['title', 'description'],
        'html' => ['description'],
    ],

    'company_documents' => [
        'fields' => ['title', 'description'],
        'html' => ['description'],
    ],

    'office_locations' => [
        // 'address' is plain text; phone/email/map_embed stay single.
        'fields' => ['name', 'address'],
        'html' => [],
    ],

    'company_credentials' => [
        // title/issuer are single-line; description is rich text (WYSIWYG,
        // rendered with {!! !!} on the public detail page) → Purifier per locale.
        'fields' => ['title', 'issuer', 'description'],
        'html' => ['description'],
    ],

    'company_credential_items' => [
        // Short list items (e.g. "KBLI 70209"). description is plain text.
        'fields' => ['title', 'description'],
        'html' => [],
    ],

];
