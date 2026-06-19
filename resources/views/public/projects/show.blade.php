@extends('layouts.public')

@section('title', $project->meta_title ?: $project->name)
@section('meta_description', $project->meta_description ?:
    \Illuminate\Support\Str::limit(strip_tags($project->short_description ?: $project->name), 150))

@if ($project->featured_image)
    @section('og_image', asset('storage/' . $project->featured_image))
@endif

@push('head')
    @php
        $projLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'CreativeWork',
                    'name' => $project->name,
                    'description' => \Illuminate\Support\Str::limit(strip_tags($project->short_description ?: $project->description), 200),
                    'image' => $project->featured_image ? asset('storage/' . $project->featured_image) : null,
                    'url' => route('projects.show', $project->slug),
                    'dateCreated' => $project->start_date?->toDateString(),
                    'creator' => ['@type' => 'Organization', 'name' => app_setting('company_name', 'Equator Group'), 'url' => url('/')],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Projects', 'item' => route('projects.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $project->name, 'item' => route('projects.show', $project->slug)],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($projLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    @php
        use Illuminate\Support\Str;

        $services = $project->services ?? collect();
        $images = $project->images ?? collect();

        // Period string (start year — end year / Present), only if a start date exists.
        $period = null;
        if ($project->start_date) {
            $start = $project->start_date->format('Y');
            $end = $project->end_date ? $project->end_date->format('Y') : 'Present';
            $period = $start === $end ? $start : "{$start} — {$end}";
        }

        $statusMeta = [
            'completed' => ['label' => 'Completed', 'dot' => 'bg-emerald-400'],
            'ongoing' => ['label' => 'Ongoing', 'dot' => 'bg-blue-400'],
            'planned' => ['label' => 'Planned', 'dot' => 'bg-amber-400'],
        ][$project->status] ?? ['label' => ucfirst((string) $project->status), 'dot' => 'bg-slate-400'];

        $locationLine = collect([$project->location, $project->country])
            ->filter()
            ->implode(', ');

        // Snapshot facts — project facts only (no services; those live in Scope of Work).
        $facts = collect([
            ['label' => 'Client', 'value' => $project->client_name],
            ['label' => 'Location', 'value' => $locationLine ?: null],
            ['label' => 'Status', 'value' => $statusMeta['label'], 'dot' => $statusMeta['dot']],
            ['label' => 'Period', 'value' => $period],
        ])
            ->filter(fn($f) => filled($f['value']))
            ->values();

        // Literal grid classes (Tailwind can't see dynamically-built class names).
$factCols = match ($facts->count()) {
    2 => 'sm:grid-cols-2',
    3 => 'sm:grid-cols-3',
    default => 'md:grid-cols-4',
};

// CTA intent — reference the proven capability when available, else the project.
$ctaService = $services->first()?->name ?? $project->name;
$contactUrl = route('contact', ['service' => $ctaService]);
    @endphp

    {{-- ════════════════════════════════════════════════════════════
         1 — PROJECT HERO (cinematic, fact-forward; graceful image fallback)
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark text-white">
        {{-- Featured image (self-removes if the file is missing → fallback below shows) --}}
        @if ($project->featured_image)
            <img src="{{ asset('storage/' . $project->featured_image) }}" alt="{{ $project->name }}"
                width="1600" height="900" decoding="async" onerror="this.remove()"
                class="absolute inset-0 h-full w-full object-cover opacity-40">
        @endif

        {{-- Ghost initial — premium fallback that always sits behind --}}
        <div class="pointer-events-none absolute inset-0 flex items-center justify-end overflow-hidden" aria-hidden="true">
            <span class="mr-[-1.5rem] font-heading text-[16rem] font-bold leading-none text-white/[0.04] sm:text-[22rem]">
                {{ Str::upper(Str::substr($project->name, 0, 1)) }}
            </span>
        </div>

        {{-- Depth gradients --}}
        <div class="absolute inset-0 bg-gradient-to-t from-equator-darker via-equator-dark/80 to-equator-dark/40"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-equator-dark/90 via-equator-dark/50 to-transparent"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
            <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-white/55">
                <a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a>
                <span>/</span>
                <a href="{{ route('projects.index') }}" class="transition-colors hover:text-white">Projects</a>
            </nav>

            <div class="max-w-3xl">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-equator-orange">Project Case
                        Study</span>
                </div>

                <h1 class="font-heading text-3xl font-semibold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
                    {{ $project->name }}
                </h1>

                {{-- Fact row — credibility above the fold --}}
                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/75">
                    @if ($project->client_name)
                        <span class="inline-flex items-center gap-2"><i
                                class="bi bi-building text-white/45"></i>{{ $project->client_name }}</span>
                    @endif
                    @if ($locationLine)
                        <span class="inline-flex items-center gap-2"><i
                                class="bi bi-geo-alt text-white/45"></i>{{ $locationLine }}</span>
                    @endif
                    <span class="inline-flex items-center gap-2">
                        <span class="{{ $statusMeta['dot'] }} h-1.5 w-1.5 rounded-full"></span>{{ $statusMeta['label'] }}
                    </span>
                    @if ($period)
                        <span class="inline-flex items-center gap-2"><i
                                class="bi bi-calendar3 text-white/45"></i>{{ $period }}</span>
                    @endif
                </div>

                <div class="mt-9">
                    <a href="{{ $contactUrl }}"
                        class="group inline-flex items-center gap-2.5 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)]">
                        Discuss a similar engagement
                        <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         2 — SNAPSHOT BAND (facts strip — not a sidebar)
    ════════════════════════════════════════════════════════════ --}}
    @if ($facts->isNotEmpty())
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <dl class="{{ $factCols }} grid grid-cols-2 divide-slate-200 sm:divide-x">
                    @foreach ($facts as $fact)
                        <div class="{{ $loop->first ? 'sm:pl-0' : '' }} px-2 py-8 sm:px-6 sm:py-9">
                            <dt class="text-[0.62rem] font-bold uppercase tracking-[0.18em] text-slate-400">
                                {{ $fact['label'] }}</dt>
                            <dd
                                class="mt-2 flex items-center gap-2 font-heading text-base font-semibold text-equator-dark sm:text-lg">
                                @isset($fact['dot'])
                                    <span class="{{ $fact['dot'] }} h-2 w-2 rounded-full"></span>
                                @endisset
                                {{ $fact['value'] }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         4 — SCOPE OF WORK (only when services exist)
    ════════════════════════════════════════════════════════════ --}}
    @if ($services->isNotEmpty())
        <section class="bg-slate-50 py-14 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">Scope of Work</span>
                </div>
                <p class="mb-6 max-w-2xl font-heading text-xl font-semibold tracking-tight text-equator-dark">
                    Services proven by this engagement
                </p>
                <div class="flex flex-wrap gap-2.5">
                    @foreach ($services as $svc)
                        <a href="{{ route('services.show', $svc->slug) }}"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-equator-dark transition-all duration-200 hover:border-equator-dark hover:shadow-sm">
                            {{ $svc->name }}
                            <i
                                class="bi bi-arrow-right text-xs text-slate-400 transition-all duration-200 group-hover:translate-x-0.5 group-hover:text-equator-bright"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         5 — PROJECT NARRATIVE (main content, full stage)
    ════════════════════════════════════════════════════════════ --}}
    @if (filled(strip_tags($project->description)))
        <section class="bg-white py-16 sm:py-24">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">Overview</span>
                </div>
                <div
                    class="prose prose-xl max-w-none prose-headings:font-heading prose-headings:font-semibold prose-headings:tracking-tight prose-headings:text-equator-dark prose-p:text-[1.2rem] prose-p:leading-[1.85] prose-p:text-slate-600 prose-a:font-medium prose-a:text-equator-bright prose-a:no-underline hover:prose-a:underline prose-strong:text-equator-dark prose-li:text-[1.2rem] prose-li:leading-[1.8] prose-li:text-slate-600">
                    {!! $project->description !!}
                </div>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         6 — GALLERY (immersive; only when images exist)
    ════════════════════════════════════════════════════════════ --}}
    @if ($images->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-16 sm:py-20" x-data="{ lightbox: null }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">From the Field</span>
                </div>

                @if ($images->count() === 1)
                    {{-- Single image → one large immersive visual (no awkward 1-item grid) --}}
                    @php $img = $images->first(); @endphp
                    <figure>
                        <button type="button" @click="lightbox = '{{ asset('storage/' . $img->image) }}'"
                            class="group block w-full overflow-hidden rounded-2xl bg-slate-100">
                            <img src="{{ asset('storage/' . $img->image) }}" alt="{{ $img->caption ?: $project->name }}"
                                loading="lazy"
                                class="aspect-[21/9] w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </button>
                        @if ($img->caption)
                            <figcaption class="mt-3 text-sm text-slate-500">{{ $img->caption }}</figcaption>
                        @endif
                    </figure>
                @else
                    {{-- Lead image + supporting strip --}}
                    @php
                        $lead = $images->first();
                        $rest = $images->slice(1);
                    @endphp
                    <figure class="mb-4">
                        <button type="button" @click="lightbox = '{{ asset('storage/' . $lead->image) }}'"
                            class="group block w-full overflow-hidden rounded-2xl bg-slate-100">
                            <img src="{{ asset('storage/' . $lead->image) }}" alt="{{ $lead->caption ?: $project->name }}"
                                loading="lazy"
                                class="aspect-[21/9] w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </button>
                        @if ($lead->caption)
                            <figcaption class="mt-3 text-sm text-slate-500">{{ $lead->caption }}</figcaption>
                        @endif
                    </figure>
                    <div
                        class="-mx-4 flex gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-4 sm:overflow-visible sm:px-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden">
                        @foreach ($rest as $img)
                            <figure class="w-64 shrink-0 sm:w-auto">
                                <button type="button" @click="lightbox = '{{ asset('storage/' . $img->image) }}'"
                                    class="group block aspect-[4/3] w-full overflow-hidden rounded-xl bg-slate-100">
                                    <img src="{{ asset('storage/' . $img->image) }}"
                                        alt="{{ $img->caption ?: $project->name }}" loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                </button>
                                @if ($img->caption)
                                    <figcaption class="mt-2 text-xs text-slate-500">{{ $img->caption }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Lightbox --}}
            <div x-show="lightbox" x-cloak @click="lightbox = null" @keydown.escape.window="lightbox = null"
                role="dialog" aria-modal="true" aria-label="Project image viewer (press Escape to close)"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 p-4">
                <button type="button" @click="lightbox = null" aria-label="Close image viewer"
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <img :src="lightbox" alt="Enlarged project image" class="max-h-[90vh] max-w-full rounded-xl object-contain">
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         7 — RELATED SERVICES (capability proof; only when services exist)
    ════════════════════════════════════════════════════════════ --}}
    @if ($services->isNotEmpty())
        <section class="border-t border-slate-200 bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="h-px w-8 bg-equator-orange"></span>
                        <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">Capability
                            Proof</span>
                    </div>
                    <h2 class="font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl">
                        Capabilities this work demonstrates
                    </h2>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($services as $svc)
                        <a href="{{ route('services.show', $svc->slug) }}"
                            class="group flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_18px_40px_-20px_rgba(38,53,146,0.25)]">
                            <div>
                                @if ($svc->category)
                                    <span
                                        class="text-[0.62rem] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $svc->category->name }}</span>
                                @endif
                                <h3
                                    class="mt-2 font-heading text-lg font-semibold leading-snug text-equator-dark transition-colors group-hover:text-equator-bright">
                                    {{ $svc->name }}
                                </h3>
                            </div>
                            <span
                                class="mt-6 inline-flex items-center gap-2 text-xs font-semibold text-slate-400 transition-colors group-hover:text-equator-dark">
                                Explore capability
                                <i
                                    class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         8 — RELATED / MORE PROJECTS (neutral heading when fallback)
    ════════════════════════════════════════════════════════════ --}}
    @if ($related->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex items-end justify-between gap-4">
                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">More
                                Evidence</span>
                        </div>
                        <h2 class="font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl">
                            {{ $relatedByService ? 'Related projects' : 'More from our portfolio' }}
                        </h2>
                    </div>
                    <a href="{{ route('projects.index') }}"
                        class="hidden shrink-0 items-center gap-1.5 text-sm font-semibold text-equator-bright transition-colors hover:text-equator-dark sm:inline-flex">
                        All projects
                        <i class="bi bi-arrow-right text-xs"></i>
                    </a>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($related as $r)
                        @php
                            $rStatus =
                                [
                                    'completed' => 'bg-emerald-500',
                                    'ongoing' => 'bg-blue-500',
                                    'planned' => 'bg-amber-500',
                                ][$r->status] ?? 'bg-slate-400';
                            $rMeta = collect([$r->start_date?->format('Y'), $r->client_name, $r->country])
                                ->filter()
                                ->implode(' · ');
                        @endphp
                        <a href="{{ route('projects.show', $r->slug) }}"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_18px_40px_-20px_rgba(38,53,146,0.3)]">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                {{-- Fallback layer (always behind) — shows if image is missing --}}
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/25">
                                    <i class="bi bi-folder2-open text-3xl"></i>
                                </div>
                                @if ($r->featured_image)
                                    <img src="{{ asset('storage/' . $r->featured_image) }}" alt="{{ $r->name }}"
                                        loading="lazy" onerror="this.remove()"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="{{ $rStatus }} h-1.5 w-1.5 rounded-full"></span>
                                    <span
                                        class="text-[0.6rem] font-bold uppercase tracking-[0.14em] text-slate-400">{{ ucfirst($r->status) }}</span>
                                </div>
                                <h3
                                    class="line-clamp-2 font-heading text-base font-semibold leading-snug text-equator-dark transition-colors group-hover:text-equator-bright">
                                    {{ $r->name }}
                                </h3>
                                @if ($rMeta)
                                    <p class="mt-2 text-sm text-slate-500">{{ $rMeta }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         9 — CONVERSION CTA
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-darker py-20 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-equator-bright/[0.14] blur-[90px]"></div>
        </div>
        <div class="relative mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="font-heading text-3xl font-semibold tracking-tight sm:text-4xl">
                Looking for similar expertise?
            </h2>
            <p class="mt-4 text-base leading-relaxed text-white/65">
                Tell us about your project and our specialists will show you how we can deliver.
            </p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $contactUrl }}"
                    class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)]">
                    Discuss a similar engagement
                    <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                </a>
                @if ($services->isNotEmpty())
                    <a href="{{ route('services.show', $services->first()->slug) }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-white/25 px-7 py-3.5 text-sm font-bold text-white transition-colors duration-300 hover:bg-white/10">
                        Explore this capability
                    </a>
                @endif
            </div>
            <div class="mt-10">
                <a href="{{ route('projects.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-white/55 transition-colors hover:text-white">
                    <i class="bi bi-arrow-left"></i>
                    Back to all projects
                </a>
            </div>
        </div>
    </section>

@endsection
