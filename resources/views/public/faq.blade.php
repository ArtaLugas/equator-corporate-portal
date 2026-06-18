@extends('layouts.public')

@section('title', 'Frequently Asked Questions — ' . app_setting('company_name', 'Equator Group'))

@section('content')

    {{-- FAQPage structured data (SEO rich result) — head-only, no visual change.
         JSON_HEX_TAG dkk mencegah teks jawaban membobol blok <script>. --}}
    @if ($faqs->isNotEmpty())
        @push('head')
            <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                ])->values()->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS) !!}
            </script>
        @endpush
    @endif

    {{-- SYSTEM NO-JS FALLBACK
         Menjamin jika JavaScript klien terblokir, seluruh jawaban FAQ tetap
         terbuka secara otomatis dan dapat diindeks sempurna oleh mesin pencari --}}
    <noscript>
        <style>
            .js-accordion-panel {
                display: block !important;
                height: auto !important;
                opacity: 1 !important;
            }

            .js-accordion-icon {
                display: none !important;
            }

            [x-cloak] {
                display: block !important;
            }
        </style>
    </noscript>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- HERO BLOCK INTEGRATION --}}
    @include('public.partials.page-hero', [
        'title' => 'Frequently Asked Questions',
        'subtitle' =>
            'Answers to common questions about our services, expertise, and approach to client engagements.',
    ])

    <section class="relative bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 gap-y-12 lg:grid-cols-12 lg:items-start lg:gap-x-16">

                {{-- ============================ LEFT: STATIC CONTEXT RAIL ============================ --}}
                {{-- Tenang & berisi — tanpa callout box. CTA tunggal ada di closing rail (kolom kanan). --}}
                <div class="lg:col-span-4">
                    <div class="lg:max-w-xs">
                        <div class="flex items-center gap-4">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Common
                                Questions</span>
                        </div>
                        <h2 class="mt-6 font-heading text-2xl font-light tracking-tight text-slate-900 sm:text-3xl">
                            What clients ask us most
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
                            Clear answers about our services, expertise, and how we work. Can't find what you're
                            looking for? Our team is one message away.
                        </p>
                    </div>
                </div>

                {{-- ============================ RIGHT: ACCORDION + CLOSING RAIL ============================ --}}
                <div class="lg:col-span-8">
                    @if ($faqs->isEmpty())
                        <div class="border border-dashed border-slate-200 py-24 text-center">
                            <div class="mb-4 flex justify-center">
                                <x-icon name="folder-open" class="h-8 w-8 text-slate-300" stroke-width="1.5" />
                            </div>
                            <p class="text-base font-light text-slate-400">No questions have been published yet.</p>
                        </div>
                    @else
                        {{-- Minimalist architectural border stack --}}
                        <div class="divide-y divide-slate-200 border-b border-t border-slate-200">
                            @foreach ($faqs as $index => $faq)
                                <div x-data="{ open: false }" class="py-6 first:pt-0 last:pb-0">

                                    <h3>
                                        <button type="button" id="faq-button-{{ $index }}" @click="open = !open"
                                            :aria-expanded="open ? 'true' : 'false'"
                                            aria-controls="faq-panel-{{ $index }}"
                                            class="group flex w-full items-start justify-between gap-6 rounded-sm text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-dark/40 focus-visible:ring-offset-2">

                                            <span
                                                class="font-heading text-lg font-normal tracking-tight text-slate-900 transition-colors duration-300 group-hover:text-equator-dark">
                                                {{ $faq->question }}
                                            </span>

                                            {{-- Minimalist line-based toggle indicator --}}
                                            <span aria-hidden="true"
                                                class="js-accordion-icon relative mt-1.5 flex h-4 w-4 shrink-0 items-center justify-center">
                                                <span
                                                    class="absolute h-0.5 w-4 bg-slate-400 transition-transform duration-300"
                                                    :class="open && 'rotate-90'"></span>
                                                <span
                                                    class="absolute h-4 w-0.5 bg-slate-400 transition-transform duration-300"
                                                    :class="open && 'rotate-90 opacity-0'"></span>
                                            </span>
                                        </button>
                                    </h3>

                                    {{-- Smooth transition panel content --}}
                                    <div id="faq-panel-{{ $index }}" x-show="open" x-collapse x-cloak
                                        class="js-accordion-panel" role="region"
                                        aria-labelledby="faq-button-{{ $index }}">

                                        {{-- Lebar baris dibatasi ~68ch + font-normal untuk keterbacaan teks panjang --}}
                                        <div
                                            class="max-w-[68ch] pb-2 pt-4 text-base font-normal leading-relaxed text-slate-600">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Closing rail — editorial, tenang (bukan card/banner) --}}
                        <div class="mt-12 border-t border-slate-200 pt-8">
                            <p class="font-heading text-base font-medium text-slate-900">Still have questions?</p>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600">
                                For questions tied to your specific scope, timeline, or requirements, speak directly
                                with one of our consultants.
                            </p>
                            <a href="{{ route('contact') }}"
                                class="group mt-4 inline-flex items-center gap-2 text-sm font-semibold text-equator-dark transition-colors duration-300 hover:text-equator-orange">
                                Contact us
                                <x-icon name="arrow-right"
                                    class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

@endsection
