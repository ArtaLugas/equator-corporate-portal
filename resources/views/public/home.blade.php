@extends('layouts.public')

@section('title', app_setting('meta_title') ?: app_setting('company_name', 'Equator Group') . ' — ' . app_setting('tagline', 'Environmental, Social & ESG Advisory'))

@section('meta_description', app_setting('meta_description', app_setting('tagline', 'Multidisciplinary environmental, social and ESG advisory across sustainability, resilience, engineering and development sectors.')))

@push('head')
    {{-- Preload the LCP hero image for a faster Largest Contentful Paint. --}}
    @if ($heroBanners->first()?->image)
        <link rel="preload" as="image" href="{{ asset('storage/' . $heroBanners->first()->image) }}" fetchpriority="high">
    @endif

    {{-- Organization + WebSite structured data (rich results / knowledge panel). --}}
    @php
        $orgSchema = [
            '@type' => 'Organization',
            'name' => app_setting('company_name', 'Equator Group'),
            'url' => url('/'),
        ];
        if (app_setting('logo')) {
            $orgSchema['logo'] = asset('storage/' . app_setting('logo'));
        }
        $homeJsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $orgSchema,
                ['@type' => 'WebSite', 'name' => app_setting('company_name', 'Equator Group'), 'url' => url('/')],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($homeJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    {{-- Hero motion system — satu motion language (CSS-only, nonaktif saat reduce-motion) --}}
    <style>
        :root {
            --hero-ease: cubic-bezier(0.22, 1, 0.36, 1);
            /* tenang, decelerate */
            --hero-reveal: 700ms;
            --hero-interval: 8000ms;
            /* = durasi autoplay */
        }

        /* 3 — Ken Burns: drift halus & linier (zoom 6%, lebih lambat dari interval) */
        @keyframes heroKenBurns {
            from {
                transform: scale(1.04) translate3d(0, 0, 0);
            }

            to {
                transform: scale(1.10) translate3d(-1.5%, -1%, 0);
            }
        }

        .hero-kenburns {
            animation: heroKenBurns 12000ms linear forwards;
        }

        /* 4/5/6 — Content reveal: hybrid (manual = rise+fade, autoplay = fade-only) */
        @keyframes heroRise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes heroFade {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .hero-reveal.is-manual {
            animation: heroRise var(--hero-reveal) var(--hero-ease) both;
        }

        .hero-reveal.is-auto {
            animation: heroFade var(--hero-reveal) var(--hero-ease) both;
        }

        .hero-reveal-1 {
            animation-delay: 120ms;
        }

        .hero-reveal-2 {
            animation-delay: 240ms;
        }

        /* 7 — Progress indicator sinkron autoplay (pseudo-element, bukan node baru) */
        @keyframes heroProgress {
            from {
                transform: scaleX(0);
            }

            to {
                transform: scaleX(1);
            }
        }

        .hero-dot {
            position: relative;
            overflow: hidden;
        }

        .hero-dot--active::after {
            content: "";
            position: absolute;
            inset: 0;
            background: #FFB74D;
            transform-origin: left;
            transform: scaleX(0);
            animation: heroProgress var(--hero-interval) linear forwards;
        }

        .hero-paused .hero-dot--active::after {
            animation-play-state: paused;
        }

        /* 9 — Scroll cue: drift tenang (bukan bounce) */
        @keyframes heroScrollDrift {
            0% {
                transform: translateY(-100%);
                opacity: 0;
            }

            35% {
                opacity: 1;
            }

            100% {
                transform: translateY(200%);
                opacity: 0;
            }
        }

        .hero-scroll-drift {
            animation: heroScrollDrift 2400ms var(--hero-ease) infinite;
        }

        @media (prefers-reduced-motion: reduce) {

            .hero-kenburns {
                animation: none;
                transform: scale(1.03);
            }

            .hero-reveal.is-manual,
            .hero-reveal.is-auto {
                animation: none;
                opacity: 1;
                transform: none;
            }

            .hero-dot--active::after {
                animation: none;
                transform: scaleX(1);
            }

            .hero-scroll-drift {
                animation: none;
            }
        }
    </style>

    {{-- ============================ HERO CAROUSEL (cinematic opener) ============================ --}}
    @if ($heroBanners->isNotEmpty())
        <section x-data="{
            active: 0,
            total: {{ $heroBanners->count() }},
            autoplay: null,
            paused: false,
            touchX: 0,
            manual: true,
            start() { if (this.total > 1 && !this.autoplay && !this.paused) this.autoplay = setInterval(() => this.auto(), 8000); },
            stop() {
                clearInterval(this.autoplay);
                this.autoplay = null;
            },
            go(i, manual = true) {
                if (this.total <= 1) return;
                this.manual = manual;
                this.active = (i + this.total) % this.total;
            },
            auto() { this.go(this.active + 1, false); },
            next() { this.go(this.active + 1, true); this.restart(); },
            prev() { this.go(this.active - 1, true); this.restart(); },
            select(i) { this.go(i, true); this.restart(); },
            restart() { this.stop(); this.start(); },
            toggle() {
                this.paused = !this.paused;
                this.paused ? this.stop() : this.start();
            },
            onTouchEnd(x) {
                const d = x - this.touchX;
                if (Math.abs(d) > 50) { d < 0 ? this.next() : this.prev(); }
            },
        }" x-init="start()" @mouseenter="stop()" @mouseleave="start()"
            x-on:visibilitychange.document="document.hidden ? stop() : start()"
            @touchstart.passive="touchX = $event.changedTouches[0].clientX"
            @touchend="onTouchEnd($event.changedTouches[0].clientX)"
            :class="paused && 'hero-paused'"
            role="region" aria-roledescription="carousel"
            aria-label="{{ __('home.hero_aria_label', ['company' => app_setting('company_name', 'Equator Group')]) }}"
            class="relative flex h-[95vh] min-h-[700px] w-full items-center overflow-hidden bg-equator-dark">

            @foreach ($heroBanners as $i => $banner)
                <div x-show="active === {{ $i }}" x-transition:enter="transition-opacity ease-out duration-1000"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-1000 absolute inset-0"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 z-0">
                    @if ($banner->image)
                        <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                            width="1920" height="1080"
                            @if ($i === 0) loading="eager" fetchpriority="high" @else loading="lazy" fetchpriority="low" @endif
                            decoding="async" class="h-full w-full object-cover will-change-transform"
                            :class="active === {{ $i }} ? 'hero-kenburns' : 'scale-105'">
                    @else
                        <div class="h-full w-full bg-equator-dark"></div>
                    @endif
                    <div class="absolute inset-0 bg-equator-dark/30 mix-blend-multiply"></div>
                    <div
                        class="absolute inset-0 w-full bg-gradient-to-r from-equator-dark/95 via-equator-dark/70 to-transparent md:w-3/4">
                    </div>
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-equator-dark via-transparent to-transparent opacity-90">
                    </div>
                </div>
            @endforeach

            <div
                class="relative z-10 mx-auto flex h-full w-full max-w-7xl flex-col justify-center px-4 pt-16 sm:px-6 lg:px-8">
                @foreach ($heroBanners as $i => $banner)
                    <div x-show="active === {{ $i }}"
                        class="{{ $i === 0 ? '' : 'absolute top-1/2 -translate-y-1/2 left-4 sm:left-6 lg:left-8 right-4' }} max-w-3xl">
                        <div x-show="active === {{ $i }}" class="hero-reveal hero-reveal-1"
                            :class="active === {{ $i }} ? (manual ? 'is-manual' : 'is-auto') : ''">
                            <div class="mb-6 flex items-center gap-4">
                                <span class="h-px w-12 bg-equator-orange"></span>
                                <span class="text-xs font-bold uppercase tracking-[0.3em] text-equator-orange">
                                    {{ app_setting('company_name', 'Equator Group') }}
                                </span>
                            </div>
                            {{-- Only the first slide is the page <h1>; subsequent slides use
                                 <p> so the document has exactly one H1 (SEO + a11y heading order). --}}
                            <{{ $i === 0 ? 'h1' : 'p' }}
                                class="font-heading text-4xl font-light leading-[1.05] tracking-tight text-white [text-shadow:0_2px_40px_rgba(0,0,0,0.35)] sm:text-6xl lg:text-[5.25rem]">
                                {{ $banner->title }}
                            </{{ $i === 0 ? 'h1' : 'p' }}>
                        </div>
                        <div x-show="active === {{ $i }}" class="hero-reveal hero-reveal-2"
                            :class="active === {{ $i }} ? (manual ? 'is-manual' : 'is-auto') : ''">
                            @if ($banner->subtitle)
                                <p
                                    class="ml-1 mt-8 max-w-2xl border-l border-white/20 pl-6 text-lg font-light leading-relaxed text-slate-300 sm:text-xl">
                                    {{ $banner->subtitle }}
                                </p>
                            @endif
                            @php
                                $heroCtaText = $banner->button_text ?: __('common.contact_team');
                                $heroCtaUrl = $banner->button_text ? ($banner->button_link ?: '#') : route('contact');
                            @endphp
                            <div class="ml-1 mt-12">
                                <a href="{{ $heroCtaUrl }}"
                                    class="group inline-flex items-center gap-4 border border-white bg-transparent px-8 py-4 text-xs font-bold uppercase tracking-[0.2em] text-white transition duration-500 hover:border-equator-orange hover:bg-equator-orange">
                                    {{ $heroCtaText }}
                                    <span aria-hidden="true"
                                        class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($heroBanners->count() > 1)
                <div
                    class="absolute bottom-0 left-0 z-20 w-full border-t border-white/10 bg-equator-dark/30 backdrop-blur-md">
                    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-6">
                            <div class="w-6 text-xs font-bold tracking-widest text-equator-orange">
                                <span x-text="String(active + 1).padStart(2, '0')"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                @foreach ($heroBanners as $i => $b)
                                    <button @click="select({{ $i }})"
                                        aria-label="{{ __('home.slide_go_to', ['number' => $i + 1]) }}"
                                        class="group flex items-center justify-center py-4 focus:outline-none">
                                        <span
                                            class="block h-[2px] transition-[width,background-color] duration-700 ease-in-out"
                                            :class="active === {{ $i }} ? 'hero-dot hero-dot--active w-12 bg-white/20' :
                                                'w-4 bg-white/20 group-hover:bg-white/60'"></span>
                                    </button>
                                @endforeach
                            </div>
                            <div class="text-xs font-medium tracking-widest text-white/40">
                                <span x-text="String(total).padStart(2, '0')"></span>
                            </div>
                            <button @click="toggle()" :aria-label="paused ? '{{ __('home.slideshow_play') }}' : '{{ __('home.slideshow_pause') }}'"
                                class="flex h-7 w-7 items-center justify-center rounded-full border border-white/20 text-white/60 transition-colors hover:border-equator-orange hover:text-equator-orange">
                                <i class="bi text-[10px]" :class="paused ? 'bi-play-fill' : 'bi-pause-fill'"
                                    aria-hidden="true"></i>
                            </button>
                        </div>
                        <div
                            class="hidden items-center gap-4 text-[10px] font-bold uppercase tracking-[0.3em] text-white/40 md:flex">
                            <span>{{ __('home.scroll') }}</span>
                            <div class="relative h-8 w-px overflow-hidden bg-white/20">
                                <div class="absolute left-0 top-0 h-1/2 w-full hero-scroll-drift bg-equator-orange"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- ============================ KEY METRICS (annual-report on white) ============================ --}}
    {{-- $stats disusun di HomeController (metrik CMS + fallback). --}}
    <section class="border-t border-slate-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-px bg-slate-200/70 lg:grid-cols-4">
                @foreach ($stats as $i => $s)
                    @php
                        preg_match('/^\s*([\d,]+)(.*)$/u', (string) $s['value'], $mm);
                        $num = isset($mm[1]) && $mm[1] !== '' ? (int) str_replace(',', '', $mm[1]) : null;
                        $suffix = $mm[2] ?? '';
                    @endphp
                    <div class="group relative bg-white p-8 transition-colors duration-500 ease-out hover:bg-slate-50/70 lg:p-12"
                        x-data="{
                            n: 0,
                            target: {{ $num ?? 0 }},
                            shown: false,
                            done: false,
                            delay: {{ $i * 100 }},
                            reveal() {
                                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                                    this.shown = true;
                                    this.n = this.target;
                                    this.done = true;
                                    return;
                                }
                                setTimeout(() => { this.shown = true; }, this.delay);
                                setTimeout(() => this.count(), this.delay);
                            },
                            count() {
                                if (this.done || !this.target) return;
                                this.done = true;
                                const dur = 1800,
                                    start = performance.now();
                                const tick = (t) => {
                                    const p = Math.min((t - start) / dur, 1);
                                    this.n = Math.floor((1 - Math.pow(1 - p, 3)) * this.target);
                                    if (p < 1) requestAnimationFrame(tick);
                                    else this.n = this.target;
                                };
                                requestAnimationFrame(tick);
                            }
                        }" x-intersect.once="reveal()">

                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 w-px origin-top scale-y-0 bg-gradient-to-b from-equator-bright to-equator-light transition-transform duration-500 ease-out group-hover:scale-y-100"
                            aria-hidden="true"></span>

                        <div class="flex h-full min-h-[150px] flex-col justify-between transition-all duration-700 ease-out"
                            :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'">
                            <span
                                class="font-heading text-xs font-semibold tracking-[0.2em] text-slate-300">0{{ $i + 1 }}</span>
                            <div class="mt-8">
                                <div
                                    class="flex items-baseline font-heading text-4xl font-light leading-none tracking-tight text-equator-dark lg:text-5xl">
                                    @if (!is_null($num))
                                        <span x-text="n.toLocaleString()">0</span><span
                                            class="ml-0.5 font-normal text-equator-orange">{{ $suffix }}</span>
                                    @else
                                        {{ $s['value'] }}
                                    @endif
                                </div>
                                <div class="mt-4 h-px w-10 origin-left bg-gradient-to-r from-equator-bright to-equator-light transition-transform duration-700 ease-out"
                                    :class="shown ? 'scale-x-100' : 'scale-x-0'" aria-hidden="true"></div>
                                <div class="mt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                    {{ $s['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ FEATURED PROJECTS (cinematic tiles on white) ============================ --}}
    @if ($featuredProjects->isNotEmpty())
        <section class="border-t border-slate-100 bg-white py-24 sm:py-32 lg:py-40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div x-data="{ shown: false }" x-intersect.margin.-100px.once="shown = true"
                    class="flex flex-col gap-6 transition duration-700 ease-out motion-reduce:transition-none md:flex-row md:items-end md:justify-between"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <div class="max-w-2xl">
                        <div class="flex items-center gap-4">
                            <span class="h-px w-10 bg-equator-orange"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500">{{ __('home.projects_eyebrow') }}</span>
                        </div>
                        <h2
                            class="mt-5 font-heading text-3xl font-light leading-[1.15] tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                            {{ __('home.projects_heading') }}
                        </h2>
                    </div>
                    <a href="{{ route('projects.index') }}"
                        class="group inline-flex shrink-0 items-center gap-3 border-b border-slate-300 pb-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-900 transition-colors duration-300 hover:border-equator-orange hover:text-equator-bright">
                        <span>{{ __('home.projects_view_all') }}</span>
                        <span aria-hidden="true"
                            class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                    </a>
                </div>

                <div x-data="{ shown: false }" x-intersect.margin.-100px.once="shown = true"
                    class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProjects->take(6) as $i => $p)
                        @php
                            $sector = $p->services->first()?->name ?? ($p->country ?: __('home.projects_sector_fallback'));
                            $place = $p->location ?: $p->country;
                        @endphp
                        <div :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                            style="transition-delay: {{ $i * 90 }}ms;"
                            class="transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]">
                            <a href="{{ route('projects.show', $p->slug) }}"
                                class="group relative block h-[440px] w-full overflow-hidden bg-equator-dark shadow-sm transition-shadow duration-500 hover:shadow-[0_30px_60px_-30px_rgba(38,53,146,0.35)]">
                                @if ($p->featured_image)
                                    <img src="{{ asset('storage/' . $p->featured_image) }}" alt="{{ $p->name }}"
                                        loading="lazy" decoding="async"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1600ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-equator-dark via-equator-dark/45 to-equator-dark/5"
                                    aria-hidden="true"></div>

                                <div class="absolute inset-x-0 top-0 flex items-center justify-between p-6">
                                    @if ($p->status)
                                        @php
                                            $statusClass = match (strtolower($p->status)) {
                                                'completed'
                                                    => 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white',
                                                'on-going',
                                                'ongoing'
                                                    => 'bg-gradient-to-r from-equator-orange to-amber-500 text-white',
                                                default => 'bg-white/10 text-white',
                                            };
                                        @endphp

                                        <span
                                            class="{{ $statusClass }} rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.15em] shadow-lg">
                                            {{ $p->status }}
                                        </span>
                                    @endif
                                    <span
                                        class="font-heading text-sm font-semibold tracking-[0.15em] text-white">{{ sprintf('%02d', $i + 1) }}</span>
                                </div>

                                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-equator-orange">
                                        {{ $sector }}</p>
                                    <h3
                                        class="mt-3 font-heading text-xl font-normal leading-snug tracking-tight text-white">
                                        {{ $p->name }}</h3>
                                    <div class="mt-5 flex items-center justify-between border-t border-white/15 pt-4">
                                        <span
                                            class="truncate text-[12px] font-medium text-white/70">{{ $place ?: ($p->client_name ?: __('home.projects_client_fallback')) }}</span>
                                        <span aria-hidden="true"
                                            class="shrink-0 text-white/60 transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ SERVICES (cinematic capability tiles) ============================ --}}
    @if ($featuredServices->isNotEmpty())
        <section class="border-t border-slate-100 bg-equator-bg py-24 sm:py-32 lg:py-40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div x-data="{ shown: false }" x-intersect.margin.-100px.once="shown = true"
                    class="flex flex-col gap-6 transition duration-700 ease-out motion-reduce:transition-none md:flex-row md:items-end md:justify-between"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <div class="max-w-2xl">
                        <div class="flex items-center gap-4">
                            <span class="h-px w-10 bg-equator-orange"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500">{{ __('home.services_eyebrow') }}</span>
                        </div>
                        <h2
                            class="mt-5 font-heading text-3xl font-light leading-[1.15] tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                            {{ __('home.services_heading') }}
                        </h2>
                        <p class="mt-5 max-w-xl text-base font-normal leading-relaxed text-slate-600 lg:text-lg">
                            {{ __('home.services_intro') }}
                        </p>
                    </div>
                    <a href="{{ route('services.index') }}"
                        class="group inline-flex shrink-0 items-center gap-3 border-b border-slate-300 pb-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-900 transition-colors duration-300 hover:border-equator-orange hover:text-equator-bright">
                        <span>{{ __('home.services_view_all') }}</span>
                        <span aria-hidden="true"
                            class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                    </a>
                </div>

                <div x-data="{ shown: false }" x-intersect.margin.-100px.once="shown = true"
                    class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-2">
                    @foreach ($featuredServices->take(4) as $i => $s)
                        <div :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                            style="transition-delay: {{ $i * 90 }}ms;"
                            class="transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]">
                            <a href="{{ route('services.show', $s->slug) }}"
                                class="group relative block h-[420px] w-full overflow-hidden bg-equator-dark shadow-sm transition-shadow duration-500 hover:shadow-[0_30px_60px_-30px_rgba(38,53,146,0.35)]">
                                @if ($s->image)
                                    <img src="{{ asset('storage/' . $s->image) }}" alt="{{ $s->name }}"
                                        loading="lazy" decoding="async"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1600ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-equator-dark via-equator-dark/55 to-equator-dark/15"
                                    aria-hidden="true"></div>

                                <div class="absolute inset-x-0 top-0 flex items-center justify-between p-6 sm:p-7">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-white">{{ $s->category?->name ?? __('home.services_category_fallback') }}</span>
                                    <span
                                        class="font-heading text-sm font-semibold tracking-[0.15em] text-white">{{ sprintf('%02d', $i + 1) }}</span>
                                </div>

                                <div class="absolute inset-x-0 bottom-0 p-7 sm:p-9">
                                    <h3 class="font-heading text-2xl font-normal leading-snug tracking-tight text-white">
                                        {{ $s->name }}</h3>
                                    @if ($s->short_description || $s->description)
                                        <p
                                            class="mt-3 line-clamp-2 max-w-md text-sm font-light leading-relaxed text-slate-200">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($s->short_description ?: $s->description), 120) }}
                                        </p>
                                    @endif
                                    <div
                                        class="mt-6 inline-flex items-center gap-3 border-t border-white/15 pt-5 text-[11px] font-bold uppercase tracking-[0.18em] text-equator-orange">
                                        <span>{{ __('common.learn_more') }}</span>
                                        <span aria-hidden="true"
                                            class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ ABOUT (institutional — DB-driven, light) ============================ --}}
    @php
        $lookup = $aboutContents ?? collect();
        $aboutMain = $lookup->get('who_we_are') ?? $lookup->first();
        $aboutVision = $lookup->get('vision');
        $aboutMission = $lookup->get('mission');
        $aboutTitle = $aboutSection?->name ?? 'About ' . app_setting('company_name', 'Equator Group');
        $aboutImage = $aboutMain?->image;
        $aboutNarrative = $aboutMain?->content;
    @endphp

    <section class="border-t border-slate-100 bg-white py-24 sm:py-32 lg:py-40">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-16 lg:grid-cols-12 lg:gap-20">

                {{-- Foto institusional parallax (cinematic) --}}
                <div x-data="{ shown: false }" x-intersect.margin.-100px.once="shown = true"
                    class="transition duration-700 ease-out motion-reduce:transition-none lg:col-span-5"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                    <div class="relative aspect-[4/5] w-full overflow-hidden bg-equator-dark shadow-sm">
                        @if ($aboutImage)
                            <img src="{{ asset('storage/' . $aboutImage) }}" alt="" aria-hidden="true"
                                loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover">
                        @endif
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-equator-dark/40 to-transparent"
                            aria-hidden="true"></div>
                    </div>
                </div>

                {{-- Narasi + pilar dari database --}}
                <div x-data="{ shown: false }" x-intersect.margin.-100px.once="shown = true"
                    class="transition delay-100 duration-700 ease-out motion-reduce:transition-none lg:col-span-7"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">

                    <div class="flex items-center gap-4">
                        <span class="h-px w-10 bg-equator-orange"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500">{{ __('home.about_eyebrow') }}</span>
                    </div>

                    <h2
                        class="mt-6 font-heading text-3xl font-light leading-[1.15] tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        {{ $aboutTitle }}
                    </h2>

                    <p class="prose prose-lg prose-slate mt-7 max-w-none">
                        {!! $aboutNarrative !!}
                    </p>

                    @if ($aboutVision || $aboutMission)
                        <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2">

                            @if ($aboutVision)
                                <div class="border-t border-slate-200 pt-6">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-equator-orange">
                                            <i data-lucide="telescope" class="h-5 w-5" stroke-width="1.5"></i>
                                        </span>

                                        <h3 class="font-heading text-lg font-medium text-slate-900">
                                            {{ __('home.about_vision') }}
                                        </h3>
                                    </div>

                                    <div class="prose prose-sm mt-4 max-w-none text-slate-600">
                                        {!! $aboutVision->content !!}
                                    </div>
                                </div>
                            @endif

                            @if ($aboutMission)
                                <div class="border-t border-slate-200 pt-6">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-equator-bright">
                                            <i data-lucide="target" class="h-5 w-5" stroke-width="1.5"></i>
                                        </span>

                                        <h3 class="font-heading text-lg font-medium text-slate-900">
                                            {{ __('home.about_mission') }}
                                        </h3>
                                    </div>

                                    <div class="prose prose-sm mt-4 max-w-none text-slate-600">
                                        {!! $aboutMission->content !!}
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endif

                    <div class="mt-12 flex flex-col gap-6 sm:flex-row sm:items-center">
                        <a href="{{ route('about') }}"
                            class="group inline-flex items-center gap-3 bg-equator-dark px-8 py-4 text-[13px] font-bold uppercase tracking-[0.18em] text-white transition-colors duration-300 hover:bg-equator-orange">
                            {{ __('home.about_cta') }}
                            <span aria-hidden="true"
                                class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                        </a>
                        @if (!empty($companyProfilePath))
                            <a href="{{ asset('storage/' . $companyProfilePath) }}" target="_blank" rel="noopener"
                                class="group inline-flex items-center gap-3 border border-slate-200 bg-white px-6 py-4 text-[13px] font-semibold uppercase tracking-[0.16em] text-slate-700 transition-all duration-300 hover:border-equator-orange hover:text-equator-orange hover:shadow-lg hover:shadow-equator-orange/10">

                                <x-icon :name="'file-text'" class="h-4 w-4 text-slate-700" />

                                <span>{{ __('home.company_profile') }}</span>

                                <x-icon name="arrow-up-right"
                                    class="h-4 w-4 transition-transform duration-300 group-hover:-translate-y-1 group-hover:translate-x-1" />
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ============================ PARTNERS (light logo wall) ============================ --}}
    @if ($partners->isNotEmpty())
        <section class="border-t border-slate-100 bg-equator-bg py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div x-data="{ shown: false }" x-intersect.margin.-80px.once="shown = true"
                    class="mx-auto flex max-w-xl flex-col items-center text-center transition duration-700 ease-out motion-reduce:transition-none"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <div class="flex items-center gap-3">
                        <span class="h-px w-8 bg-equator-orange"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500">{{ __('home.partners_eyebrow') }}</span>
                        <span class="h-px w-8 bg-equator-orange"></span>
                    </div>
                    <h2 class="mt-5 font-heading text-2xl font-light tracking-tight text-slate-900 lg:text-3xl">
                        {{ __('home.partners_heading') }}</h2>
                </div>

                <div x-data="{ shown: false }" x-intersect.margin.-60px.once="shown = true"
                    class="mt-14 flex flex-wrap items-center justify-center gap-x-12 gap-y-10 transition duration-700 ease-out motion-reduce:transition-none lg:gap-x-16"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    @foreach ($partners as $partner)
                        <div class="flex items-center justify-center">
                            @if ($partner->website)
                                <a href="{{ $partner->website }}" target="_blank" rel="noopener"
                                    title="{{ $partner->name }}" class="flex items-center justify-center">
                            @endif
                            @if ($partner->logo)
                                <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}"
                                    loading="lazy" decoding="async"
                                    class="h-9 w-auto max-w-[150px] object-contain opacity-50 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0 lg:h-10">
                            @else
                                <span
                                    class="font-heading text-base font-medium text-slate-400 transition-colors duration-300 hover:text-slate-900">{{ $partner->name }}</span>
                            @endif
                            @if ($partner->website)
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ TRUSTED CREDENTIALS ============================ --}}
    @if (($featuredCredentials ?? collect())->isNotEmpty())
        <section class="border-t border-slate-100 bg-white py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div x-data="{ shown: false }" x-intersect.margin.-80px.once="shown = true"
                    class="mx-auto flex max-w-xl flex-col items-center text-center transition duration-700 ease-out motion-reduce:transition-none"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <div class="flex items-center gap-3">
                        <span class="h-px w-8 bg-equator-orange"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500">{{ __('credentials.home_title') }}</span>
                        <span class="h-px w-8 bg-equator-orange"></span>
                    </div>
                    <h2 class="mt-5 font-heading text-2xl font-light tracking-tight text-slate-900 lg:text-3xl">
                        {{ __('credentials.home_subtitle') }}
                    </h2>
                </div>

                <div x-data="{ shown: false }" x-intersect.margin.-60px.once="shown = true"
                    class="mt-14 flex flex-wrap justify-center gap-4 transition duration-700 ease-out motion-reduce:transition-none"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    @foreach ($featuredCredentials as $cred)
                        <a href="{{ route('about') }}#credentials" title="{{ $cred->title }}"
                            class="group flex aspect-square w-36 flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-center transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_18px_40px_-22px_rgba(38,53,146,0.3)]">
                            @if ($cred->image)
                                <img src="{{ asset('storage/' . $cred->image) }}" alt="{{ $cred->title }}"
                                    loading="lazy" decoding="async"
                                    class="h-14 w-auto max-w-[80%] object-contain opacity-80 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                            @else
                                <x-icon :name="config('credentials.categories.' . $cred->category . '.icon', 'file-badge')"
                                    class="h-10 w-10 text-equator-dark/70" stroke-width="1.25" />
                            @endif
                            <span class="line-clamp-2 text-[11px] font-semibold leading-tight text-slate-500 group-hover:text-equator-dark">
                                {{ $cred->title }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('about') }}#credentials"
                        class="group inline-flex items-center gap-3 border-b border-slate-300 pb-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-900 transition-colors duration-300 hover:border-equator-orange hover:text-equator-bright">
                        <span>{{ __('credentials.view_all') }}</span>
                        <span aria-hidden="true" class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ FINAL CTA (cinematic full-bleed close) ============================ --}}
    @php
        $ctaImg = optional($featuredProjects->first())->featured_image ?? optional($heroBanners->first())->image;
    @endphp
    <section class="relative isolate overflow-hidden bg-equator-dark">
        @if ($ctaImg)
            <div class="pointer-events-none absolute inset-0 bg-cover bg-center"
                style="background-image: url('{{ asset('storage/' . $ctaImg) }}');" aria-hidden="true"></div>
        @endif
        <div class="pointer-events-none absolute inset-0 bg-equator-dark/85" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-equator-dark via-equator-dark/75 to-equator-dark/90"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-0 h-80 w-[48rem] -translate-x-1/2 rounded-full bg-equator-orange/15 blur-[140px]"
            aria-hidden="true"></div>

        <div x-data="{ shown: false }" x-intersect.margin.-80px.once="shown = true"
            class="relative z-10 mx-auto max-w-4xl px-4 py-28 text-center transition duration-700 ease-out motion-reduce:transition-none sm:px-6 lg:py-36"
            :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
            <div class="flex items-center justify-center gap-4">
                <span class="h-px w-8 bg-equator-orange"></span>
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-equator-light">{{ __('home.cta_eyebrow') }}</span>
                <span class="h-px w-8 bg-equator-orange"></span>
            </div>
            <h2
                class="mt-8 font-heading text-4xl font-light leading-[1.1] tracking-tight text-white md:text-5xl lg:text-6xl">
                {{ __('home.cta_heading') }}
            </h2>
            <p class="mx-auto mt-8 max-w-2xl text-lg font-light leading-relaxed text-slate-300">
                {{ __('home.cta_body') }}
            </p>
            <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="group inline-flex w-full items-center justify-center gap-3 bg-equator-orange px-9 py-5 text-xs font-bold uppercase tracking-[0.2em] text-white transition duration-300 ease-out hover:bg-white hover:text-equator-dark sm:w-auto">
                    {{ __('common.contact_team') }}
                    <span aria-hidden="true"
                        class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                </a>
                @if (!empty($companyProfilePath))
                    <a href="{{ asset('storage/' . $companyProfilePath) }}" target="_blank" rel="noopener"
                        class="group inline-flex w-full items-center justify-center gap-3 border border-white/25 px-9 py-5 text-xs font-bold uppercase tracking-[0.2em] text-white transition duration-300 ease-out hover:border-white hover:bg-white/5 sm:w-auto">
                        {{ __('home.company_profile') }}
                    </a>
                @endif
            </div>
        </div>
    </section>

@endsection
