@extends('layouts.public')

@section('title', 'Contact Us — ' . app_setting('company_name', 'Equator Group'))

@push('head')
    {{-- Cloudflare Turnstile API Core --}}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endpush

@section('content')

    {{-- SYSTEM NO-JS FALLBACK
         Memastikan jika JavaScript dinonaktifkan di browser klien, seluruh data kantor
         dan peta tetap tampil secara berurutan tanpa terkunci oleh state Alpine --}}
    <noscript>
        <style>
            .js-dependent-tab {
                display: block !important;
                opacity: 1 !important;
                transform: none !important;
            }

            .js-dependent-nav {
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
        'title' => 'Contact Us',
        'subtitle' => 'Tell us about your project, and our consultants will respond within one business day.',
    ])

    <section class="relative bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Dua pilar setara: pemisahan lewat whitespace + tint halus (tanpa garis pemisah). --}}
            <div class="grid grid-cols-1 gap-x-12 gap-y-16 lg:grid-cols-12 lg:items-start lg:gap-x-20">

                {{-- ============================ PILAR KIRI: OFFICES ============================ --}}
                {{-- order-2 di mobile agar FORM (aksi utama) tampil lebih dulu; kembali ke kiri pada lg --}}
                <div class="order-2 flex flex-col lg:order-1 lg:col-span-5" x-data="{ activeOffice: 0 }">

                    <div class="max-w-md">
                        <div class="flex items-center gap-4">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Our offices</span>
                        </div>
                        <h2 class="mt-6 font-heading text-2xl font-light tracking-tight text-slate-900 sm:text-3xl">
                            Reach our team
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
                            Contact a regional office directly, or use the form and we'll route your message to the
                            right team.
                        </p>
                    </div>

                    {{-- CASE 1: MULTI-OFFICE ENGINE (CMS MODE) --}}
                    @if ($offices->isNotEmpty())
                        <div class="mt-10">

                            {{-- Tab Selector minimalis (underline) — hanya muncul jika kantor > 1 --}}
                            @if ($offices->count() > 1)
                                <nav class="js-dependent-nav -mb-px flex flex-wrap gap-x-6 border-b border-slate-200"
                                    role="tablist" aria-label="Office locations"
                                    @keydown.arrow-right.prevent="activeOffice = (activeOffice + 1) % {{ $offices->count() }}; $refs['tab' + activeOffice].focus()"
                                    @keydown.arrow-left.prevent="activeOffice = (activeOffice - 1 + {{ $offices->count() }}) % {{ $offices->count() }}; $refs['tab' + activeOffice].focus()">
                                    @foreach ($offices as $index => $office)
                                        <button type="button" role="tab" id="office-tab-{{ $index }}"
                                            x-ref="tab{{ $index }}" @click="activeOffice = {{ $index }}"
                                            :aria-selected="activeOffice === {{ $index }} ? 'true' : 'false'"
                                            :tabindex="activeOffice === {{ $index }} ? 0 : -1"
                                            aria-controls="office-panel-{{ $index }}"
                                            :class="activeOffice === {{ $index }} ?
                                                'border-equator-dark text-slate-900 font-semibold' :
                                                'border-transparent text-slate-500 hover:text-slate-800'"
                                            class="border-b-2 px-1 pb-3 pt-1 text-[11px] uppercase tracking-[0.15em] transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-dark/40 focus-visible:ring-offset-2">
                                            {{ $office->name }}
                                        </button>
                                    @endforeach
                                </nav>
                            @endif

                            {{-- Office panels: tinggi natural (tanpa min-h / absolute) --}}
                            <div class="mt-8">
                                @foreach ($offices as $index => $office)
                                    <div x-show="activeOffice === {{ $index }}" @if ($index > 0) x-cloak @endif
                                        role="tabpanel" id="office-panel-{{ $index }}"
                                        aria-labelledby="office-tab-{{ $index }}"
                                        x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="js-dependent-tab space-y-6">

                                        <div class="border border-slate-200/70 bg-white p-6 lg:p-8">
                                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                                <h3 class="font-heading text-lg font-normal text-slate-900">
                                                    {{ $office->name }}</h3>
                                                @if ($office->is_primary)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                                        <span class="h-1 w-1 rounded-full bg-equator-orange"></span>HQ
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-6 space-y-4">
                                                @if ($office->address)
                                                    <div class="flex items-start gap-3">
                                                        <x-icon name="map-pin"
                                                            class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                                            stroke-width="1.75" />
                                                        <p class="text-sm leading-relaxed text-slate-600">
                                                            {{ $office->address }}</p>
                                                    </div>
                                                @endif

                                                @if ($office->phone)
                                                    <div class="flex items-center gap-3">
                                                        <x-icon name="phone" class="h-4 w-4 shrink-0 text-slate-400"
                                                            stroke-width="1.75" />
                                                        <a href="tel:{{ $office->phone }}"
                                                            class="text-sm text-slate-600 transition-colors duration-300 hover:text-equator-dark">{{ $office->phone }}</a>
                                                    </div>
                                                @endif

                                                @if ($office->email)
                                                    <div class="flex items-center gap-3">
                                                        <x-icon name="mail" class="h-4 w-4 shrink-0 text-slate-400"
                                                            stroke-width="1.75" />
                                                        <a href="mailto:{{ $office->email }}"
                                                            class="text-sm text-slate-600 transition-colors duration-300 hover:text-equator-dark">{{ $office->email }}</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Google Maps Embed — hanya dimuat untuk office aktif (lazy, inert
                                             di dalam <template x-if>) + fallback no-JS. --}}
                                        @if ($office->map_embed)
                                            <template x-if="activeOffice === {{ $index }}">
                                                <div
                                                    class="overflow-hidden border border-slate-200/70 bg-slate-100 [&_iframe]:h-64 [&_iframe]:w-full [&_iframe]:border-0">
                                                    {!! $office->map_embed !!}
                                                </div>
                                            </template>
                                            <noscript>
                                                <div
                                                    class="overflow-hidden border border-slate-200/70 bg-slate-100 [&_iframe]:h-64 [&_iframe]:w-full [&_iframe]:border-0">
                                                    {!! $office->map_embed !!}
                                                </div>
                                            </noscript>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- CASE 2: Belum ada Office Location aktif. --}}
                    @else
                        <div class="mt-10 border border-slate-200/70 bg-white p-6 text-sm text-slate-500 lg:p-8">
                            Office details will appear here once a location is added. Meanwhile, please reach us using
                            the form.
                        </div>
                    @endif

                    {{-- Social Links — brand color muncul saat hover (via data-brand-color + JS,
                         menghindari Tailwind purging untuk warna dinamis). Light background. --}}
                    @if ($socials->isNotEmpty())
                        <div class="mt-10 border-t border-slate-100 pt-6">
                            <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Connect with us
                            </p>
                            <div class="flex flex-wrap gap-2" id="contact-socials">
                                @foreach ($socials as $social)
                                    <a href="{{ $social->url }}" target="_blank" rel="noopener"
                                        title="{{ $social->brand_label }}" aria-label="{{ $social->brand_label }}"
                                        data-brand-color="{{ $social->brand_color }}"
                                        class="social-brand-btn flex h-10 w-10 items-center justify-center border border-slate-200 text-slate-500 transition-all duration-300 hover:border-slate-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-dark/40 focus-visible:ring-offset-2">
                                        <i class="bi bi-{{ $social->icon_class ?: 'link-45deg' }} text-base"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ============================ PILAR KANAN: FORM ============================ --}}
                {{-- order-1 di mobile agar form tampil lebih dulu; kembali ke kanan pada lg --}}
                <div class="order-1 flex flex-col lg:order-2 lg:col-span-7">

                    <div class="max-w-xl">
                        <div class="flex items-center gap-4">
                            <span class="h-px w-8 bg-equator-orange"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Get in touch</span>
                        </div>
                        <h2 class="mt-6 font-heading text-2xl font-light tracking-tight text-slate-900 sm:text-3xl">
                            Send us a message
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
                            Tell us briefly what you need, and the right team will get back to you.
                        </p>
                    </div>

                    {{-- Form panel: tint slate-50 nyaris tak terlihat + hairline, tanpa shadow --}}
                    <div class="mt-10 border border-slate-200/70 bg-slate-50 p-6 sm:p-10 lg:p-12"
                        x-data="{ submitting: false }">

                        {{-- Success — konfirmasi elegan, tenang, selaras brand --}}
                        @if (session('success'))
                            <div role="status" aria-live="polite"
                                class="mb-8 flex items-start gap-3 border border-slate-200 bg-white px-5 py-4">
                                <span
                                    class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <x-icon name="check" class="h-3.5 w-3.5" stroke-width="3" />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Message sent</p>
                                    <p class="mt-0.5 text-sm leading-relaxed text-slate-600">{{ session('success') }}</p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('contact.store') }}" method="POST" @submit="submitting = true"
                            class="space-y-6">
                            @csrf

                            {{-- High-Integrity Honeypot Fields --}}
                            <div class="hidden" aria-hidden="true">
                                <label for="sys_website_verification">Website Security Filter</label>
                                <input type="text" id="sys_website_verification" name="website" tabindex="-1"
                                    autocomplete="off">
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <x-public.field name="name" label="Full Name" required autocomplete="name" />
                                <x-public.field name="email" label="Work Email" type="email" required
                                    autocomplete="email" />
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <x-public.field name="phone" label="Phone Number" type="tel" optional
                                    autocomplete="tel" />
                                <x-public.field name="company" label="Company" optional autocomplete="organization" />
                            </div>

                            <x-public.field name="subject" label="Subject" required />

                            <x-public.field name="message" label="How can we help?" textarea rows="6" required
                                minlength="10" hint="Please provide at least 10 characters." />

                            {{-- Cloudflare Turnstile CAPTCHA --}}
                            <div class="relative py-2">
                                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                                    data-theme="light" data-language="en"></div>
                                @error('cf-turnstile-response')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Submit --}}
                            <div class="pt-2">
                                <button type="submit" :disabled="submitting"
                                    class="group inline-flex w-full items-center justify-center gap-3 bg-equator-dark px-8 py-4 text-xs font-bold uppercase tracking-[0.2em] text-white transition-colors duration-300 hover:bg-equator-orange focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-dark/40 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-400">
                                    <span x-text="submitting ? 'Sending…' : 'Send Message'">Send Message</span>

                                    {{-- Ikon Lucide harus ada di DOM saat load → pakai x-show (bukan x-if) --}}
                                    <span x-show="!submitting" class="inline-flex">
                                        <x-icon name="arrow-right"
                                            class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                                    </span>
                                    <span x-show="submitting" x-cloak class="inline-flex">
                                        <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                </button>

                                {{-- What happens next (versi ringkas) --}}
                                <p class="mt-5 text-center text-xs leading-relaxed text-slate-500">
                                    Submit <span class="text-slate-300" aria-hidden="true">&rarr;</span> routed to the
                                    right team <span class="text-slate-300" aria-hidden="true">&rarr;</span> reply within
                                    one business day
                                </p>

                                {{-- Privacy reassurance --}}
                                <p class="mt-2 flex items-center justify-center gap-2 text-center text-xs text-slate-500">
                                    <x-icon name="shield-check" class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                        stroke-width="1.75" />
                                    <span>Your details stay confidential and are never shared.</span>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Social icon brand-color hover (light background variant) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('contact-socials');
            if (!container) return;

            container.querySelectorAll('.social-brand-btn').forEach(btn => {
                const color = btn.dataset.brandColor;
                if (!color) return;

                // Light bg: brand color is solid on the icon, with a soft brand-tinted
                // border + faint background wash. Inline styles are required because
                // Tailwind can't emit dynamically-generated color classes at build time.
                const borderHover = color + '66'; // ~40% opacity
                const bgHover     = color + '0F'; // ~6% opacity wash

                btn.addEventListener('mouseenter', () => {
                    btn.style.color           = color;
                    btn.style.borderColor     = borderHover;
                    btn.style.backgroundColor = bgHover;
                });

                btn.addEventListener('mouseleave', () => {
                    btn.style.color           = '';
                    btn.style.borderColor     = '';
                    btn.style.backgroundColor = '';
                });
            });
        });
    </script>

@endsection
