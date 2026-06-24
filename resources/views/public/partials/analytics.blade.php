{{--
    Google Analytics 4 — consent-gated. gtag.js is loaded ONLY after the visitor
    grants the "Analytics" cookie category (via the consent banner). No pings fire
    before consent. The measurement ID comes from the CMS settings — never hardcoded.

    Events: page_view (auto), contact_form_submit, cta_click, file_download,
    external_link_click. CTAs opt in with data-track="cta" (+ optional
    data-track-label) so tracking is explicit and easy to extend.
--}}
@php($ga4 = app_setting('ga4_measurement_id'))

@if ($ga4)
    <script>
        (function () {
            var GA_ID = @json($ga4);
            var CONSENT_COOKIE = @json(config('cookie_consent.cookie_name', 'equator_cookie_consent'));
            var loaded = false;

            function analyticsGranted() {
                try {
                    var m = document.cookie.match(new RegExp('(?:^|; )' + CONSENT_COOKIE + '=([^;]*)'));
                    if (!m) return false;
                    var data = JSON.parse(decodeURIComponent(m[1]));
                    return !!(data && data.categories && data.categories.analytics);
                } catch (e) {
                    return false;
                }
            }

            function track(name, params) {
                if (window.gtag) window.gtag('event', name, params || {});
            }

            function bindEvents() {
                // Contact form submit.
                document.querySelectorAll('form[data-track-form="contact"]').forEach(function (form) {
                    form.addEventListener('submit', function () {
                        track('contact_form_submit', { form_name: 'contact' });
                    });
                });

                // Delegated clicks: CTA, file download, external link.
                document.addEventListener('click', function (event) {
                    var el = event.target.closest('a, button');
                    if (!el) return;

                    if (el.dataset && el.dataset.track === 'cta') {
                        track('cta_click', {
                            cta_label: el.dataset.trackLabel || (el.textContent || '').trim().slice(0, 80),
                            link_url: el.href || null
                        });
                        return;
                    }

                    if (el.tagName !== 'A' || !el.href) return;

                    var url;
                    try { url = new URL(el.href, location.href); } catch (e) { return; }

                    if (/\.(pdf|docx?|xlsx?|pptx?|zip|rar|csv|dwg|dxf|kml|kmz)$/i.test(url.pathname)) {
                        track('file_download', {
                            file_name: decodeURIComponent(url.pathname.split('/').pop()),
                            link_url: url.href
                        });
                        return;
                    }

                    if (url.host && url.host !== location.host) {
                        track('external_link_click', { link_url: url.href, link_domain: url.host });
                    }
                });
            }

            function loadGA() {
                if (loaded || !GA_ID) return;
                loaded = true;

                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA_ID);
                document.head.appendChild(s);

                window.dataLayer = window.dataLayer || [];
                window.gtag = function () { window.dataLayer.push(arguments); };
                window.gtag('js', new Date());
                window.gtag('config', GA_ID, { anonymize_ip: true });

                bindEvents();
            }

            if (analyticsGranted()) {
                loadGA();
            }

            // The consent banner dispatches this when the visitor saves a choice.
            window.addEventListener('cookie-consent-updated', function (e) {
                if (e && e.detail && e.detail.analytics) loadGA();
            });
        })();
    </script>
@endif
