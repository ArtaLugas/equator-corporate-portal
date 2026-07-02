@php
    $activeLocale = app()->getLocale();
    $localeMeta = config('locales.supported.'.$activeLocale, []);
    $localeIso = $localeMeta['iso'] ?? 'en-US';
@endphp
<!DOCTYPE html>
<html lang="{{ $localeIso }}" dir="{{ $localeMeta['dir'] ?? 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', app_setting('meta_title') ?: app_setting('company_name', 'Equator Group'))</title>

    <meta name="description" content="@yield('meta_description', app_setting('meta_description', app_setting('tagline', '')))">

    @if (app_setting('meta_keywords'))
        <meta name="keywords" content="{{ app_setting('meta_keywords') }}">
    @endif

    {{-- Canonical: the current localized URL (query string excluded). --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- hreflang alternates — one per locale + x-default, preserving the current page. --}}
    @foreach (config('locales.supported', []) as $hrefCode => $hrefMeta)
        <link rel="alternate" hreflang="{{ $hrefCode }}" href="{{ locale_url($hrefCode) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ locale_url(config('locales.default')) }}">

    {{-- og:image — a page may override via @section('og_image', $url); falls back to setting/logo. --}}
    @php($ogImage = app_setting('og_image') ?: app_setting('logo'))
    @php($ogImageUrl = trim($__env->yieldContent('og_image', $ogImage ? asset('storage/' . $ogImage) : '')))

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ app_setting('company_name', 'Equator Group') }}">
    <meta property="og:title" content="@yield('title', app_setting('company_name', 'Equator Group'))">
    <meta property="og:description" content="@yield('meta_description', app_setting('meta_description', app_setting('tagline', '')))">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', $localeIso) }}">
    @foreach (config('locales.supported', []) as $ogCode => $ogMeta)
        @if ($ogCode !== $activeLocale)
            <meta property="og:locale:alternate" content="{{ str_replace('-', '_', $ogMeta['iso']) }}">
        @endif
    @endforeach
    @if ($ogImageUrl)
        <meta property="og:image" content="{{ $ogImageUrl }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', app_setting('company_name', 'Equator Group'))">
    <meta name="twitter:description" content="@yield('meta_description', app_setting('meta_description', app_setting('tagline', '')))">
    @if ($ogImageUrl)
        <meta name="twitter:image" content="{{ $ogImageUrl }}">
    @endif

    @if (app_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . app_setting('favicon')) }}">
    @endif

    {{-- Google Search Console verification (from CMS settings). --}}
    @if (app_setting('gsc_verification'))
        <meta name="google-site-verification" content="{{ app_setting('gsc_verification') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Responsive 16:9 frame for CKEditor media embeds (YouTube/Vimeo). HTML
         Purifier strips the `position` the inline 16:9 hack needs, collapsing the
         box to 0px height — so we re-impose a clean responsive frame here. Inline
         (not app.css) so it works on shared hosting without a Vite rebuild. --}}
    <style>
        figure.media { margin: 1.75rem 0; }
        figure.media > div,
        figure.media > div > div {
            position: static !important;
            height: auto !important;
            padding: 0 !important;
        }
        figure.media iframe {
            display: block;
            width: 100% !important;
            height: auto !important;
            aspect-ratio: 16 / 9;
            border: 0;
            border-radius: 0.5rem;
        }
    </style>

    @stack('head')

    {{-- Google Analytics 4 — consent-gated; renders only when a GA4 ID is configured. --}}
    @include('public.partials.analytics')
</head>
<body class="bg-white font-sans text-equator-text antialiased">

    @include('public.partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('public.partials.footer')

    {{-- Cookie consent banner (necessary cookie only; stores the visitor's choice). --}}
    <x-public.cookie-consent />

</body>
</html>
