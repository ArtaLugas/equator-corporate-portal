@extends('layouts.public')

@php
    use Illuminate\Support\Str;

    $readMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $article->content)) / 200));
    $views = (int) $article->views_count;

    $metaTitle = $article->meta_title ?: $article->title;
    $metaDescription = $article->meta_description ?: Str::limit(strip_tags((string) $article->content), 155);
    $canonical = route('news.show', $article->slug);
    $ogImage = $article->image ? asset('storage/' . $article->image) : null;
@endphp

@section('title', $metaTitle)
@section('meta_description', $metaDescription)

@push('head')
    <link rel="canonical" href="{{ $canonical }}">
    {{-- Open Graph (article) --}}
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    @if ($article->published_at)
        <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
    @endif
    @if ($article->category)
        <meta property="article:section" content="{{ $article->category->name }}">
    @endif
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    @if ($article->meta_keywords)
        <meta name="keywords" content="{{ $article->meta_keywords }}">
    @endif
@endpush

@section('content')

    {{-- ════════════════════════════════════════════════════════════
         HERO BANNER — dark, consistent with other pages
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark text-white">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-equator-darker/50 to-equator-dark"></div>
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/[0.05] blur-[80px]"></div>
            <div class="absolute -bottom-32 left-10 h-80 w-80 rounded-full bg-equator-bright/[0.12] blur-[90px]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-white/55" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a>
                <span class="text-white/30" aria-hidden="true">/</span>
                <a href="{{ route('news.index') }}" class="transition-colors hover:text-white">News</a>
            </nav>

            <div>
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    @if ($article->category)
                        <a href="{{ route('news.index', ['category' => $article->category->slug]) }}"
                            class="inline-flex items-center gap-2.5 text-[0.7rem] font-bold uppercase tracking-[0.22em] text-equator-orange transition-colors hover:text-white">
                            <span class="h-px w-6 bg-equator-orange"></span>
                            {{ $article->category->name }}
                        </a>
                    @else
                        <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-white/50">Article</span>
                    @endif
                    @if ($article->is_featured)
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-equator-orange/40 bg-equator-orange/10 px-3 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-equator-orange">
                            <i class="bi bi-star-fill text-[0.55rem]"></i> Featured
                        </span>
                    @endif
                </div>

                <h1
                    class="font-heading text-[1.75rem] font-semibold leading-[1.2] tracking-tight sm:text-4xl sm:leading-[1.15] lg:text-[2.75rem] lg:leading-[1.14]">
                    {{ $article->title }}
                </h1>

                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/65">
                    @if ($article->published_at)
                        <span class="inline-flex items-center gap-2"><i
                                class="bi bi-calendar3 text-white/40"></i>{{ $article->published_at->format('d M Y') }}</span>
                    @endif
                    <span class="inline-flex items-center gap-2"><i
                            class="bi bi-clock text-white/40"></i>{{ $readMinutes }} min read</span>
                    <span class="inline-flex items-center gap-2"><i
                            class="bi bi-eye text-white/40"></i>{{ number_format($views) }} views</span>
                </div>
            </div>
        </div>
    </section>

    <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12 xl:gap-16">

                {{-- ════════════════════════ ARTICLE ════════════════════════ --}}
                <article class="lg:col-span-8">

                    {{-- Featured image (16:9, rounded) — hidden if none/missing --}}
                    @if ($article->image)
                        <figure class="overflow-hidden rounded-xl shadow-sm">
                            <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}"
                                loading="lazy" decoding="async" onerror="this.closest('figure').remove()"
                                class="aspect-video w-full object-cover">
                        </figure>
                    @endif

                    {{-- Content (CKEditor HTML) --}}
                    <div
                        class="prose prose-lg mt-10 max-w-none prose-headings:font-heading prose-headings:font-semibold prose-headings:tracking-tight prose-headings:text-equator-dark prose-h2:mb-4 prose-h2:mt-12 prose-h3:mb-3 prose-h3:mt-9 prose-p:leading-[1.85] prose-p:text-[#333333] prose-a:font-medium prose-a:text-equator-bright prose-a:underline prose-a:decoration-equator-bright/30 prose-a:underline-offset-4 hover:prose-a:decoration-equator-bright prose-blockquote:rounded-r-xl prose-blockquote:border-l-4 prose-blockquote:border-equator-orange prose-blockquote:bg-equator-bg prose-blockquote:px-6 prose-blockquote:py-1 prose-blockquote:not-italic prose-blockquote:text-equator-dark prose-figure:my-8 prose-figcaption:mt-2 prose-figcaption:text-center prose-figcaption:text-sm prose-figcaption:text-slate-400 prose-strong:text-equator-dark prose-code:rounded prose-code:bg-slate-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-[0.9em] prose-code:font-medium prose-code:text-equator-dark prose-code:before:content-[''] prose-code:after:content-[''] prose-pre:rounded-xl prose-pre:bg-equator-darker prose-pre:text-slate-100 prose-li:leading-[1.8] prose-li:text-[#333333] prose-li:marker:text-equator-orange prose-table:w-full prose-table:overflow-hidden prose-table:rounded-xl prose-table:border prose-table:border-slate-200 prose-thead:bg-equator-bg prose-th:px-4 prose-th:py-3 prose-th:text-left prose-th:font-semibold prose-th:text-equator-dark prose-td:border-t prose-td:border-slate-100 prose-td:px-4 prose-td:py-3 prose-td:text-slate-600 prose-img:mx-auto prose-img:rounded-xl prose-img:shadow-sm prose-hr:my-10 prose-hr:border-slate-200 [&_iframe]:my-8 [&_iframe]:aspect-video [&_iframe]:w-full [&_iframe]:rounded-xl [&_img]:h-auto [&_img]:max-w-full">
                        {!! $article->content !!}
                    </div>

                    {{-- Tags --}}
                    @if ($article->tags->isNotEmpty())
                        <div class="mt-10 border-t border-slate-100 pt-6">
                            <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Tags</p>
                            <x-public.tag-cloud :tags="$article->tags" />
                        </div>
                    @endif

                    {{-- Back link --}}
                    <div class="mt-10">
                        <a href="{{ route('news.index') }}"
                            class="inline-flex items-center gap-2 text-sm font-bold text-equator-dark transition-colors hover:text-equator-bright">
                            <i class="bi bi-arrow-left"></i> Back to all news
                        </a>
                    </div>
                </article>

                {{-- ════════════════════════ SIDEBAR ════════════════════════ --}}
                <aside class="lg:col-span-4">
                    <div class="space-y-8 lg:sticky lg:top-24">

                        {{-- Recent articles --}}
                        @if ($recent->isNotEmpty())
                            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h2
                                    class="mb-5 flex items-center gap-2 text-sm font-extrabold uppercase tracking-wider text-equator-dark">
                                    <span class="h-4 w-1 rounded-full bg-equator-orange"></span> Latest Articles
                                </h2>
                                <ul class="space-y-5">
                                    @foreach ($recent as $r)
                                        <li>
                                            <a href="{{ route('news.show', $r->slug) }}"
                                                class="group flex items-start gap-4">
                                                <span
                                                    class="relative h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                                    <span
                                                        class="absolute inset-0 flex items-center justify-center text-equator-dark/20">
                                                        <i class="bi bi-newspaper text-lg"></i>
                                                    </span>
                                                    @if ($r->image)
                                                        <img src="{{ asset('storage/' . $r->image) }}"
                                                            alt="{{ $r->title }}" loading="lazy"
                                                            onerror="this.remove()"
                                                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                                    @endif
                                                </span>
                                                <span class="min-w-0">
                                                    <span
                                                        class="line-clamp-2 text-sm font-bold leading-snug text-equator-text transition-colors group-hover:text-equator-bright">
                                                        {{ $r->title }}
                                                    </span>
                                                    <span
                                                        class="mt-1 block text-xs text-slate-400">{{ $r->published_at?->format('d M Y') }}</span>
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        {{-- Categories --}}
                        @if ($categories->isNotEmpty())
                            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h2
                                    class="mb-5 flex items-center gap-2 text-sm font-extrabold uppercase tracking-wider text-equator-dark">
                                    <span class="h-4 w-1 rounded-full bg-equator-orange"></span> Categories
                                </h2>
                                <ul class="space-y-1">
                                    @foreach ($categories as $cat)
                                        @php $isActive = $article->category && $article->category->id === $cat->id; @endphp
                                        <li>
                                            <a href="{{ route('news.index', ['category' => $cat->slug]) }}"
                                                @class([
                                                    'flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                                    'bg-equator-dark text-white' => $isActive,
                                                    'text-slate-600 hover:bg-equator-bg hover:text-equator-dark' => !$isActive,
                                                ])>
                                                <span>{{ $cat->name }}</span>
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-xs font-bold',
                                                    'bg-white/15 text-white' => $isActive,
                                                    'bg-slate-100 text-slate-500' => !$isActive,
                                                ])>{{ $cat->news_count }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        {{-- Topics / Tags (component — future-proof) --}}
                        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2
                                class="mb-5 flex items-center gap-2 text-sm font-extrabold uppercase tracking-wider text-equator-dark">
                                <span class="h-4 w-1 rounded-full bg-equator-orange"></span> Topics
                            </h2>
                            <x-public.tag-cloud :tags="$article->tags" />
                        </section>

                    </div>
                </aside>

            </div>
        </div>
    </div>

@endsection
