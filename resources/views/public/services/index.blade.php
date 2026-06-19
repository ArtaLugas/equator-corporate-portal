@extends('layouts.public')

@section('title', 'Services — ' . app_setting('company_name', 'Equator Group'))

@section('content')

    @include('public.partials.page-hero', [
        'title' => 'Our Services',
        'subtitle' => 'Comprehensive social and environmental consulting solutions across the entire project lifecycle.',
    ])

    @php
        $activeSlug = $activeCategory?->slug;
        $searchTerm = trim((string) request('search'));
    @endphp

    {{-- ════════════════════════════════════════════════════════════
         STICKY DISCOVERY BAR — search + sector rail
    ════════════════════════════════════════════════════════════ --}}
    <div class="sticky top-16 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-md lg:top-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Row 1: Search + credibility metric --}}
            <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">

                <form method="GET" action="{{ route('services.index') }}"
                      id="service-search-form"
                      class="relative w-full sm:max-w-md">
                    @if ($activeSlug)
                        <input type="hidden" name="category" value="{{ $activeSlug }}">
                    @endif
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="bi bi-search text-sm"></i>
                    </div>
                    <input type="search" name="search" value="{{ $searchTerm }}"
                           id="service-search-input"
                           placeholder="Search our expertise…"
                           autocomplete="off"
                           class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-24 text-sm font-medium text-equator-text placeholder-slate-400 transition-colors duration-200 hover:bg-slate-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-dark/15 [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none">
                    <div class="absolute inset-y-0 right-2 flex items-center gap-1">
                        @if ($searchTerm !== '')
                            <a href="{{ route('services.index', $activeSlug ? ['category' => $activeSlug] : []) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                               aria-label="Clear search">
                                <i class="bi bi-x-lg text-xs"></i>
                            </a>
                        @endif
                        <button type="submit"
                                class="rounded-lg bg-equator-dark px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-equator-bright">
                            Search
                        </button>
                    </div>
                </form>

                <div class="flex shrink-0 items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="flex h-2 w-2 items-center justify-center">
                        <span class="h-1.5 w-1.5 rounded-full bg-equator-orange"></span>
                    </span>
                    <span class="font-mono text-equator-dark">{{ $totalServices }}</span>
                    <span class="uppercase tracking-[0.14em] text-slate-400">Services indexed</span>
                </div>
            </div>

            {{-- Row 2: Sector rail (horizontal scroll, count badges, active indicator) --}}
            <div class="-mx-4 overflow-x-auto px-4 pb-px [scrollbar-width:none] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 [&::-webkit-scrollbar]:hidden">
                <nav class="flex min-w-max items-stretch gap-1">
                    <a href="{{ route('services.index', $searchTerm !== '' ? ['search' => $searchTerm] : []) }}"
                       @class([
                           'group relative flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold transition-colors duration-200',
                           'border-equator-dark text-equator-dark' => ! $activeSlug,
                           'border-transparent text-slate-500 hover:text-equator-dark' => $activeSlug,
                       ])>
                        All Expertise
                        <span @class([
                            'rounded-md px-1.5 py-0.5 text-[0.65rem] font-bold tabular-nums',
                            'bg-equator-dark/10 text-equator-dark' => ! $activeSlug,
                            'bg-slate-100 text-slate-400' => $activeSlug,
                        ])>{{ $totalServices }}</span>
                    </a>

                    @foreach ($categories as $cat)
                        @php $isActive = $activeSlug === $cat->slug; @endphp
                        <a href="{{ route('services.index', array_filter(['category' => $cat->slug, 'search' => $searchTerm ?: null])) }}"
                           @class([
                               'group relative flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold transition-colors duration-200',
                               'border-equator-dark text-equator-dark' => $isActive,
                               'border-transparent text-slate-500 hover:text-equator-dark' => ! $isActive,
                           ])>
                            {{ $cat->name }}
                            <span @class([
                                'rounded-md px-1.5 py-0.5 text-[0.65rem] font-bold tabular-nums',
                                'bg-equator-dark/10 text-equator-dark' => $isActive,
                                'bg-slate-100 text-slate-400' => ! $isActive,
                            ])>{{ $cat->services_count }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    <section class="bg-white pb-24 pt-12 sm:pt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Active-state context line --}}
            <div class="mb-10 flex items-baseline justify-between gap-4">
                <div>
                    <h2 class="font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl">
                        @if ($searchTerm !== '')
                            Results for “{{ $searchTerm }}”
                        @elseif ($activeCategory)
                            {{ $activeCategory->name }}
                        @else
                            All Expertise
                        @endif
                    </h2>
                    <p class="mt-1.5 text-sm text-slate-500">
                        @if ($searchTerm !== '')
                            {{ $services->total() }} {{ Str::plural('service', $services->total()) }} matched your search.
                        @elseif ($activeCategory)
                            Specialist services within {{ $activeCategory->name }}.
                        @else
                            Browse our full index of advisory and technical services.
                        @endif
                    </p>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════
                 SPOTLIGHT — featured services (bento, only on unfiltered All)
            ════════════════════════════════════════════════════════ --}}
            @if ($featured->isNotEmpty())
                <div class="mb-16">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="h-px w-6 bg-equator-orange"></span>
                        <span class="text-[0.65rem] font-bold uppercase tracking-[0.22em] text-slate-400">Featured Expertise</span>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3 lg:grid-rows-2">
                        @foreach ($featured as $i => $service)
                            <a href="{{ route('services.show', $service->slug) }}"
                               data-service-card
                               @class([
                                   'spotlight-card group relative flex overflow-hidden rounded-3xl border border-slate-200 bg-equator-dark text-white transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:shadow-[0_28px_56px_-20px_rgba(38,53,146,0.45)]',
                                   // First featured = large hero tile spanning 2 cols x 2 rows
                                   'lg:col-span-2 lg:row-span-2 min-h-[260px] lg:min-h-[420px]' => $i === 0,
                                   'min-h-[200px]' => $i !== 0,
                               ])>

                                {{-- Background image --}}
                                @if ($service->image)
                                    <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}"
                                         loading="lazy"
                                         class="absolute inset-0 h-full w-full object-cover opacity-45 transition-all duration-700 ease-out group-hover:scale-105 group-hover:opacity-55">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-equator-darker via-equator-dark/70 to-equator-dark/20"></div>

                                {{-- Content --}}
                                <div class="relative z-10 flex w-full flex-col justify-end p-6 sm:p-7 {{ $i === 0 ? 'lg:p-9' : '' }}">
                                    @if ($service->category)
                                        <span class="mb-2 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-equator-orange">
                                            {{ $service->category->name }}
                                        </span>
                                    @endif
                                    <h3 @class([
                                        'font-heading font-semibold leading-tight tracking-tight',
                                        'text-2xl sm:text-3xl lg:text-[2rem]' => $i === 0,
                                        'text-lg sm:text-xl' => $i !== 0,
                                    ])>{{ $service->name }}</h3>

                                    @if ($i === 0 && $service->short_description)
                                        <p class="mt-3 max-w-md text-sm leading-relaxed text-white/70 line-clamp-2">
                                            {{ $service->short_description }}
                                        </p>
                                    @endif

                                    <span class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-white/80 transition-colors group-hover:text-white">
                                        Explore service
                                        <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 THE INDEX — numbered editorial cards
            ════════════════════════════════════════════════════════ --}}
            @if ($services->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-50/50 py-24 text-center">
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-300">
                        <i class="bi bi-search text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold text-equator-dark">No services found</h3>
                    <p class="mt-1.5 max-w-sm text-sm text-slate-500">
                        We couldn't find any expertise matching your criteria. Try a different sector or clear your search.
                    </p>
                    <a href="{{ route('services.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright">
                        Reset filters
                    </a>
                </div>
            @else
                @if ($featured->isNotEmpty())
                    <div class="mb-5 flex items-center gap-3">
                        <span class="h-px w-6 bg-equator-orange"></span>
                        <span class="text-[0.65rem] font-bold uppercase tracking-[0.22em] text-slate-400">All Services</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-7">
                    @foreach ($services as $i => $service)
                        @php $index = ($services->firstItem() ?? 1) + $i; @endphp
                        <a href="{{ route('services.show', $service->slug) }}"
                           data-service-card
                           class="index-card group relative flex flex-col rounded-2xl border border-slate-200/80 bg-white p-5 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_18px_40px_-20px_rgba(38,53,146,0.25)]">

                            {{-- Top row: index number + category --}}
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-bold text-slate-300 transition-colors group-hover:text-equator-orange">
                                    {{ str_pad($index, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                @if ($service->category)
                                    <span class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-slate-400">
                                        {{ $service->category->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Image strip --}}
                            <div class="mt-4 aspect-[16/10] overflow-hidden rounded-xl bg-slate-100">
                                @if ($service->image)
                                    <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}"
                                         loading="lazy"
                                         class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/25">
                                        <i class="bi bi-gear-wide-connected text-3xl"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Title (large, editorial) --}}
                            <h3 class="mt-5 font-heading text-lg font-semibold leading-snug tracking-tight text-equator-dark transition-colors group-hover:text-equator-bright sm:text-xl">
                                {{ $service->name }}
                            </h3>

                            @if ($service->short_description)
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-500">
                                    {{ $service->short_description }}
                                </p>
                            @endif

                            {{-- Footer: arrow + growing accent line --}}
                            <div class="mt-5 flex items-center gap-3 pt-1">
                                <span class="text-xs font-semibold text-slate-400 transition-colors group-hover:text-equator-dark">Read more</span>
                                <span class="h-px flex-1 bg-slate-200"></span>
                                <i class="bi bi-arrow-right text-sm text-slate-400 transition-all duration-300 group-hover:translate-x-1 group-hover:text-equator-bright"></i>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($services->hasPages())
                    <div class="mt-14">{{ $services->links() }}</div>
                @endif
            @endif

            {{-- ════════════════════════════════════════════════════════
                 RECENTLY VIEWED — populated client-side from localStorage
            ════════════════════════════════════════════════════════ --}}
            <div id="recently-viewed" class="mt-20 hidden border-t border-slate-200 pt-12">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-px w-6 bg-equator-orange"></span>
                    <span class="text-[0.65rem] font-bold uppercase tracking-[0.22em] text-slate-400">Recently Viewed</span>
                </div>
                <div id="recently-viewed-rail"
                     class="-mx-4 flex gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
                    {{-- JS injects cards here --}}
                </div>
            </div>

        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         INTERACTIONS
    ════════════════════════════════════════════════════════════ --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // ── 1. Debounced instant-feel search (auto-submit) ──────────────
            const form = document.getElementById('service-search-form');
            const input = document.getElementById('service-search-input');
            if (form && input) {
                let timer;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => form.submit(), 550);
                });
                // Keep focus position after server round-trip
                if (input.value) {
                    const v = input.value;
                    input.focus();
                    input.setSelectionRange(v.length, v.length);
                }
            }

            // ── 2. Scroll entrance stagger for cards ────────────────────────
            if (!prefersReduced) {
                const cards = document.querySelectorAll('[data-service-card]');
                cards.forEach((el, i) => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(16px)';
                    el.style.transition =
                        'opacity 600ms cubic-bezier(0.22,1,0.36,1), transform 600ms cubic-bezier(0.22,1,0.36,1)';
                });
                const io = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) return;
                        const el = entry.target;
                        const delay = Math.min(parseInt(el.dataset.stagger || '0'), 300);
                        setTimeout(() => {
                            el.style.opacity = '1';
                            el.style.transform = 'none';
                        }, delay);
                        obs.unobserve(el);
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
                cards.forEach((el, i) => {
                    el.dataset.stagger = (i % 9) * 45;
                    io.observe(el);
                });
            }

            // ── 3. Recently viewed (localStorage) ───────────────────────────
            const KEY = 'equator_recent_services';
            const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            try {
                const items = JSON.parse(localStorage.getItem(KEY) || '[]');
                if (Array.isArray(items) && items.length) {
                    const rail = document.getElementById('recently-viewed-rail');
                    const wrap = document.getElementById('recently-viewed');
                    rail.innerHTML = items.slice(0, 8).map(it => `
                        <a href="${escapeHtml(it.url)}"
                           class="group flex w-52 shrink-0 flex-col rounded-xl border border-slate-200 bg-white p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md">
                            <span class="text-[0.6rem] font-bold uppercase tracking-[0.16em] text-slate-400">${escapeHtml(it.category || 'Service')}</span>
                            <span class="mt-1.5 font-heading text-sm font-semibold leading-snug text-equator-dark line-clamp-2 group-hover:text-equator-bright">${escapeHtml(it.name)}</span>
                            <span class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 group-hover:text-equator-dark">
                                View <i class="bi bi-arrow-right transition-transform group-hover:translate-x-1"></i>
                            </span>
                        </a>`).join('');
                    wrap.classList.remove('hidden');
                }
            } catch (e) { /* ignore malformed storage */ }
        });
    </script>

@endsection
