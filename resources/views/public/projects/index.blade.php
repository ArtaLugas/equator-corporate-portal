@extends('layouts.public')

@section('title', 'Projects — ' . app_setting('company_name', 'Equator Group'))

@section('meta_description', 'Explore selected projects and case studies delivered by ' . app_setting('company_name', 'Equator Group') . ' across environmental, social and ESG engagements worldwide.')

@section('content')

    @php
        use Illuminate\Support\Str;

        $activeStatus = request('status');
        $activeYear = request('year');
        $activeCountry = request('country');
        $activeServiceSlug = request('service');
        $searchTerm = trim((string) request('search'));
        $hasFilter = $searchTerm !== '' || $activeStatus || $activeYear || $activeCountry || $activeServiceSlug;

        $statusMeta = [
            'completed' => [
                'label' => 'Completed',
                'dot' => 'bg-emerald-500',
                'pill' => 'bg-emerald-50 text-emerald-700',
            ],
            'ongoing' => ['label' => 'Ongoing', 'dot' => 'bg-blue-500', 'pill' => 'bg-blue-50 text-blue-700'],
        ];

        // Card meta-line builder (year · client · location) — all real, all populated.
        $metaLine = function ($project) {
            $year = $project->start_date ? $project->start_date->format('Y') : null;
            return collect([$year, $project->client_name, $project->country])
                ->filter()
                ->implode(' · ');
        };
    @endphp

    {{-- ════════════════════════════════════════════════════════════
         HERO — authority statement with embedded proof of scale
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark text-white">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-equator-darker/50 to-equator-dark"></div>
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/[0.05] blur-[80px]"></div>
            <div class="absolute -bottom-32 left-10 h-80 w-80 rounded-full bg-equator-bright/[0.12] blur-[90px]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-white/55">
                <a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a>
                <span>/</span>
                <span class="text-white/85">Projects</span>
            </nav>

            <div class="max-w-3xl">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-equator-orange">Proven
                        Delivery</span>
                </div>
                <h1 class="font-heading text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.5rem]">
                    A track record that speaks for itself
                </h1>
                <p class="mt-6 text-lg leading-relaxed text-white/70">
                    Social and environmental work delivered for clients across industries — evidence of capability, not just
                    a catalogue.
                </p>
            </div>

            {{-- Proof stats — integrated into the hero, not a generic stat block --}}
            <dl class="mt-12 grid max-w-3xl grid-cols-2 gap-x-8 gap-y-8 border-t border-white/10 pt-10 sm:grid-cols-4">
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-white/45">Projects delivered</dt>
                    <dd class="mt-2 font-heading text-3xl font-bold tracking-tight sm:text-4xl">{{ $stats['total'] }}</dd>
                </div>
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-white/45">Completed</dt>
                    <dd class="mt-2 font-heading text-3xl font-bold tracking-tight sm:text-4xl">{{ $stats['completed'] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-white/45">Clients served</dt>
                    <dd class="mt-2 font-heading text-3xl font-bold tracking-tight sm:text-4xl">{{ $stats['clients'] }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         DISCOVERY + PORTFOLIO
    ════════════════════════════════════════════════════════════ --}}
    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Section header --}}
            <div class="mb-8">
                <div class="mb-4 flex items-center gap-3">
                    <span class="h-px w-8 bg-equator-orange"></span>
                    <span class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400">The Portfolio</span>
                </div>
                <h2 class="font-heading text-2xl font-semibold tracking-tight text-equator-dark sm:text-3xl">
                    @if ($hasFilter)
                        {{ $projects->total() }} {{ Str::plural('project', $projects->total()) }} found
                    @else
                        Explore our work
                    @endif
                </h2>
            </div>

            {{-- Discovery toolbar — status as primary segmented control, search + facets inline --}}
            <form method="GET" id="project-discovery"
                class="mb-10 flex flex-col gap-4 border-b border-slate-200 pb-6 lg:flex-row lg:items-center lg:justify-between">

                {{-- Status segmented control --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <a href="{{ route('projects.index', array_filter(['search' => $searchTerm ?: null, 'year' => $activeYear, 'country' => $activeCountry, 'service' => $activeServiceSlug])) }}"
                        @class([
                            'rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                            'bg-equator-dark text-white' => !$activeStatus,
                            'bg-slate-100 text-slate-600 hover:bg-slate-200' => $activeStatus,
                        ])>All</a>
                    @foreach ($statusMeta as $key => $meta)
                        <a href="{{ route('projects.index', array_filter(['status' => $key, 'search' => $searchTerm ?: null, 'year' => $activeYear, 'country' => $activeCountry, 'service' => $activeServiceSlug])) }}"
                            @class([
                                'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                                'bg-equator-dark text-white' => $activeStatus === $key,
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $activeStatus !== $key,
                            ])>
                            <span class="{{ $meta['dot'] }} h-1.5 w-1.5 rounded-full"></span>
                            {{ $meta['label'] }}
                        </a>
                    @endforeach
                </div>

                {{-- Search + facets --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Capability Finder — expertise as primary discovery axis --}}
                    <x-public.capability-filter :groups="$serviceGroups" :active="$activeService" />

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="bi bi-search text-xs"></i>
                        </div>
                        <input type="search" name="search" value="{{ $searchTerm }}" placeholder="Search projects…"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-equator-text placeholder-slate-400 transition-colors hover:bg-slate-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-dark/15 sm:w-52 [&::-webkit-search-cancel-button]:appearance-none">
                    </div>

                    {{-- Year facet — all projects have dates, so this is always useful --}}
                    @if ($years->isNotEmpty())
                        <select name="year" onchange="this.form.submit()"
                            class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-equator-text transition-colors hover:bg-slate-100 focus:border-equator-dark focus:outline-none">
                            <option value="">All years</option>
                            @foreach ($years as $y)
                                <option value="{{ $y }}"
                                    {{ (string) $activeYear === (string) $y ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    {{-- Country facet — only shown when more than one country exists --}}
                    @if ($countries->count() > 1)
                        <select name="country" onchange="this.form.submit()"
                            class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-equator-text transition-colors hover:bg-slate-100 focus:border-equator-dark focus:outline-none">
                            <option value="">All regions</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c }}" {{ $activeCountry === $c ? 'selected' : '' }}>
                                    {{ $c }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if ($activeStatus)
                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                    @endif
                    @if ($activeServiceSlug)
                        <input type="hidden" name="service" value="{{ $activeServiceSlug }}">
                    @endif

                    <button type="submit"
                        class="rounded-lg bg-equator-dark px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-equator-bright">
                        Search
                    </button>

                    @if ($hasFilter)
                        <a href="{{ route('projects.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700">
                            <i class="bi bi-x-lg text-xs"></i> Clear
                        </a>
                    @endif
                </div>
            </form>

            {{-- Portfolio grid — bento: first card spans wide for editorial rhythm --}}
            @if ($projects->isEmpty())
                <div
                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-50/50 py-24 text-center">
                    <div
                        class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-300">
                        <i class="bi bi-folder2-open text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold text-equator-dark">No projects found</h3>
                    <p class="mt-1.5 max-w-sm text-sm text-slate-500">Try a different year, status, or clear your search.
                    </p>
                    <a href="{{ route('projects.index') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright">
                        Reset filters
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        @php
                            $meta = $statusMeta[$project->status] ?? [
                                'label' => ucfirst($project->status),
                                'dot' => 'bg-slate-400',
                                'pill' => 'bg-slate-100 text-slate-600',
                            ];
                        @endphp
                        <a href="{{ route('projects.show', $project->slug) }}"
                            class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[0_18px_40px_-20px_rgba(38,53,146,0.3)]">
                            <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                                @if ($project->featured_image)
                                    <img src="{{ asset('storage/' . $project->featured_image) }}"
                                        alt="{{ $project->name }}" loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/25">
                                        <i class="bi bi-folder2-open text-4xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <div class="mb-3 flex items-center gap-2">
                                    <span
                                        class="{{ $meta['pill'] }} inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                                        <span
                                            class="{{ $meta['dot'] }} h-1.5 w-1.5 rounded-full"></span>{{ $meta['label'] }}
                                    </span>
                                </div>
                                <h3
                                    class="line-clamp-2 font-heading text-base font-bold leading-snug text-equator-dark transition-colors group-hover:text-equator-bright">
                                    {{ $project->name }}
                                </h3>
                                <p class="mt-3 text-sm text-slate-500">{{ $metaLine($project) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($projects->hasPages())
                    <div class="mt-12">{{ $projects->links() }}</div>
                @endif
            @endif
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         CAPABILITY CTA — turn proof into a conversation
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-equator-dark py-20 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-equator-bright/[0.14] blur-[90px]"></div>
        </div>
        <div class="relative mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="font-heading text-3xl font-semibold tracking-tight sm:text-4xl">
                See the capability behind the work
            </h2>
            <p class="mt-4 text-base leading-relaxed text-white/65">
                Every project draws on a defined set of services. Explore what we do — or start a conversation about yours.
            </p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('services.index') }}"
                    class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)]">
                    Explore our services
                    <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                </a>
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-white/25 px-7 py-3.5 text-sm font-bold text-white transition-colors duration-300 hover:bg-white/10">
                    Start a conversation
                </a>
            </div>
        </div>
    </section>

@endsection
