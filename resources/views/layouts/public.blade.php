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

    {{-- Sticky Mobile CTA — muncul setelah user men-scroll (di luar halaman Contact). --}}
    @unless (request()->routeIs('contact'))
        <div x-data="{ show: false }" @scroll.window.passive="show = window.scrollY > 480" x-show="show" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-full opacity-0"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-4 pb-[calc(0.7rem+env(safe-area-inset-bottom))] pt-3 shadow-[0_-8px_24px_-12px_rgba(15,23,42,0.25)] backdrop-blur lg:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('contact') }}" data-track="cta" data-track-label="sticky_contact"
                    class="flex flex-1 items-center justify-center gap-2 bg-equator-orange px-5 py-3.5 text-xs font-bold uppercase tracking-[0.15em] text-white transition-colors hover:bg-equator-dark">
                    {{ __('common.contact_team') }}
                    <i class="bi bi-arrow-right text-sm" aria-hidden="true"></i>
                </a>
                @if (primary_office()?->phone)
                    <a href="tel:{{ primary_office()->phone }}" aria-label="{{ __('common.call_us') }}"
                        class="flex h-12 w-12 shrink-0 items-center justify-center border border-slate-200 text-equator-dark transition-colors hover:border-equator-dark hover:bg-equator-dark hover:text-white">
                        <i class="bi bi-telephone-fill text-base" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>
    @endunless

    {{-- Cookie consent banner (necessary cookie only; stores the visitor's choice). --}}
    <x-public.cookie-consent />

</body>
</html>
