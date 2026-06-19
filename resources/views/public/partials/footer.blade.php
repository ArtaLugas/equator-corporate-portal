@php
    use Illuminate\Support\Facades\Schema;

    $socials = Schema::hasTable('social_links')
        ? \App\Models\SocialLink::where('status', 'active')->orderBy('display_order')->get()
        : collect();

    $offices = Schema::hasTable('office_locations')
        ? \App\Models\OfficeLocation::active()->ordered()->get()
        : collect();
    $primaryOffice = $offices->firstWhere('is_primary', true) ?? $offices->first();

    $footerAddress = $primaryOffice?->address;
    $footerPhone = $primaryOffice?->phone;
    $footerEmail = $primaryOffice?->email;
@endphp

<footer class="bg-slate-950 text-white">

    {{-- ════════════════════════════════════════════════════════
         ZONE 1 — BRAND MANIFESTO
    ════════════════════════════════════════════════════════ --}}
    <div class="mx-auto max-w-7xl px-6 pb-16 pt-20 sm:pt-24 lg:px-8">
        <div class="grid items-start gap-14 lg:grid-cols-12 lg:gap-x-16">

            {{-- Left: Tagline + CTA --}}
            <div class="footer-reveal lg:col-span-7" data-footer-delay="0">
                <div class="flex items-center gap-3">
                    <span class="h-px w-6 bg-equator-orange" aria-hidden="true"></span>
                    <span class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-white/55">
                        {{ app_setting('company_name', 'Equator Group') }}
                    </span>
                </div>

                <p
                    class="mt-6 font-heading text-4xl font-semibold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-[3.25rem]">
                    {{ app_setting('tagline', 'Safeguarding people, planet, prosperity, and principles.') }}
                </p>

                <div class="mt-10">
                    <a href="{{ route('contact') }}"
                        class="group inline-flex items-center gap-3 text-sm font-medium text-white/70 transition-all duration-300 hover:text-white">
                        <span class="h-px w-8 bg-equator-orange transition-all duration-300 group-hover:w-14"></span>
                        <span>Start a conversation</span>
                        <i class="bi bi-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1"
                            aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            {{-- Right: Logo + description + social icons --}}
            <div class="footer-reveal lg:col-span-5 lg:pt-1" data-footer-delay="100">

                @if (app_setting('logo'))
                    <img src="{{ asset('storage/' . app_setting('logo')) }}"
                        alt="{{ app_setting('company_name', 'Equator Group') }}"
                        class="h-10 w-auto object-contain brightness-0 invert">
                @else
                    <span class="font-heading text-base font-semibold text-white/70">
                        {{ app_setting('company_name', 'Equator Group') }}
                    </span>
                @endif

                <p class="mt-5 max-w-xs text-sm leading-7 text-white/90">
                    A social and environmental advisory firm dedicated to managing risk and delivering sustainable
                    impact across industries and geographies.
                </p>

                {{-- Social icons — brand color on hover via data attribute (bypasses Tailwind purging) --}}
                @if ($socials->isNotEmpty())
                    <div class="mt-7 flex items-center gap-2.5">
                        @foreach ($socials as $social)
                            <a href="{{ $social->url }}" target="_blank" rel="noopener"
                                class="social-brand-btn flex h-9 w-9 items-center justify-center rounded-lg border border-white/15 text-white/80 transition-all duration-200"
                                data-brand-color="{{ $social->brand_color }}" aria-label="{{ $social->brand_label }}"
                                title="{{ $social->brand_label }}">
                                <i class="bi bi-{{ $social->icon_class ?: 'link-45deg' }} text-sm"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>

        </div>
    </div>

    {{-- Separator --}}
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="h-px bg-gradient-to-r from-transparent via-white/[0.12] to-transparent"></div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         ZONE 2 — NAVIGATION + CONTACT
    ════════════════════════════════════════════════════════ --}}
    <div class="mx-auto max-w-7xl px-6 pb-10 pt-14 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-x-16">

            {{-- Navigation: Explore + Practice Areas --}}
            <div class="grid grid-cols-2 gap-10 lg:col-span-7">

                {{-- Explore --}}
                <div class="footer-reveal" data-footer-delay="60">
                    <p class="mb-5 text-[0.62rem] font-semibold uppercase tracking-[0.24em] text-white">Explore</p>
                    <ul class="space-y-3">
                        <li>
                            <a href="{{ route('about') }}"
                                class="text-sm text-white/90 transition-colors duration-200 hover:text-white">About
                                Us</a>
                        </li>
                        <li>
                            <a href="{{ route('services.index') }}"
                                class="text-sm text-white/90 transition-colors duration-200 hover:text-white">Services</a>
                        </li>
                        <li>
                            <a href="{{ route('projects.index') }}"
                                class="text-sm text-white/90 transition-colors duration-200 hover:text-white">Projects</a>
                        </li>
                        <li>
                            <a href="{{ route('news.index') }}"
                                class="text-sm text-white/90 transition-colors duration-200 hover:text-white">News</a>
                        </li>
                        <li>
                            <a href="{{ route('faq') }}"
                                class="text-sm text-white/90 transition-colors duration-200 hover:text-white">FAQ</a>
                        </li>
                    </ul>
                </div>

                {{-- Practice Areas --}}
                <div class="footer-reveal" data-footer-delay="110">
                    <p class="mb-5 text-[0.62rem] font-semibold uppercase tracking-[0.24em] text-white">Practice
                        Areas</p>
                    <ul class="space-y-3">
                        @foreach (\App\Models\ServiceCategory::where('status', 'active')->orderBy('display_order')->take(5)->get() as $cat)
                            <li>
                                <a href="{{ route('services.index', ['category' => $cat->slug]) }}"
                                    class="text-sm text-white/90 transition-colors duration-200 hover:text-white">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            {{-- Contact: featured email + label-value rows --}}
            <div class="footer-reveal lg:col-span-5" data-footer-delay="70">
                <p class="mb-5 text-[0.62rem] font-semibold uppercase tracking-[0.24em] text-white">Contact</p>

                @if ($footerEmail)
                    <a href="mailto:{{ $footerEmail }}" class="group block">
                        <span
                            class="block font-heading text-lg font-medium tracking-tight text-white/90 transition-colors duration-300 group-hover:text-white sm:text-xl">
                            {{ $footerEmail }}
                        </span>
                        <span
                            class="mt-1.5 block h-px w-full bg-white/15 transition-all duration-500 group-hover:bg-white/35"></span>
                    </a>
                @endif

                <div class="mt-7 space-y-4">
                    @if ($footerPhone)
                        <div class="flex items-center gap-3">
                            <span
                                class="w-16 shrink-0 text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-white/90">Phone</span>
                            <span class="h-px w-3 shrink-0 bg-white/90" aria-hidden="true"></span>
                            <a href="tel:{{ $footerPhone }}"
                                class="text-sm text-white/90 transition-colors duration-200 hover:text-white">
                                {{ $footerPhone }}
                            </a>
                        </div>
                    @endif

                    @if ($footerAddress)
                        <div class="flex items-start gap-3">
                            <span
                                class="mt-0.5 w-16 shrink-0 text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-white/90">Location</span>
                            <span class="mt-2 h-px w-3 shrink-0 bg-white/90" aria-hidden="true"></span>
                            <span
                                class="whitespace-pre-line text-sm leading-6 text-white/90">{{ $footerAddress }}</span>
                        </div>
                    @endif

                    @if ($offices->count() > 1)
                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center gap-1.5 text-[0.72rem] font-semibold text-equator-orange/80 transition-colors duration-200 hover:text-equator-orange">
                            All {{ $offices->count() }} offices
                            <i class="bi bi-arrow-right text-[0.6rem]" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Copyright bar --}}
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div
            class="flex flex-col items-start justify-between gap-2 border-t border-white/[0.08] py-6 sm:flex-row sm:items-center">
            <p class="text-[0.7rem] text-white/70">
                &copy; {{ date('Y') }} {{ app_setting('company_name', 'Equator Group') }}. All rights reserved.
            </p>
            <p class="text-[0.7rem] text-white/70">
                Empowering sustainable &amp; resilient development.
            </p>
        </div>
    </div>

</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const footer = document.querySelector('footer');
        if (!footer) return;

        // ── Scroll entrance animation ──────────────────────────────────────
        if (!prefersReduced) {
            const reveals = footer.querySelectorAll('.footer-reveal');

            reveals.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(14px)';
                el.style.transition =
                    'opacity 650ms cubic-bezier(0.22,1,0.36,1), transform 650ms cubic-bezier(0.22,1,0.36,1)';
            });

            const io = new IntersectionObserver(entries => {
                if (!entries[0].isIntersecting) return;
                reveals.forEach(el => {
                    const delay = parseInt(el.dataset.footerDelay || '0');
                    setTimeout(() => {
                        el.style.opacity = '1';
                        el.style.transform = 'none';
                    }, delay);
                });
                io.disconnect();
            }, {
                threshold: 0.04
            });

            io.observe(footer);
        }

        // ── Social icon brand-color hover ──────────────────────────────────
        // Uses data-brand-color attribute to apply brand hex via inline style.
        // Tailwind cannot include dynamically-generated color classes at build
        // time, so inline styles via JS is the correct architectural approach.
        footer.querySelectorAll('.social-brand-btn').forEach(btn => {
            const color = btn.dataset.brandColor;
            if (!color) return;

            // Pre-compute derived values once — not on every mouseover
            const borderHover = color + '55'; // ~33% opacity
            const bgHover = color + '18'; // ~9% opacity background glow
            const glowHover = color + '22'; // ~13% opacity box shadow

            btn.style.transition =
                'color 200ms ease, border-color 200ms ease, background-color 200ms ease, box-shadow 200ms ease';

            btn.addEventListener('mouseenter', () => {
                btn.style.color = color;
                btn.style.borderColor = borderHover;
                btn.style.backgroundColor = bgHover;
                btn.style.boxShadow = `0 0 14px 0 ${glowHover}`;
            });

            btn.addEventListener('mouseleave', () => {
                btn.style.color = '';
                btn.style.borderColor = '';
                btn.style.backgroundColor = '';
                btn.style.boxShadow = '';
            });
        });
    });
</script>
