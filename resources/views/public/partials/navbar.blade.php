@php
    $navLinks = [
        ['label' => __('nav.home'), 'route' => 'home', 'active' => request()->routeIs('home')],
        ['label' => __('nav.about'), 'route' => 'about', 'active' => request()->routeIs('about')],
        ['label' => __('nav.services'), 'route' => 'services.index', 'active' => request()->routeIs('services.*')],
        ['label' => __('nav.projects'), 'route' => 'projects.index', 'active' => request()->routeIs('projects.*')],
        [
            'label' => __('nav.foundation'),
            'url' => 'https://lpm.equatorgroup.id',
            'external' => true,
            'active' => false,
        ],
        ['label' => __('nav.news'), 'route' => 'news.index', 'active' => request()->routeIs('news.*')],
        ['label' => __('nav.faq'), 'route' => 'faq', 'active' => request()->routeIs('faq')],
    ];
@endphp

<header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 20"
    class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
    :class="scrolled || open ? 'bg-white shadow-md' : 'bg-white/95 backdrop-blur'">

    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:h-20 lg:px-8">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            @if (app_setting('logo'))
                <img src="{{ asset('storage/' . app_setting('logo')) }}"
                    alt="{{ app_setting('company_name', 'Equator Group') }}" class="h-9 w-auto object-contain lg:h-11">
            @else
                <span
                    class="text-xl font-black tracking-tight text-equator-dark">{{ app_setting('company_name', 'Equator Group') }}</span>
            @endif
        </a>

        {{-- DESKTOP MENU --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] ?? route($link['route']) }}"
                    @if (!empty($link['external'])) target="_blank"
            rel="noopener noreferrer" @endif
                    class="{{ $link['active'] ? 'text-equator-dark' : 'text-gray-600 hover:text-equator-dark' }} relative px-4 py-2 text-sm font-bold transition-colors">

                    {{ $link['label'] }}

                    @if ($link['active'])
                        <span class="absolute inset-x-4 -bottom-0.5 h-0.5 rounded-full bg-equator-bright"></span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2 sm:gap-3">

            {{-- LANGUAGE SWITCHER (desktop) --}}
            <div class="hidden lg:block">
                <x-public.lang-switcher />
            </div>

            <a href="{{ route('contact') }}" data-track="cta" data-track-label="nav_contact"
                class="hidden rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition hover:bg-equator-bright lg:inline-flex">
                {{ __('nav.contact_us') }}
            </a>

            {{-- MOBILE TOGGLE --}}
            <button @click="open = !open" class="rounded-lg p-2 text-equator-dark lg:hidden"
                aria-label="{{ __('nav.menu') }}">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <line x1="4" x2="20" y1="6" y2="6" />
                    <line x1="4" x2="20" y1="12" y2="12" />
                    <line x1="4" x2="20" y1="18" y2="18" />
                </svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </div>
    </nav>

    {{-- MOBILE MENU --}}
    <div x-show="open" x-collapse x-cloak class="border-t border-gray-100 bg-white lg:hidden">
        <div class="space-y-1 px-4 py-4">
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] ?? route($link['route']) }}"
                    @if ($link['external'] ?? false) target="_blank"
            rel="noopener noreferrer" @endif
                    class="{{ $link['active'] ? 'bg-equator-dark/5 text-equator-dark' : 'text-gray-600' }} block rounded-xl px-4 py-3 text-sm font-bold">

                    {{ $link['label'] }}
                </a>
            @endforeach

            <a href="{{ route('contact') }}"
                class="mt-2 block rounded-xl bg-equator-dark px-4 py-3 text-center text-sm font-bold text-white">
                {{ __('nav.contact_us') }}
            </a>

            {{-- LANGUAGE SWITCHER (mobile) --}}
            @php($switcherLocales = config('locales.supported', []))
            @if (count($switcherLocales) > 1)
                <div class="mt-3 border-t border-gray-100 pt-3">
                    <p class="px-4 pb-1 text-[0.65rem] font-bold uppercase tracking-wider text-gray-400">
                        {{ __('nav.language') }}</p>
                    <div class="flex gap-2 px-1">
                        @foreach ($switcherLocales as $code => $meta)
                            <a href="{{ locale_url($code) }}" hreflang="{{ $code }}"
                                class="{{ $code === app()->getLocale() ? 'bg-equator-dark text-white' : 'bg-gray-50 text-gray-600' }} flex-1 rounded-xl px-3 py-2.5 text-center text-sm font-bold">
                                {{ $meta['native'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>

{{-- Spacer for fixed header --}}
<div class="h-16 lg:h-20"></div>
