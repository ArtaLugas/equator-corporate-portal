@extends('layouts.public')

@section('title', $service->meta_title ?: $service->name)
@section('meta_description', $service->meta_description ?:
    \Illuminate\Support\Str::limit(strip_tags($service->short_description), 150))

@if ($service->image)
    @section('og_image', asset('storage/' . $service->image))
@endif

@push('head')
    @php
        $svcLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Service',
                    'name' => $service->name,
                    'serviceType' => $service->category?->name,
                    'description' => \Illuminate\Support\Str::limit(strip_tags($service->short_description ?: $service->description), 200),
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                    'url' => route('services.show', $service->slug),
                    'provider' => ['@type' => 'Organization', 'name' => app_setting('company_name', 'Equator Group'), 'url' => url('/')],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => route('services.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $service->name, 'item' => route('services.show', $service->slug)],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($svcLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    @php
        $projects = $service->projects ?? collect();
        $contactUrl = route('contact', ['service' => $service->name]);
    @endphp

    {{-- ════════════════════════════════════════════════════════════
         HERO — service identity (single CTA)
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark text-white" data-service-hero>
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-equator-darker/50 to-equator-dark"></div>
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/[0.05] blur-[80px]"></div>
            <div class="absolute -bottom-32 left-10 h-80 w-80 rounded-full bg-equator-bright/[0.12] blur-[90px]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-x-16">

                <div class="{{ $service->image ? 'lg:col-span-7' : 'lg:col-span-9' }}">
                    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-white/55">
                        <a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a>
                        <span>/</span>
                        <a href="{{ route('services.index') }}" class="transition-colors hover:text-white">Services</a>
                        @if ($service->category)
                            <span>/</span>
                            <span class="text-white/85">{{ $service->category->name }}</span>
                        @endif
                    </nav>

                    @if ($service->category)
                        <div class="mb-5 flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-equator-orange">
                                {{ $service->category->name }}
                            </span>
                        </div>
                    @endif

                    <h1
                        class="font-heading text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.5rem]">
                        {{ $service->name }}
                    </h1>

                    @if ($service->short_description)
                        <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/70">
                            {{ $service->short_description }}
                        </p>
                    @endif

                    <div class="mt-9">
                        <a href="{{ $contactUrl }}"
                            class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)]">
                            Discuss this service
                            <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                        </a>
                    </div>
                </div>

                @if ($service->image)
                    <div class="lg:col-span-5">
                        <div
                            class="overflow-hidden rounded-3xl border border-white/10 shadow-[0_30px_60px_-25px_rgba(0,0,0,0.6)]">
                            <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}"
                                width="800" height="600" loading="eager" fetchpriority="high" decoding="async"
                                class="aspect-[4/3] w-full object-cover">
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         OVERVIEW — the main content of the page (readable, prominent)
    ════════════════════════════════════════════════════════════ --}}
    <section class="bg-white py-16 sm:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center gap-3">
                <span class="h-px w-8 bg-equator-orange"></span>
                <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">Overview</span>
            </div>

            <div
                class="prose prose-xl max-w-none prose-headings:font-heading prose-headings:font-semibold prose-headings:tracking-tight prose-headings:text-equator-dark prose-p:text-[1.2rem] prose-p:leading-[1.85] prose-p:text-slate-600 prose-a:font-medium prose-a:text-equator-bright prose-a:no-underline hover:prose-a:underline prose-strong:text-equator-dark prose-li:text-[1.2rem] prose-li:leading-[1.8] prose-li:text-slate-600">
                {!! $service->description ?:
                    '<p class="text-slate-400">A detailed overview for this service is being prepared. Get in touch and our team will walk you through it in detail.</p>' !!}
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         PROJECTS — linked work (real references)
    ════════════════════════════════════════════════════════════ --}}
    @if ($projects->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex items-end justify-between gap-4">
                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">Selected
                                Work</span>
                        </div>
                        <h2 class="font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl">
                            Related projects
                        </h2>
                    </div>
                    <a href="{{ route('projects.index') }}"
                        class="hidden shrink-0 items-center gap-1.5 text-sm font-semibold text-equator-bright transition-colors hover:text-equator-dark sm:inline-flex">
                        All projects
                        <i class="bi bi-arrow-right text-xs"></i>
                    </a>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($projects as $project)
                        <a href="{{ route('projects.show', $project->slug) }}"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_18px_40px_-20px_rgba(38,53,146,0.3)]">
                            <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                                @if ($project->featured_image)
                                    <img src="{{ asset('storage/' . $project->featured_image) }}"
                                        alt="{{ $project->name }}" loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/25">
                                        <i class="bi bi-buildings text-3xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                @if ($project->client_name || $project->country)
                                    <span class="text-[0.62rem] font-bold uppercase tracking-[0.16em] text-slate-400">
                                        {{ collect([$project->client_name, $project->country])->filter()->implode(' · ') }}
                                    </span>
                                @endif
                                <h3
                                    class="mt-2 font-heading text-base font-semibold leading-snug text-equator-dark transition-colors group-hover:text-equator-bright">
                                    {{ $project->name }}
                                </h3>
                                @if ($project->location)
                                    <span class="mt-3 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                        <i class="bi bi-geo-alt"></i>{{ $project->location }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         CTA — single closing call to action
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark py-20 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-equator-bright/[0.14] blur-[90px]"></div>
        </div>
        <div class="relative mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="font-heading text-3xl font-semibold tracking-tight sm:text-4xl">
                Have a project in mind?
            </h2>
            <p class="mt-4 text-base leading-relaxed text-white/65">
                Tell us about your objectives and our team will get back to you within one business day.
            </p>
            <div class="mt-9">
                <a href="{{ $contactUrl }}"
                    class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-white px-8 py-4 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)]">
                    Discuss this service
                    <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                </a>
            </div>
            <div class="mt-10">
                <a href="{{ route('services.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-white/55 transition-colors hover:text-white">
                    <i class="bi bi-arrow-left"></i>
                    Back to all services
                </a>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         SCRIPTS — sticky bar visibility + recently-viewed recorder
    ════════════════════════════════════════════════════════════ --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hero = document.querySelector('[data-service-hero]');
            const bar = document.getElementById('service-action-bar');
            if (hero && bar) {
                const io = new IntersectionObserver(([entry]) => {
                    const show = !entry.isIntersecting;
                    bar.classList.toggle('translate-y-full', !show);
                    bar.classList.toggle('opacity-0', !show);
                }, {
                    rootMargin: '-40% 0px 0px 0px'
                });
                io.observe(hero);
            }

            // Record this service for the "recently viewed" rail on the services index.
            const KEY = 'equator_recent_services';
            const entry = {
                name: @json($service->name),
                url: @json(route('services.show', $service->slug)),
                category: @json($service->category?->name),
            };
            try {
                let items = JSON.parse(localStorage.getItem(KEY) || '[]');
                if (!Array.isArray(items)) items = [];
                items = items.filter(it => it && it.url !== entry.url);
                items.unshift(entry);
                localStorage.setItem(KEY, JSON.stringify(items.slice(0, 8)));
            } catch (e) {
                /* storage unavailable — non-critical */
            }
        });
    </script>

@endsection
