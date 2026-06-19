@extends('layouts.public')

@section('title', 'About Us — ' . app_setting('company_name', 'Equator Group'))

@section('meta_description', 'Learn about ' . app_setting('company_name', 'Equator Group') . ' — ' . app_setting('tagline', 'a social and environmental advisory firm across sustainability, ESG, resilience and development.'))

@push('head')
    @php
        $aboutOrg = [
            '@type' => 'Organization',
            'name' => app_setting('company_name', 'Equator Group'),
            'url' => url('/'),
        ];
        if (app_setting('logo')) {
            $aboutOrg['logo'] = asset('storage/' . app_setting('logo'));
        }
        if ($histories->first()?->year) {
            $aboutOrg['foundingDate'] = (string) $histories->first()->year;
        }
        $aboutJsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                ['@type' => 'AboutPage', 'url' => url()->current(), 'name' => 'About — ' . app_setting('company_name', 'Equator Group')],
                $aboutOrg,
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'About', 'item' => url()->current()],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($aboutJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    {{-- HERO --}}
    @include('public.partials.page-hero', [
        'title' => 'About ' . app_setting('company_name', 'Equator Group'),
        'subtitle' => app_setting('tagline', 'Safeguarding people, planet, prosperity, and principles.'),
    ])

    {{-- ============================ 01 — EDITORIAL STORY (CMS SECTIONS) ============================ --}}
    <section class="bg-white py-24 sm:py-28 lg:py-32">

        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            @forelse ($sections as $section)
                @php
                    // Ambil berdasarkan field "key" yang STABIL (ada unique [section_id, key]),
                    // bukan berdasarkan posisi/index — supaya tahan terhadap perubahan urutan data.
                    $vision = $section->contents->firstWhere('key', 'vision');
                    $mission = $section->contents->firstWhere('key', 'mission');

                    // Konten "lead" = konten pertama yang BUKAN vision/mission.
                    // (key lead bersifat spesifik per-section, mis. "safeguarding_sustainable_future".)
                    $companyContent = $section->contents
                        ->reject(fn($c) => in_array($c->key, ['vision', 'mission'], true))
                        ->first();

                    // Apakah section ini punya narasi lead? Menentukan layout 2 kolom vs 1 kolom.
                    $hasLead = filled($companyContent?->content);
                @endphp

                <div @class(['border-t border-slate-200 pt-24' => !$loop->first])>

                    {{-- Header + Lead — anchored portrait layout --}}
                    @php $hasImage = $companyContent && filled($companyContent->image); @endphp

                    <div data-story-section>

                        @if ($hasLead && $hasImage)
                            {{-- LAYOUT A: Content left (7) + Image right (5) --}}
                            <div class="grid gap-x-16 gap-y-12 lg:grid-cols-12 lg:items-start">

                                <div class="story-content lg:col-span-7">
                                    <div class="story-eyebrow flex items-center gap-3">
                                        <span class="h-px w-8 bg-equator-orange"></span>
                                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">About
                                            Us</span>
                                    </div>
                                    <h2
                                        class="story-heading mt-5 font-heading text-3xl font-semibold tracking-tight text-equator-dark sm:text-4xl lg:text-[2.5rem] lg:leading-[1.2]">
                                        {{ $section->name }}
                                    </h2>
                                    <div class="story-rule mt-8 h-px w-10 origin-left bg-gradient-to-r from-equator-orange to-slate-200"
                                        aria-hidden="true"></div>
                                    <div
                                        class="story-narrative prose mt-8 max-w-none prose-headings:font-heading prose-headings:text-equator-dark prose-p:text-base prose-p:leading-8 prose-p:text-slate-600 prose-a:text-equator-bright prose-a:no-underline hover:prose-a:underline prose-strong:text-equator-dark sm:prose-p:text-[1.0625rem]">
                                        {!! $companyContent->content !!}
                                    </div>
                                </div>

                                <div class="story-image group lg:col-span-5 lg:pt-2">
                                    <figure
                                        class="overflow-hidden rounded-2xl shadow-[0_12px_40px_-12px_rgba(15,23,42,0.14)]">
                                        <img src="{{ asset('storage/' . $companyContent->image) }}"
                                            alt="{{ $section->name }}" loading="lazy" decoding="async"
                                            class="aspect-[3/4] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.02]">
                                    </figure>
                                </div>

                            </div>
                        @elseif ($hasLead)
                            {{-- LAYOUT B: No image — heading left (5) + narrative right (7) --}}
                            <div class="grid gap-x-16 gap-y-10 lg:grid-cols-12 lg:items-start">

                                <div class="story-content lg:col-span-5">
                                    <div class="story-eyebrow flex items-center gap-3">
                                        <span class="h-px w-8 bg-equator-orange"></span>
                                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">About
                                            Us</span>
                                    </div>
                                    <h2
                                        class="story-heading mt-5 font-heading text-3xl font-semibold tracking-tight text-equator-dark sm:text-4xl lg:text-5xl">
                                        {{ $section->name }}
                                    </h2>
                                    <div class="story-rule mt-8 h-px w-10 origin-left bg-gradient-to-r from-equator-orange to-slate-200"
                                        aria-hidden="true"></div>
                                </div>

                                <div class="story-narrative lg:col-span-7">
                                    <div
                                        class="prose max-w-none prose-headings:font-heading prose-headings:text-equator-dark prose-p:text-base prose-p:leading-8 prose-p:text-slate-600 prose-a:text-equator-bright prose-a:no-underline hover:prose-a:underline prose-strong:text-equator-dark sm:prose-p:text-[1.0625rem]">
                                        {!! $companyContent->content !!}
                                    </div>
                                </div>

                            </div>
                        @else
                            {{-- LAYOUT C: No lead — heading only (before vision/mission cards) --}}
                            <div class="max-w-3xl">
                                <div class="story-eyebrow flex items-center gap-3">
                                    <span class="h-px w-8 bg-equator-orange"></span>
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">About
                                        Us</span>
                                </div>
                                <h2
                                    class="story-heading mt-5 font-heading text-3xl font-semibold tracking-tight text-equator-dark sm:text-4xl lg:text-5xl">
                                    {{ $section->name }}
                                </h2>
                                @if ($hasImage)
                                    <figure class="story-image mt-10 overflow-hidden rounded-2xl border border-slate-200">
                                        <img src="{{ asset('storage/' . $companyContent->image) }}"
                                            alt="{{ $section->name }}" loading="lazy" decoding="async"
                                            class="aspect-[16/9] w-full object-cover">
                                    </figure>
                                @endif
                            </div>
                        @endif

                    </div>

                    @if ($vision || $mission)
                        <div class="mt-16 grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
                            @if ($vision)
                                <div
                                    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
                                    <div class="flex items-center gap-4">
                                        <span
                                            class="font-heading text-sm font-semibold tracking-[0.2em] text-equator-orange">
                                            01
                                        </span>
                                        <span class="h-px w-6 bg-slate-200"></span>
                                        <i data-lucide="eye" class="h-5 w-5 text-slate-400"></i>
                                    </div>

                                    <h3
                                        class="mt-6 font-heading text-xl font-semibold tracking-tight text-equator-dark sm:text-2xl">
                                        {{ $vision->title ?? 'Vision' }}
                                    </h3>

                                    <div
                                        class="prose mt-4 max-w-none flex-1 prose-p:text-base prose-p:leading-8 prose-p:text-slate-600 prose-li:text-slate-600">
                                        {!! $vision->content !!}
                                    </div>
                                </div>
                            @endif

                            @if ($mission)
                                <div
                                    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
                                    <div class="flex items-center gap-4">
                                        <span
                                            class="font-heading text-sm font-semibold tracking-[0.2em] text-equator-orange">
                                            02
                                        </span>
                                        <span class="h-px w-6 bg-slate-200"></span>
                                        <i data-lucide="target" class="h-5 w-5 text-slate-400"></i>
                                    </div>

                                    <h3
                                        class="mt-6 font-heading text-xl font-semibold tracking-tight text-equator-dark sm:text-2xl">
                                        {{ $mission->title ?? 'Mission' }}
                                    </h3>

                                    <div
                                        class="prose mt-4 max-w-none flex-1 prose-p:text-base prose-p:leading-8 prose-p:text-slate-600 prose-li:text-slate-600">
                                        {!! $mission->content !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

            @empty

                <div class="max-w-3xl">
                    <div class="flex items-center gap-3">
                        <span class="h-px w-8 bg-equator-orange"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">About Us</span>
                    </div>
                    <h2 class="mt-6 font-heading text-3xl font-semibold tracking-tight text-equator-dark sm:text-4xl">
                        {{ app_setting('company_name', 'Equator Group') }}
                    </h2>
                    <p class="mt-6 text-base leading-8 text-slate-600 sm:text-[1.0625rem]">
                        A social and environmental advisory firm helping organizations manage risk and compliance
                        across multiple industries and regions.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- Story section entrance animations --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (prefersReduced) return;

                document.querySelectorAll('[data-story-section]').forEach(section => {
                    const eyebrow = section.querySelector('.story-eyebrow');
                    const heading = section.querySelector('.story-heading');
                    const rule = section.querySelector('.story-rule');
                    const narrative = section.querySelector('.story-narrative');
                    const image = section.querySelector('.story-image');

                    // [el, initial transform, reveal delay ms]
                    const reveals = [
                        [eyebrow, 'translateX(-10px)', 0],
                        [heading, 'translateY(12px)', 80],
                        [narrative, 'translateY(8px)', 200],
                        [image, 'translateX(16px)', 60],
                    ];

                    reveals.forEach(([el, transform]) => {
                        if (!el) return;
                        el.style.opacity = '0';
                        el.style.transform = transform;
                        el.style.transition =
                            'opacity 750ms cubic-bezier(0.22,1,0.36,1), transform 750ms cubic-bezier(0.22,1,0.36,1)';
                    });

                    if (rule) {
                        rule.style.transform = 'scaleX(0)';
                        rule.style.transformOrigin = 'left';
                        rule.style.transition = 'transform 600ms cubic-bezier(0.22,1,0.36,1)';
                    }

                    const io = new IntersectionObserver(entries => {
                        if (!entries[0].isIntersecting) return;
                        reveals.forEach(([el, , delay]) => {
                            if (!el) return;
                            setTimeout(() => {
                                el.style.opacity = '1';
                                el.style.transform = 'none';
                            }, delay);
                        });
                        if (rule) setTimeout(() => {
                            rule.style.transform = 'scaleX(1)';
                        }, 180);
                        io.disconnect();
                    }, {
                        threshold: 0.1
                    });

                    io.observe(section);
                });
            });
        </script>

    </section>

    {{-- ============================ 02 — CORE VALUES ============================ --}}
    @if ($coreValues->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-24 sm:py-28">

            <div class="mx-auto max-w-7xl px-6 lg:px-8">

                {{-- Header — editorial split --}}
                <div class="grid gap-x-10 gap-y-5 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7">
                        <div class="flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Our Values</span>
                        </div>
                        <h2
                            class="mt-5 font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl lg:text-4xl">
                            Principles That Shape Every Decision
                        </h2>
                    </div>
                    <div class="lg:col-span-5">
                        <p class="text-[0.9375rem] leading-7 text-slate-600 sm:text-base">
                            These values define how we work with clients, partners, communities, and one another.
                        </p>
                    </div>
                </div>

                {{-- Values Grid --}}
                <div class="mt-16 grid grid-cols-1 items-stretch gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($coreValues as $loopIndex => $value)
                        {{--
                        CINEMATIC CARD MAIN VESSEL
                        - Scroll Entrance: Menggunakan data-attributes untuk Intersection Observer (Scroll Reveal).
                        - Breathing Interaction: Dikendalikan lewat sinkronisasi durasi 800ms dan custom bezier.
                    --}}
                        <div data-animate-scroll style="--stagger-delay: {{ $loopIndex * 150 }}ms;"
                            class="value-card group relative flex h-full translate-y-8 flex-col overflow-hidden rounded-xl border border-slate-200/60 bg-white p-8 opacity-0 shadow-[0_4px_12px_-1px_rgba(15,23,42,0.06),0_1px_3px_0px_rgba(15,23,42,0.04)] transition-all duration-[800ms] ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-2 hover:scale-[1.008] hover:border-slate-300/80 hover:shadow-[0_24px_48px_-12px_rgba(15,23,42,0.18),0_12px_24px_-8px_rgba(15,23,42,0.08),0_0_0_1px_rgba(15,23,42,0.04)] motion-reduce:transition-none motion-reduce:hover:translate-y-0 motion-reduce:hover:scale-100">

                            {{-- Surface Dynamics: Responsif Terhadap Posisi Kursor (Ambient Light Tracker) --}}
                            <div class="pointer-events-none absolute inset-0 z-0 opacity-0 transition-opacity duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:opacity-100 motion-reduce:hidden"
                                style="background: radial-gradient(circle 320px at var(--mouse-x, 0px) var(--mouse-y, 0px), rgba(248,250,252,0.8) 0%, rgba(255,255,255,0) 100%);">
                            </div>

                            {{-- Soft Gradient Overlay: Refleksi Cahaya Jatuh Yang Bergerak Lambat Dari Sisi Atas --}}
                            <div
                                class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-br from-slate-50/0 via-slate-50/0 to-slate-50/20 opacity-100 transition-all duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105 motion-reduce:hidden">
                            </div>

                            {{-- INTERNAL LAYERING CONTENT --}}
                            <div class="relative z-10 space-y-6">

                                {{-- Icon: Elemen Paling Hidup dengan Soft Glow & Gerakan Parallax Lebih Jauh (-translate-y-1.5) --}}
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200/80 bg-slate-50 text-slate-600 shadow-sm transition-all duration-[850ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-1.5 group-hover:border-slate-300 group-hover:bg-white group-hover:text-slate-900 group-hover:shadow-[0_8px_20px_-4px_rgba(15,23,42,0.04),0_0_12px_1px_rgba(15,23,42,0.01)] motion-reduce:transition-none motion-reduce:group-hover:translate-y-0">
                                    <x-icon :name="$value->icon ?: 'shield-check'" class="h-5 w-5 stroke-[1.4]" />
                                </div>

                                {{-- Typography Wrapper --}}
                                <div class="space-y-3">
                                    {{-- Title: Micro-translation (-translate-y-[2px]) dan Transisi Opacity Halus --}}
                                    <h3
                                        class="font-heading text-lg font-medium tracking-tight text-slate-900 opacity-[0.92] transition-all duration-[700ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-[2px] group-hover:text-slate-900 group-hover:opacity-100 motion-reduce:transition-none motion-reduce:group-hover:translate-y-0">
                                        {{ $value->title }}
                                    </h3>

                                    {{-- Description: Bergerak Lebih Sedikit (-translate-y-[1px]) untuk Efek Parallax Kedalaman --}}
                                    <div
                                        class="prose prose-slate max-w-none text-slate-500 opacity-[0.88] transition-all duration-[750ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-[1px] group-hover:text-slate-600 group-hover:opacity-100 prose-p:text-[0.9rem] prose-p:font-normal prose-p:leading-relaxed motion-reduce:transition-none motion-reduce:group-hover:translate-y-0">
                                        {!! $value->description !!}
                                    </div>
                                </div>

                            </div>

                            {{-- Subtle Border Accent Lines: Penanda Elemen Premium Hasil Crafted Manual --}}
                            <div
                                class="pointer-events-none absolute bottom-0 left-0 h-[1.5px] w-0 bg-gradient-to-r from-slate-200 via-slate-300 to-transparent transition-all duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:w-full motion-reduce:hidden">
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>

        {{-- INTERACTION ENGINE: Menghidupkan Surface Dynamics & Scroll Entrance Berbasis Performa Tinggi --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // 1. Dynamic Surface Tracker (Arah Pointer Gerakan Cahaya)
                const cards = document.querySelectorAll('.value-card');
                cards.forEach(card => {
                    card.addEventListener('mousemove', e => {
                        const rect = card.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        card.style.setProperty('--mouse-x', `${x}px`);
                        card.style.setProperty('--mouse-y', `${y}px`);
                    });
                });

                // 2. High-Performance Scroll Entrance Animation (Fade-in, Translate & Stagger)
                const animationOptions = {
                    root: null,
                    threshold: 0.1,
                    rootMargin: "0px 0px -50px 0px"
                };

                const scrollObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const target = entry.target;
                            const delay = target.style.getPropertyValue('--stagger-delay') || '0ms';

                            setTimeout(() => {
                                // Mengaktifkan kelas translasi & opacity via Tailwind utility classes
                                target.classList.remove('opacity-0', 'translate-y-8');
                                target.classList.add('opacity-100', 'translate-y-0');
                            }, parseInt(delay));

                            // Hentikan pemantauan setelah animasi dijalankan sekali (Sesuai instruksi)
                            observer.unobserve(target);
                        }
                    });
                }, animationOptions);

                document.querySelectorAll('[data-animate-scroll]').forEach(el => {
                    // Pastikan menghormati prefers-reduced-motion dari user system
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        el.classList.remove('opacity-0', 'translate-y-8');
                        el.classList.add('opacity-100', 'translate-y-0');
                    } else {
                        scrollObserver.observe(el);
                    }
                });
            });
        </script>
    @endif

    {{-- ============================ 03 — OUR JOURNEY (TIMELINE) ============================ --}}
    @if ($histories->isNotEmpty())
        <section class="overflow-hidden border-t border-slate-200 bg-white py-24 sm:py-28 lg:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">

                {{-- Section Header — editorial split (matches other sections) --}}
                <div class="grid gap-x-10 gap-y-5 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7">
                        <div class="flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Our Journey</span>
                        </div>
                        <h2
                            class="mt-5 font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl lg:text-4xl">
                            Milestones That Define Our Story
                        </h2>
                    </div>
                    <div class="lg:col-span-5">
                        <p class="text-[0.9375rem] leading-7 text-slate-600 sm:text-base">
                            A record of growth, commitment, and impact — chapter by chapter.
                        </p>
                    </div>
                </div>

                {{-- Timeline body --}}
                <div class="relative mt-20" id="journey-body">

                    {{-- Track: static background line --}}
                    <div class="absolute bottom-0 left-3 top-0 w-px bg-slate-100" aria-hidden="true"></div>

                    {{-- Track: animated fill that grows on scroll --}}
                    <div id="journey-progress-fill"
                        class="absolute left-3 top-0 w-px origin-top bg-gradient-to-b from-equator-orange to-equator-bright"
                        style="height: 0%;" aria-hidden="true">
                    </div>

                    {{-- Chapters --}}
                    @foreach ($histories as $mIndex => $history)
                        <div class="journey-chapter group relative pb-20 pl-10 last:pb-0 sm:pl-16"
                            data-delay="{{ $mIndex * 100 }}">

                            {{-- Milestone dot — centered on the track (track at left:12px, dot w-3=12px → left:6px=left-1.5) --}}
                            <div class="absolute left-1.5 top-1 flex h-3 w-3 items-center justify-center rounded-full border-2 border-slate-200 bg-white transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-125 group-hover:border-equator-orange"
                                aria-hidden="true">
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-equator-orange opacity-0 transition-opacity duration-500 group-hover:opacity-100"></span>
                            </div>

                            {{-- Ghost year — large watermark behind content --}}
                            <div class="pointer-events-none absolute right-0 top-0 select-none font-heading text-[4rem] font-bold leading-none tracking-tight text-slate-100 transition-colors duration-700 group-hover:text-slate-200/60 sm:text-[6.5rem] lg:text-[9rem]"
                                aria-hidden="true">
                                {{ $history->year }}
                            </div>

                            {{-- Content — relative so it paints above the absolute ghost year --}}
                            <div class="relative max-w-2xl">

                                {{-- Chapter meta row --}}
                                <div class="flex items-center gap-3">
                                    <span class="font-heading text-xs font-bold tracking-[0.25em] text-equator-orange">
                                        {{ str_pad($mIndex + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="h-px w-5 bg-slate-200" aria-hidden="true"></span>
                                    <time class="font-mono text-xs font-semibold tracking-wider text-slate-400"
                                        datetime="{{ $history->year }}">{{ $history->year }}</time>
                                </div>

                                {{-- Milestone title --}}
                                <h3
                                    class="mt-4 font-heading text-xl font-semibold tracking-tight text-equator-dark sm:text-2xl lg:text-[1.625rem]">
                                    {{ $history->title }}
                                </h3>

                                {{-- Description --}}
                                <div
                                    class="prose prose-slate mt-3 max-w-none text-slate-600 prose-p:text-[0.9375rem] prose-p:leading-7">
                                    {!! $history->description !!}
                                </div>

                                {{-- Milestone image (optional) --}}
                                @if ($history->image)
                                    <figure class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                                        <img src="{{ asset('storage/' . $history->image) }}"
                                            alt="{{ $history->title }}" loading="lazy" decoding="async"
                                            class="aspect-[16/9] w-full object-cover">
                                    </figure>
                                @endif

                                {{-- Chapter divider (between chapters, not after last) --}}
                                @unless ($loop->last)
                                    <div class="mt-10 h-px bg-gradient-to-r from-slate-200 to-transparent" aria-hidden="true">
                                    </div>
                                @endunless

                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const chapters = document.querySelectorAll('.journey-chapter');

                // 1. Scroll entrance: fade + slide-up with stagger
                if (!prefersReduced) {
                    chapters.forEach(el => {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(20px)';
                        el.style.transition =
                            'opacity 700ms cubic-bezier(0.22,1,0.36,1), transform 700ms cubic-bezier(0.22,1,0.36,1)';
                    });

                    const io = new IntersectionObserver(entries => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const el = entry.target;
                                const delay = parseInt(el.dataset.delay || '0');
                                setTimeout(() => {
                                    el.style.opacity = '1';
                                    el.style.transform = 'translateY(0)';
                                }, delay);
                                io.unobserve(el);
                            }
                        });
                    }, {
                        threshold: 0.08,
                        rootMargin: '0px 0px -30px 0px'
                    });

                    chapters.forEach(el => io.observe(el));
                }

                // 2. Progress line: fills as user scrolls through the section
                const journeyBody = document.getElementById('journey-body');
                const progressFill = document.getElementById('journey-progress-fill');
                if (!prefersReduced && journeyBody && progressFill) {
                    const tick = () => {
                        const r = journeyBody.getBoundingClientRect();
                        const p = Math.max(0, Math.min(1,
                            (window.innerHeight - r.top) / (r.height + window.innerHeight * 0.15)
                        ));
                        progressFill.style.height = (p * 100) + '%';
                    };
                    window.addEventListener('scroll', tick, {
                        passive: true
                    });
                    tick();
                }
            });
        </script>
    @endif

    {{-- ============================ 04 — OUR TEAM ============================ --}}
    @if ($teams->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-24 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">

                {{-- Header — editorial split --}}
                <div class="grid gap-x-10 gap-y-5 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7">
                        <div class="flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Our People</span>
                        </div>
                        <h2
                            class="mt-5 font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl lg:text-4xl">
                            The People Behind Our Work
                        </h2>
                    </div>
                    <div class="lg:col-span-5">
                        <p class="text-[0.9375rem] leading-7 text-slate-600 sm:text-base">
                            A team of specialists committed to delivering responsible, high-impact solutions.
                        </p>
                    </div>
                </div>

                {{-- Team Grid — optimised for ~15 members (5 per row desktop) --}}
                <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($teams as $tIndex => $member)
                        <div class="team-member group overflow-hidden rounded-xl bg-white shadow-[0_2px_12px_-2px_rgba(15,23,42,0.08)] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.16)]"
                            data-delay="{{ min($tIndex * 50, 300) }}">

                            {{-- Photo --}}
                            <div class="aspect-[3/4] overflow-hidden bg-slate-100">
                                @if ($member->photo)
                                    <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}"
                                        loading="lazy" decoding="async"
                                        class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-equator-darker">
                                        <span class="font-heading text-3xl font-semibold text-white/[0.15]">
                                            {{ \Illuminate\Support\Str::upper(mb_substr($member->name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-4">
                                <p class="text-[0.6rem] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ $member->position }}
                                </p>
                                <h3 class="mt-1 font-heading text-sm font-semibold leading-snug text-equator-dark">
                                    {{ $member->name }}
                                </h3>

                                @if ($member->linkedin_url || $member->email)
                                    <div class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3">
                                        @if ($member->linkedin_url)
                                            <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener"
                                                aria-label="LinkedIn — {{ $member->name }}"
                                                class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-slate-400 transition-colors duration-200 hover:border-equator-dark hover:bg-equator-dark hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-dark/30">
                                                <i class="bi bi-linkedin text-[0.65rem]"></i>
                                            </a>
                                        @endif
                                        @if ($member->email)
                                            <a href="mailto:{{ $member->email }}"
                                                aria-label="Email — {{ $member->name }}"
                                                class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-slate-400 transition-colors duration-200 hover:border-equator-dark hover:bg-equator-dark hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-dark/30">
                                                <i class="bi bi-envelope text-[0.65rem]"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const members = document.querySelectorAll('.team-member');

                if (!prefersReduced) {
                    members.forEach(el => {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(16px)';
                        el.style.transition =
                            'opacity 600ms cubic-bezier(0.22,1,0.36,1), transform 600ms cubic-bezier(0.22,1,0.36,1)';
                    });

                    const io = new IntersectionObserver(entries => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const el = entry.target;
                                const delay = parseInt(el.dataset.delay || '0');
                                setTimeout(() => {
                                    el.style.opacity = '1';
                                    el.style.transform = 'translateY(0)';
                                }, delay);
                                io.unobserve(el);
                            }
                        });
                    }, {
                        threshold: 0.1
                    });

                    members.forEach(el => io.observe(el));
                }
            });
        </script>
    @endif

    {{-- ============================ 05 — CLOSING (CONTACT + COMPANY PROFILE) ============================ --}}
    @php $hasProfile = !empty($companyProfile) && $companyProfile->file; @endphp
    <section class="closing-cta-section relative overflow-hidden bg-equator-dark py-28 sm:py-36">

        {{-- Atmospheric depth layers — equator-dark bg needs lighter/warmer light sources (not same-hue blue) --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -bottom-28 -left-28 h-[32rem] w-[32rem] rounded-full bg-white/[0.06] blur-[88px]"></div>
            <div class="absolute -right-20 top-0 h-72 w-72 rounded-full bg-equator-orange/[0.05] blur-[72px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_55%_45%_at_8%_95%,rgba(255,255,255,0.05),transparent_60%)]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">

            {{-- Top separator rule --}}
            <div class="closing-reveal mb-20 h-px bg-gradient-to-r from-transparent via-white/[0.18] to-transparent"
                data-delay="0"></div>

            <div class="{{ $hasProfile ? 'lg:grid-cols-12 lg:gap-x-16' : '' }} grid items-start gap-y-16">

                {{-- ── Left: Narrative + Primary CTA ── --}}
                <div class="{{ $hasProfile ? 'lg:col-span-7' : 'lg:max-w-3xl' }}">

                    <div class="closing-reveal flex items-center gap-3" data-delay="50"
                        style="transform: translateX(-8px);">
                        <span class="h-px w-8 bg-equator-orange"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">Begin the
                            conversation</span>
                    </div>

                    <h2 class="closing-reveal mt-6 font-heading text-4xl font-semibold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-[3.25rem]"
                        data-delay="130" style="transform: translateY(14px);">
                        Ready to take<br>
                        <span class="text-white/60">the next step?</span>
                    </h2>

                    <p class="closing-reveal mt-8 max-w-lg text-[1.0625rem] leading-8 text-white/[0.68]" data-delay="230"
                        style="transform: translateY(10px);">
                        Tell us about your goals and we'll connect you with the right specialists — whether it's
                        environmental compliance, social assessment, or ESG advisory.
                    </p>

                    <div class="closing-reveal mt-12" data-delay="330" style="transform: translateY(8px);">
                        <a href="{{ route('contact') }}"
                            class="group inline-flex items-center gap-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40 focus-visible:ring-offset-4 focus-visible:ring-offset-equator-dark">
                            <span
                                class="inline-flex items-center gap-3 rounded-xl bg-white px-8 py-4 text-base font-semibold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.3)] transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:shadow-[0_20px_48px_-12px_rgba(0,0,0,0.45),0_0_0_1px_rgba(255,255,255,0.2)]">
                                Contact our team
                                <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"
                                    aria-hidden="true"></i>
                            </span>
                        </a>
                    </div>

                </div>

                {{-- ── Right: Company Profile Publication Card ── --}}
                @if ($hasProfile)
                    <div class="closing-reveal lg:col-span-5 lg:pt-3" data-delay="180"
                        style="transform: translateX(12px);">

                        <a href="{{ asset('storage/' . $companyProfile->file) }}"
                            download="{{ \Illuminate\Support\Str::slug(app_setting('company_name', 'Equator Group') . ' Company Profile') }}.pdf"
                            class="profile-doc-card group relative block overflow-hidden rounded-2xl border border-white/[0.15] bg-white/[0.07] p-8 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:border-white/[0.28] hover:bg-white/[0.12] hover:shadow-[0_28px_56px_-16px_rgba(0,0,0,0.4)] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40">

                            {{-- Hover sheen overlay --}}
                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/[0.05] via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                            </div>

                            {{-- Decorative concentric quarter-circles at top-right corner --}}
                            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full border border-white/[0.1] transition-all duration-700 group-hover:scale-[1.12] group-hover:border-white/[0.2]"
                                aria-hidden="true"></div>
                            <div class="pointer-events-none absolute -right-3 -top-3 h-14 w-14 rounded-full border border-white/[0.08] transition-all duration-700 group-hover:scale-[1.12] group-hover:border-white/[0.16]"
                                aria-hidden="true"></div>

                            {{-- Document header row --}}
                            <div class="relative flex items-start justify-between">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl border border-white/[0.18] bg-white/[0.1] text-white/60 transition-all duration-500 group-hover:border-white/[0.32] group-hover:bg-white/[0.16] group-hover:text-white">
                                    <i class="bi bi-file-earmark-text text-xl" aria-hidden="true"></i>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 rounded-full border border-white/[0.18] bg-white/[0.08] px-3 py-1 transition-all duration-300 group-hover:border-equator-orange/40 group-hover:bg-equator-orange/[0.12]">
                                    <span class="h-1 w-1 rounded-full bg-equator-orange" aria-hidden="true"></span>
                                    <span
                                        class="font-mono text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-white/60 transition-colors duration-300 group-hover:text-equator-orange/90">PDF</span>
                                </div>
                            </div>

                            {{-- Document info --}}
                            <div class="relative mt-7">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-white/40">
                                    {{ app_setting('company_name', 'Equator Group') }}
                                </p>
                                <h3
                                    class="mt-2.5 font-heading text-[1.375rem] font-semibold leading-snug tracking-tight text-white">
                                    Company Profile
                                </h3>
                                <p
                                    class="mt-3 text-sm leading-6 text-white/[0.58] transition-colors duration-300 group-hover:text-white/[0.75]">
                                    An overview of our services, expertise, and the impact we've delivered across industries
                                    and geographies.
                                </p>
                            </div>

                            {{-- Footer: file meta + download CTA --}}
                            <div class="relative mt-7 flex items-center justify-between border-t border-white/[0.14] pt-6">
                                <div class="flex items-center gap-2">
                                    @if ($companyProfile->file_size)
                                        <span
                                            class="rounded-md bg-white/[0.1] px-2 py-0.5 text-[0.65rem] font-semibold tracking-wider text-white/55">
                                            {{ number_format($companyProfile->file_size / 1048576, 1) }} MB
                                        </span>
                                        <span class="text-white/25" aria-hidden="true">·</span>
                                    @endif
                                    <span
                                        class="text-[0.65rem] font-medium uppercase tracking-[0.14em] text-white/40">Document</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 text-[0.8125rem] font-medium text-white/50 transition-all duration-300 group-hover:text-white/90">
                                    <i class="bi bi-download transition-transform duration-300 group-hover:translate-y-0.5"
                                        aria-hidden="true"></i>
                                    <span>Download</span>
                                </div>
                            </div>

                        </a>

                        <p class="mt-4 text-[0.72rem] leading-5 text-white/30">
                            Free to download · No registration required
                        </p>

                    </div>
                @endif

            </div>

            {{-- Bottom sign-off — visual signature closing the page --}}
            <div class="closing-reveal mt-24 flex items-center gap-6" data-delay="420"
                style="transform: translateY(4px);">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-white/[0.15]"></div>
                <span class="shrink-0 text-[0.65rem] font-semibold uppercase tracking-[0.28em] text-white/30">
                    {{ app_setting('company_name', 'Equator Group') }}
                </span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-white/[0.15]"></div>
            </div>

        </div>

    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const section = document.querySelector('.closing-cta-section');
            if (!section) return;

            const reveals = section.querySelectorAll('.closing-reveal');

            if (prefersReduced) {
                reveals.forEach(el => {
                    el.style.opacity = '1';
                    el.style.transform = 'none';
                });
                return;
            }

            reveals.forEach(el => {
                el.style.opacity = '0';
                el.style.transition =
                    'opacity 700ms cubic-bezier(0.22,1,0.36,1), transform 700ms cubic-bezier(0.22,1,0.36,1)';
            });

            const io = new IntersectionObserver(entries => {
                if (!entries[0].isIntersecting) return;
                reveals.forEach(el => {
                    const delay = parseInt(el.dataset.delay || '0');
                    setTimeout(() => {
                        el.style.opacity = '1';
                        el.style.transform = 'none';
                    }, delay);
                });
                io.disconnect();
            }, {
                threshold: 0.1
            });

            io.observe(section);
        });
    </script>

@endsection
