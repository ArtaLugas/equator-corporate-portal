{{--
    Standalone error layout — Equator visual identity, ZERO database/asset
    dependencies. Used by every resources/views/errors/{code} page.

    Why self-contained (no @vite, no app_setting(), no DB queries, no JS):
    an error page must render even when the cause IS the database, the cache,
    or the asset pipeline. All styling is inline; all links use url() (no route
    resolution). This is the deliberate trade-off behind the "lightweight error
    layout" decision — resilient first, on-brand second.

    Slots: @section('code'), @section('eyebrow'), @section('error_title'),
    @section('error_message').
--}}
@php
    $localeIso = config('locales.supported.'.app()->getLocale().'.iso', 'en-US');
    $appName = config('app.name', 'Equator Group');
@endphp
<!DOCTYPE html>
<html lang="{{ $localeIso }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Error pages must never be indexed, but their links may still be followed. --}}
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#263592">
    <title>@yield('error_title') — {{ $appName }}</title>

    <style>
        :root {
            --eq-dark: #263592;
            --eq-darker: #141A45;
            --eq-bright: #006CCD;
            --eq-light: #80C7E3;
            --eq-orange: #FFB74D;
            --eq-text: #333333;
            --eq-muted: #5b6170;
            --eq-line: #eef0f4;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            color: var(--eq-text);
            font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Thin branded hairline across the very top. */
        body::before {
            content: "";
            display: block;
            height: 3px;
            background: linear-gradient(90deg, var(--eq-dark), var(--eq-bright));
        }

        a { color: inherit; }

        .eq-bar {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 1.25rem clamp(1.25rem, 5vw, 3rem);
            border-bottom: 1px solid var(--eq-line);
        }

        .eq-bar__logo {
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--eq-dark);
            font-size: 1.05rem;
            text-decoration: none;
        }

        .eq-bar__dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: var(--eq-orange);
            display: inline-block;
        }

        .eq-main {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(2.5rem, 8vw, 5rem) clamp(1.25rem, 5vw, 3rem);
            position: relative;
            overflow: hidden;
        }

        /* Restrained brand accent — one soft glow, low opacity. */
        .eq-main::before {
            content: "";
            position: absolute;
            top: -18%;
            left: 50%;
            width: min(38rem, 90vw);
            height: min(38rem, 90vw);
            transform: translateX(-50%);
            background: radial-gradient(circle, rgba(128, 199, 227, 0.18), rgba(128, 199, 227, 0) 70%);
            pointer-events: none;
        }

        .eq-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 34rem;
            text-align: center;
        }

        .eq-eyebrow {
            margin: 0 0 1rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: var(--eq-bright);
        }

        .eq-code {
            margin: 0 0 1.25rem;
            font-size: clamp(3.5rem, 14vw, 5.5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.03em;
            color: var(--eq-dark);
        }

        .eq-title {
            margin: 0 0 0.75rem;
            font-size: clamp(1.4rem, 4vw, 1.9rem);
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--eq-darker);
        }

        .eq-msg {
            margin: 0 auto 2rem;
            max-width: 30rem;
            font-size: 1rem;
            color: var(--eq-muted);
        }

        .eq-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .eq-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.6rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 0.4rem;
            border: 1px solid transparent;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .eq-btn--primary {
            background: var(--eq-dark);
            color: #ffffff;
        }

        .eq-btn--primary:hover { background: var(--eq-bright); }

        .eq-btn--ghost {
            background: transparent;
            color: var(--eq-dark);
            border-color: #d8dbe4;
        }

        .eq-btn--ghost:hover {
            border-color: var(--eq-dark);
            background: #f7f8fb;
        }

        /* Visible, on-brand keyboard focus for accessibility. */
        a:focus-visible {
            outline: 2px solid var(--eq-bright);
            outline-offset: 3px;
            border-radius: 0.25rem;
        }

        .eq-foot {
            padding: 1.5rem clamp(1.25rem, 5vw, 3rem);
            border-top: 1px solid var(--eq-line);
            font-size: 0.8rem;
            color: var(--eq-muted);
            text-align: center;
        }

        .eq-foot a {
            color: var(--eq-dark);
            text-decoration: none;
            font-weight: 600;
        }

        .eq-foot a:hover { text-decoration: underline; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>
    <header class="eq-bar">
        <a class="eq-bar__logo" href="{{ url('/') }}">{{ $appName }}</a>
        <span class="eq-bar__dot" aria-hidden="true"></span>
    </header>

    <main class="eq-main" role="main">
        <div class="eq-card">
            <p class="eq-eyebrow">@yield('eyebrow')</p>
            <div class="eq-code" aria-hidden="true">@yield('code')</div>
            <h1 class="eq-title">@yield('error_title')</h1>
            <p class="eq-msg">@yield('error_message')</p>

            <div class="eq-actions">
                <a class="eq-btn eq-btn--primary" href="{{ url('/') }}">{{ __('errors.cta_home') }}</a>
                <a class="eq-btn eq-btn--ghost" href="{{ url('/contact') }}">{{ __('errors.cta_contact') }}</a>
            </div>
        </div>
    </main>

    <footer class="eq-foot">
        &copy; {{ date('Y') }} {{ $appName }}.
        <a href="{{ url('/') }}">{{ __('errors.cta_home') }}</a>
        &middot;
        <a href="{{ url('/contact') }}">{{ __('errors.cta_contact') }}</a>
    </footer>
</body>
</html>
