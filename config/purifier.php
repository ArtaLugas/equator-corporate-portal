<?php

/**
 * HTML Purifier — sanitizes every CMS rich-text field on write
 * (App\Models\Concerns\HasTranslations → Purifier::clean()).
 *
 * The 'default' profile below intentionally allows the full set of tags the
 * CKEditor toolbar can produce (headings, lists, tables, blockquote, images,
 * figures, horizontal rules) PLUS safe media embeds. Iframes are restricted by
 * URI.SafeIframeRegexp to YouTube/Vimeo embed URLs only — any other iframe src
 * is stripped, so this stays XSS-safe.
 *
 * Published from vendor/mews/purifier; keep the top-level keys intact.
 */
return [
    'encoding'         => 'UTF-8',
    'finalize'         => true,
    'ignoreNonStrings' => false,
    'cachePath'        => storage_path('app/purifier'),
    'cacheFileMode'    => 0755,

    'settings' => [

        'default' => [
            'HTML.Doctype'          => 'HTML 4.01 Transitional',

            // Whitelist mirrors the CKEditor toolbar output. `iframe` is allowed
            // here but its src is still gated by URI.SafeIframeRegexp below.
            'HTML.Allowed'          => implode(',', [
                'div[class|style|data-oembed-url]', 'p[class|style]', 'br', 'hr',
                'span[class|style]', 'b', 'strong', 'i', 'em', 'u', 's', 'sub', 'sup',
                'a[href|title|target|rel]',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'ul[class|style]', 'ol[class|style|start|reversed]', 'li[class|style]',
                'blockquote',
                'figure[class]', 'figcaption',
                'img[src|alt|width|height|class|style]',
                'table[class|style|border]', 'thead', 'tbody', 'tfoot',
                'tr[class|style]', 'td[colspan|rowspan|class|style]', 'th[colspan|rowspan|scope|class|style]', 'caption',
                'iframe[src|width|height|frameborder|allowfullscreen|class|style]',
            ]),

            // Only YouTube (incl. nocookie) and Vimeo embed URLs survive.
            'HTML.SafeIframe'       => true,
            'URI.SafeIframeRegexp'  => '%^(https?:)?//(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',

            'CSS.AllowedProperties' => implode(',', [
                'font', 'font-size', 'font-weight', 'font-style', 'font-family',
                'text-decoration', 'text-align', 'color', 'background-color',
                'line-height', 'letter-spacing', 'list-style-type',
                'width', 'height', 'max-width', 'border', 'border-collapse',
                'padding', 'padding-left', 'padding-right', 'padding-top', 'padding-bottom',
                'margin', 'vertical-align',
                // NOTE: `position`/`top`/`left` are NOT in HTMLPurifier's CSS
                // whitelist and cannot be allowed — the CKEditor media embed's
                // 16:9 hack relies on them, so the responsive frame is re-imposed
                // via CSS in the public layout instead.
            ]),

            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],

        'youtube' => [
            'HTML.SafeIframe'      => 'true',
            'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
        ],

        // HTML5 element/attribute definitions (figure, allowfullscreen, …).
        'custom_definition' => [
            'id'    => 'html5-definitions',
            'rev'   => 4,
            'debug' => false,
            'elements' => [
                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
                ['figcaption', 'Inline', 'Flow', 'Common'],
                ['s',   'Inline', 'Inline', 'Common'],
                ['sub', 'Inline', 'Inline', 'Common'],
                ['sup', 'Inline', 'Inline', 'Common'],
            ],
            'attributes' => [
                ['iframe', 'allowfullscreen', 'Bool'],
                // `reversed` is HTML5-only; HTMLPurifier (HTML 4.01 doctype) does
                // not know it, so it must be defined before it can be whitelisted.
                ['ol', 'reversed', 'Bool'],
                // CKEditor stores the media source here; it must survive so the
                // editor can re-recognise the embed when the content is re-opened.
                ['div', 'data-oembed-url', 'URI'],
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],

        'custom_attributes' => [
            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
        ],

        'custom_elements' => [
            ['u', 'Inline', 'Inline', 'Common'],
        ],
    ],
];
