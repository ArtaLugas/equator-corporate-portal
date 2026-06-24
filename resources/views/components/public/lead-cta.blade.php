@props(['heading', 'body', 'ctaLabel', 'ctaHref'])

{{--
    Closing contextual CTA band — shared CTA for Service and Project detail pages.
--}}
<section class="relative overflow-hidden bg-equator-dark py-20 text-white sm:py-24">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-equator-bright/15 blur-[90px]"></div>
    </div>

    <div class="relative mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="font-heading text-3xl font-semibold tracking-tight sm:text-4xl">
            {{ $heading }}
        </h2>

        <p class="mt-4 text-base leading-relaxed text-white/65">
            {{ $body }}
        </p>

        <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ $ctaHref }}"
                class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-white px-8 py-4 text-sm font-bold text-equator-dark shadow-[0_8px_24px_-8px_rgba(0,0,0,0.4)] transition-all duration-300 hover:shadow-[0_16px_36px_-10px_rgba(0,0,0,0.5)] focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                {{ $ctaLabel }}

                <i class="bi bi-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"
                    aria-hidden="true"></i>
            </a>

            {{ $slot }}
        </div>

        @isset($back)
            <div class="mt-10">
                {{ $back }}
            </div>
        @endisset
    </div>
</section>
