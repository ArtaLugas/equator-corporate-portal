@extends('layouts.public')

@section('title', __('news.page_title', ['company' => app_setting('company_name', 'Equator Group')]))

@section('meta_description', __('news.meta_description', ['company' => app_setting('company_name', 'Equator Group')]))

@section('content')

    @php
        use Illuminate\Support\Str;

        $activeSlug = $activeCategory?->slug;
        $hasFilter = $activeCategory || $search !== '';

        // Reading-time estimate (≈200 wpm) derived from content — no new field.
        $readTime = fn ($a) => max(1, (int) ceil(str_word_count(strip_tags((string) ($a->content ?? ''))) / 200));

        $categoryHref = fn ($slug = null) => route('news.index', array_filter([
            'category' => $slug,
            'search' => $search ?: null,
        ]));
    @endphp

    {{-- ════════════════════════════════════════════════════════════
         HERO — Latest Insight (featured lead) OR compact header (filtered)
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark text-white">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-equator-darker/50 to-equator-dark"></div>
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/[0.05] blur-[80px]"></div>
            <div class="absolute -bottom-32 left-10 h-80 w-80 rounded-full bg-equator-bright/[0.12] blur-[90px]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <nav class="mb-8 flex items-center gap-2 text-xs font-medium text-white/55">
                <a href="{{ route('home') }}" class="transition-colors hover:text-white">{{ __('news.breadcrumb_home') }}</a>
                <span>/</span>
                <span class="text-white/85">{{ __('news.breadcrumb_insights') }}</span>
            </nav>

            @if ($lead)
                {{-- Featured Latest Insight --}}
                <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-x-16">
                    <div class="lg:col-span-7">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-equator-orange">{{ __('news.latest_insight_eyebrow') }}</span>
                        </div>

                        <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium text-white/55">
                            @if ($lead->category)
                                <span class="font-bold uppercase tracking-[0.16em] text-white/75">{{ $lead->category->name }}</span>
                                <span class="text-white/25" aria-hidden="true">·</span>
                            @endif
                            @if ($lead->published_at)
                                <span>{{ $lead->published_at->format('d M Y') }}</span>
                                <span class="text-white/25" aria-hidden="true">·</span>
                            @endif
                            <span>{{ __('news.min_read', ['count' => $readTime($lead)]) }}</span>
                        </div>

                        <a href="{{ route('news.show', $lead->slug) }}" class="group block">
                            <h1 class="font-heading text-3xl font-semibold leading-[1.1] tracking-tight transition-colors group-hover:text-white/90 sm:text-4xl lg:text-[2.75rem]">
                                {{ $lead->title }}
                            </h1>
                            <span class="mt-7 inline-flex items-center gap-2.5 text-sm font-semibold text-white">
                                {{ __('news.read_insight') }}
                                <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                            </span>
                        </a>
                    </div>

                    <div class="lg:col-span-5">
                        <a href="{{ route('news.show', $lead->slug) }}"
                           class="group relative block aspect-[4/3] overflow-hidden rounded-2xl border border-white/10 bg-equator-darker shadow-[0_30px_60px_-25px_rgba(0,0,0,0.6)]">
                            <div class="absolute inset-0 flex items-center justify-center text-white/10">
                                <i class="bi bi-journal-text text-5xl"></i>
                            </div>
                            @if ($lead->image)
                                <img src="{{ asset('storage/' . $lead->image) }}" alt="{{ $lead->title }}"
                                     onerror="this.remove()"
                                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                            @endif
                        </a>
                    </div>
                </div>
            @else
                {{-- Compact header for filtered / search view --}}
                <div class="max-w-3xl">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="h-px w-8 bg-equator-orange"></span>
                        <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-equator-orange">{{ __('news.hero_eyebrow') }}</span>
                    </div>
                    <h1 class="font-heading text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl">
                        {{ __('news.hero_heading') }}
                    </h1>
                    <p class="mt-5 text-lg leading-relaxed text-white/70">
                        {{ __('news.hero_subtitle') }}
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         STICKY TOOLBAR — search + category chips (no sidebar)
    ════════════════════════════════════════════════════════════ --}}
    <div class="sticky top-16 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-md lg:top-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 py-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Category chips --}}
                @if ($categories->count() > 1)
                    <div class="-mx-4 overflow-x-auto px-4 [scrollbar-width:none] sm:mx-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
                        <div class="flex min-w-max items-center gap-1.5">
                            <a href="{{ $categoryHref() }}"
                               @class([
                                   'rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                                   'bg-equator-dark text-white' => ! $activeCategory,
                                   'bg-slate-100 text-slate-600 hover:bg-slate-200' => $activeCategory,
                               ])>{{ __('news.all_insights') }}</a>
                            @foreach ($categories as $cat)
                                @php $isActive = $activeSlug === $cat->slug; @endphp
                                <a href="{{ $categoryHref($cat->slug) }}"
                                   @class([
                                       'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                                       'bg-equator-dark text-white' => $isActive,
                                       'bg-slate-100 text-slate-600 hover:bg-slate-200' => ! $isActive,
                                   ])>
                                    {{ $cat->name }}
                                    <span @class([
                                        'rounded-md px-1.5 py-0.5 text-[0.65rem] font-bold tabular-nums',
                                        'bg-white/15 text-white' => $isActive,
                                        'bg-white text-slate-400' => ! $isActive,
                                    ])>{{ $cat->news_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Search --}}
                <form method="GET" action="{{ route('news.index') }}" id="news-search-form" class="relative shrink-0">
                    @if ($activeSlug)
                        <input type="hidden" name="category" value="{{ $activeSlug }}">
                    @endif
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                    <input type="search" name="search" value="{{ $search }}" id="news-search-input"
                           placeholder="{{ __('news.search_placeholder') }}"
                           aria-label="{{ __('news.search_placeholder') }}" autocomplete="off"
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-20 text-sm text-equator-text placeholder-slate-400 transition-colors hover:bg-slate-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-dark/15 lg:w-64 [&::-webkit-search-cancel-button]:appearance-none">
                    <div class="absolute inset-y-0 right-1.5 flex items-center gap-1">
                        @if ($search !== '')
                            <a href="{{ $categoryHref($activeSlug) }}"
                               class="flex h-6 w-6 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                               aria-label="{{ __('news.search_clear_aria') }}"><i class="bi bi-x-lg text-[0.65rem]"></i></a>
                        @endif
                        <button type="submit" class="rounded-md bg-equator-dark px-2.5 py-1 text-xs font-bold text-white transition-colors hover:bg-equator-bright">{{ __('news.search_submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         THE INSIGHTS — chronological editorial grid
    ════════════════════════════════════════════════════════════ --}}
    <section class="bg-white py-14 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <div class="mb-4 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">
                        {{ $hasFilter ? __('news.section_results') : __('news.section_insights') }}
                    </span>
                </div>
                <h2 class="font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl">
                    @if ($search !== '')
                        {{ __('news.results_for', ['query' => $search]) }}
                    @elseif ($activeCategory)
                        {{ $activeCategory->name }}
                    @else
                        {{ __('news.latest_perspectives') }}
                    @endif
                </h2>
                @if ($hasFilter)
                    <p class="mt-1.5 text-sm text-slate-500">
                        {{ __('news.count_found', ['count' => $news->total(), 'label' => $news->total() === 1 ? __('news.insight_singular') : __('news.insight_plural')]) }} ·
                        <a href="{{ route('news.index') }}" class="font-semibold text-equator-bright hover:text-equator-dark">{{ __('common.clear') }}</a>
                    </p>
                @endif
            </div>

            @if ($news->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-50/50 py-24 text-center">
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-300">
                        <i class="bi bi-search text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold text-equator-dark">{{ __('news.empty_heading') }}</h3>
                    <p class="mt-1.5 max-w-sm text-sm text-slate-500">{{ __('news.empty_body') }}</p>
                    <a href="{{ route('news.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright">
                        {{ __('news.empty_action') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-7">
                    @foreach ($news as $article)
                        <a href="{{ route('news.show', $article->slug) }}"
                           class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_40px_-20px_rgba(38,53,146,0.3)]">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/25">
                                    <i class="bi bi-journal-text text-3xl"></i>
                                </div>
                                @if ($article->image)
                                    <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" loading="lazy"
                                         onerror="this.remove()"
                                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <div class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em]">
                                    @if ($article->category)
                                        <span class="text-equator-bright">{{ $article->category->name }}</span>
                                        <span class="text-slate-300" aria-hidden="true">·</span>
                                    @endif
                                    <span class="text-slate-400">{{ $article->published_at?->format('d M Y') }}</span>
                                </div>
                                <h3 class="line-clamp-3 flex-1 font-heading text-lg font-semibold leading-snug text-equator-dark transition-colors group-hover:text-equator-bright">
                                    {{ $article->title }}
                                </h3>
                                <div class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-400">
                                    <span>{{ __('news.min_read', ['count' => $readTime($article)]) }}</span>
                                    <span class="h-px flex-1 bg-slate-100"></span>
                                    <i class="bi bi-arrow-right transition-all duration-300 group-hover:translate-x-1 group-hover:text-equator-bright"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($news->hasPages())
                    <div class="mt-12">{{ $news->links() }}</div>
                @endif
            @endif
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         MOST READ — only when real view data exists
    ════════════════════════════════════════════════════════════ --}}
    @if ($mostRead->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">{{ __('news.most_read') }}</span>
                </div>
                <div class="grid gap-x-10 gap-y-2 sm:grid-cols-2 lg:gap-x-16">
                    @foreach ($mostRead as $i => $article)
                        <a href="{{ route('news.show', $article->slug) }}"
                           class="group flex items-start gap-5 border-b border-slate-200 py-5 transition-colors hover:border-slate-300">
                            <span class="font-heading text-2xl font-bold text-slate-200 transition-colors group-hover:text-equator-orange">
                                {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="min-w-0">
                                @if ($article->category)
                                    <span class="text-[0.6rem] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $article->category->name }}</span>
                                @endif
                                <span class="mt-1 block font-heading text-base font-semibold leading-snug text-equator-dark transition-colors group-hover:text-equator-bright">
                                    {{ $article->title }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         KNOWLEDGE CTA — turn insight into action (soft)
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-darker py-20 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-equator-bright/[0.14] blur-[90px]"></div>
        </div>
        <div class="relative mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="font-heading text-3xl font-semibold tracking-tight sm:text-4xl">
                {{ __('news.cta_heading') }}
            </h2>
            <p class="mt-4 text-base leading-relaxed text-white/65">
                {{ __('news.cta_body') }}
            </p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('services.index') }}"
                   class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)]">
                    {{ __('news.cta_services') }}
                    <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                </a>
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-white/25 px-7 py-3.5 text-sm font-bold text-white transition-colors duration-300 hover:bg-white/10">
                    {{ __('news.cta_contact') }}
                </a>
            </div>
            <div class="mt-8">
                <a href="{{ route('projects.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-white/55 transition-colors hover:text-white">
                    {{ __('news.cta_work') }}
                    <i class="bi bi-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Debounced instant-feel search --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('news-search-form');
            const input = document.getElementById('news-search-input');
            if (!form || !input) return;
            let t;
            input.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => form.submit(), 550); });
            if (input.value) { const v = input.value; input.focus(); input.setSelectionRange(v.length, v.length); }
        });
    </script>

@endsection
