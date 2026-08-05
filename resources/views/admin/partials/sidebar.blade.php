{{-- Mobile Backdrop Overlay --}}
<div x-show="sidebarOpen" x-transition.opacity.duration.300ms @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-gray-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

{{-- Sidebar Container (Flat Enterprise Design) --}}
<aside
    class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col border-r border-gray-200 bg-white transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'">
    {{-- Header / Logo --}}
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-gray-100 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            @if (app_setting('favicon'))
                <img src="{{ asset('storage/' . app_setting('favicon')) }}"
                    alt="{{ app_setting('company_name', 'Favicon') }}" class="h-8 w-8 shrink-0 rounded-lg object-contain">
            @else
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-equator-dark text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" />
                    </svg>
                </div>
            @endif
            <div class="flex min-w-0 flex-col">
                <span
                    class="truncate text-sm font-extrabold leading-none tracking-tight text-gray-900">{{ app_setting('company_name', 'Equator Group') }}</span>
                <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-500">Admin Portal</span>
            </div>
        </a>

        {{-- Mobile Close Button --}}
        <button @click="sidebarOpen = false"
            class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="custom-scrollbar flex-1 space-y-8 overflow-y-auto px-4 py-6">

        {{-- GROUP: MAIN --}}
        <div>
            <h3 class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Main</h3>
            <div class="space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.dashboard') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <rect width="7" height="9" x="3" y="3" rx="1" />
                        <rect width="7" height="5" x="14" y="3" rx="1" />
                        <rect width="7" height="9" x="14" y="12" rx="1" />
                        <rect width="7" height="5" x="3" y="16" rx="1" />
                    </svg>
                    <span class="text-sm">Dashboard</span>
                </a>

                @can('translation-progress.view')
                {{-- Translation Progress --}}
                <a href="{{ route('admin.translations.index') }}"
                    class="{{ request()->routeIs('admin.translations.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.translations.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path d="m5 8 6 6" /><path d="m4 14 6-6 2-3" /><path d="M2 5h12" /><path d="M7 2h1" />
                        <path d="m22 22-5-10-5 10" /><path d="M14 18h6" />
                    </svg>
                    <span class="text-sm">Translation Progress</span>
                </a>
                @endcan

                @canany(['service-category.view', 'service.view'])
                {{-- SERVICE MENU (Accordion) --}}
                <div x-data="{ open: {{ request()->routeIs('admin.services.*') || request()->routeIs('admin.service-categories.*') ? 'true' : 'false' }} }" class="space-y-1">

                    {{-- Parent Button --}}
                    <button type="button" @click="open = !open"
                        class="{{ request()->routeIs('admin.services.*') || request()->routeIs('admin.service-categories.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex w-full items-center justify-between rounded-xl px-3 py-2.5 transition-colors">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('admin.services.*') || request()->routeIs('admin.service-categories.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                                <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                            </svg>
                            <span class="text-sm">Services</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.services.*') || request()->routeIs('admin.service-categories.*') ? 'text-equator-dark' : 'text-gray-400' }} transition-transform duration-200"
                            :class="{ 'rotate-180': open }">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    {{-- Nested Tree Content --}}
                    <div x-show="open" x-collapse x-cloak>
                        <div class="ml-5 mt-1 space-y-1 border-l border-gray-100 pl-3">
                            @can('service-category.view')
                                <a href="{{ route('admin.service-categories.index') }}"
                                    class="{{ request()->routeIs('admin.service-categories.*') ? 'text-equator-dark font-bold bg-gray-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">
                                    Categories
                                </a>
                            @endcan
                            @can('service.view')
                                <a href="{{ route('admin.services.index') }}"
                                    class="{{ request()->routeIs('admin.services.*') ? 'text-equator-dark font-bold bg-gray-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">
                                    All Services
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
                @endcanany

                @can('project.view')
                {{-- Projects --}}
                <a href="{{ route('admin.projects.index') }}"
                    class="{{ request()->routeIs('admin.projects.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.projects.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path
                            d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z" />
                        <path d="M8 10v4" />
                        <path d="M12 10v2" />
                        <path d="M16 10v6" />
                    </svg>
                    <span class="text-sm">Projects</span>
                </a>
                @endcan
            </div>
        </div>

        {{-- GROUP: CONTENT MANAGEMENT --}}
        <div>

            <h3 class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">

                Content Management

            </h3>

            <div class="space-y-1">

                @can('hero-banner.view')
                {{-- HERO BANNERS --}}
                <a href="{{ route('admin.hero-banners.index') }}"
                    class="{{ request()->routeIs('admin.hero-banners.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">

                    {{-- ICON --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.hero-banners.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">

                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="M3 9h18" />
                        <path d="m9 21 3-3 3 3" />

                    </svg>

                    <span class="text-sm">

                        Hero Banners

                    </span>

                </a>
                @endcan

                @canany(['about-section.view', 'about-content.view', 'about-history.view'])
                {{-- ABOUT MENU (Accordion) --}}
                <div x-data="{ open: {{ request()->routeIs('admin.about-sections.*') || request()->routeIs('admin.about-contents.*') || request()->routeIs('admin.about-histories.*') ? 'true' : 'false' }} }" class="space-y-1">

                    {{-- Parent Button --}}
                    <button type="button" @click="open = !open"
                        class="{{ request()->routeIs('admin.about-sections.*') ||
                        request()->routeIs('admin.about-contents.*') ||
                        request()->routeIs('admin.about-histories.*')
                            ? 'bg-equator-dark/5 text-equator-dark font-bold'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex w-full items-center justify-between rounded-xl px-3 py-2.5 transition-colors">

                        <div class="flex items-center gap-3">

                            {{-- ICON --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="{{ request()->routeIs('admin.about-sections.*') ||
                                request()->routeIs('admin.about-contents.*') ||
                                request()->routeIs('admin.about-histories.*')
                                    ? 'text-equator-dark'
                                    : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">

                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />

                            </svg>

                            <span class="text-sm">

                                About

                            </span>

                        </div>

                        {{-- CHEVRON --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.about-sections.*') ||
                            request()->routeIs('admin.about-contents.*') ||
                            request()->routeIs('admin.about-histories.*')
                                ? 'text-equator-dark'
                                : 'text-gray-400' }} transition-transform duration-200"
                            :class="{ 'rotate-180': open }">

                            <path d="m6 9 6 6 6-6" />

                        </svg>

                    </button>

                    {{-- Nested Menu --}}
                    <div x-show="open" x-collapse x-cloak>

                        <div class="ml-5 mt-1 space-y-1 border-l border-gray-100 pl-3">

                            @can('about-section.view')
                                {{-- ABOUT SECTIONS --}}
                                <a href="{{ route('admin.about-sections.index') }}"
                                    class="{{ request()->routeIs('admin.about-sections.*')
                                        ? 'text-equator-dark font-bold bg-gray-50/50'
                                        : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">

                                    Sections

                                </a>
                            @endcan

                            @can('about-content.view')
                                {{-- ABOUT CONTENTS --}}
                                <a href="{{ route('admin.about-contents.index') }}"
                                    class="{{ request()->routeIs('admin.about-contents.*')
                                        ? 'text-equator-dark font-bold bg-gray-50/50'
                                        : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">

                                    Contents

                                </a>
                            @endcan

                            @can('about-history.view')
                                {{-- ABOUT HISTORIES --}}
                                <a href="{{ route('admin.about-histories.index') }}"
                                    class="{{ request()->routeIs('admin.about-histories.*')
                                        ? 'text-equator-dark font-bold bg-gray-50/50'
                                        : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">

                                    Histories

                                </a>
                            @endcan

                        </div>

                    </div>

                </div>
                @endcanany

                @can('core-value.view')
                {{-- CORE VALUES --}}
                <a href="{{ route('admin.core-values.index') }}"
                    class="{{ request()->routeIs('admin.core-values.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">

                    {{-- ICON --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.core-values.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">

                        <path
                            d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77 5.82 21l1.18-6.88-5-4.87 6.91-1.01L12 2z" />

                    </svg>

                    <span class="text-sm">

                        Core Values

                    </span>

                </a>
                @endcan

                @can('company-document.view')
                <a href="{{ route('admin.company-documents.index') }}"
                    class="{{ request()->routeIs('admin.company-documents.*') ? 'text-equator-dark font-bold bg-gray-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.company-documents.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path
                            d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z" />
                        <path d="M14 2v5a1 1 0 0 0 1 1h5" />
                    </svg>

                    <span class="text-sm">

                        Company Documents

                    </span>

                </a>
                @endcan

                @can('company-credential.view')
                {{-- COMPANY CREDENTIALS --}}
                <a href="{{ route('admin.company-credentials.index') }}"
                    class="{{ request()->routeIs('admin.company-credentials.*') ? 'text-equator-dark font-bold bg-gray-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.company-credentials.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path
                            d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>

                    <span class="text-sm">

                        Company Credentials

                    </span>

                </a>
                @endcan

                @can('partner.view')
                {{-- PARTNERS --}}
                <a href="{{ route('admin.partners.index') }}"
                    class="{{ request()->routeIs('admin.partners.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">

                    {{-- ICON --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.partners.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">

                        <path d="m11 17 2 2a1 1 0 1 0 3-3" />
                        <path
                            d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4" />
                        <path d="m21 3 1 11h-2" />
                        <path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3" />
                        <path d="M3 4h8" />

                    </svg>

                    <span class="text-sm">

                        Partners

                    </span>

                </a>
                @endcan


                @canany(['news-category.view', 'news.view'])
                {{-- NEWS MENU (Accordion) --}}
                <div x-data="{ open: {{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-categories.*') ? 'true' : 'false' }} }" class="space-y-1">

                    <button type="button" @click="open = !open"
                        class="{{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-categories.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex w-full items-center justify-between rounded-xl px-3 py-2.5 transition-colors">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="{{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-categories.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                                <path
                                    d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                                <path d="M18 14h-8" />
                                <path d="M15 18h-5" />
                                <path d="M10 6h8v4h-8V6Z" />
                            </svg>
                            <span class="text-sm">News</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-categories.*') ? 'text-equator-dark' : 'text-gray-400' }} transition-transform duration-200"
                            :class="{ 'rotate-180': open }">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak>
                        <div class="ml-5 mt-1 space-y-1 border-l border-gray-100 pl-3">
                            @can('news-category.view')
                                <a href="{{ route('admin.news-categories.index') }}"
                                    class="{{ request()->routeIs('admin.news-categories.*') ? 'text-equator-dark font-bold bg-gray-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">
                                    Categories
                                </a>
                            @endcan
                            @can('news.view')
                                <a href="{{ route('admin.news.index') }}"
                                    class="{{ request()->routeIs('admin.news.*') ? 'text-equator-dark font-bold bg-gray-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-medium' }} block rounded-lg px-3 py-2 text-sm transition-colors">
                                    All News
                                </a>
                            @endcan
                        </div>
                    </div>

                </div>
                @endcanany

                @can('faq.view')
                {{-- FAQ --}}
                <a href="{{ route('admin.faqs.index') }}"
                    class="{{ request()->routeIs('admin.faqs.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.faqs.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <path d="M12 17h.01" />
                    </svg>
                    <span class="text-sm">FAQ</span>
                </a>
                @endcan

                @can('key-metric.view')
                {{-- KEY METRICS --}}
                <a href="{{ route('admin.key-metrics.index') }}"
                    class="{{ request()->routeIs('admin.key-metrics.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.key-metrics.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <line x1="18" x2="18" y1="20" y2="10" />
                        <line x1="12" x2="12" y1="20" y2="4" />
                        <line x1="6" x2="6" y1="20" y2="14" />
                    </svg>
                    <span class="text-sm">Key Metrics</span>
                </a>
                @endcan

            </div>

        </div>

        {{-- GROUP: COMMUNICATION --}}
        <div>
            <h3 class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Communication</h3>
            <div class="space-y-1">
                @php $unreadMessages = \App\Models\Message::where('status', 'unread')->count(); @endphp

                @can('message.view')
                {{-- Messages (live unread badge) --}}
                <a href="{{ route('admin.messages.index') }}"
                    class="{{ request()->routeIs('admin.messages.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.messages.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    <span class="flex-1 text-sm">Messages</span>
                    @if ($unreadMessages > 0)
                        <span
                            class="flex h-5 min-w-[20px] items-center justify-center rounded-md bg-equator-bright px-1.5 text-[10px] font-bold text-white">{{ $unreadMessages }}</span>
                    @endif
                </a>
                @endcan
            </div>
        </div>

        {{-- GROUP: WORKSPACE --}}
        <div>
            <h3 class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Workspace</h3>
            <div class="space-y-1">
                @can('team.view')
                {{-- Team --}}
                <a href="{{ route('admin.teams.index') }}"
                    class="{{ request()->routeIs('admin.teams.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.teams.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <span class="text-sm">Team</span>
                </a>
                @endcan

            </div>
        </div>

        {{-- GROUP: SETTINGS --}}
        <div>
            <h3 class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Settings</h3>
            <div class="space-y-1">

                @can('setting.view')
                {{-- General Settings --}}
                <a href="{{ route('admin.settings.general') }}"
                    class="{{ request()->routeIs('admin.settings.general') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.settings.general') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path
                            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <span class="text-sm">General Settings</span>
                </a>
                @endcan

                @can('social-link.view')
                {{-- Social Links --}}
                <a href="{{ route('admin.social-links.index') }}"
                    class="{{ request()->routeIs('admin.social-links.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.social-links.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <circle cx="18" cy="5" r="3" />
                        <circle cx="6" cy="12" r="3" />
                        <circle cx="18" cy="19" r="3" />
                        <line x1="8.59" x2="15.42" y1="13.51" y2="17.49" />
                        <line x1="15.41" x2="8.59" y1="6.51" y2="10.49" />
                    </svg>
                    <span class="text-sm">Social Links</span>
                </a>
                @endcan

                @can('office-location.view')
                {{-- Office Locations --}}
                <a href="{{ route('admin.office-locations.index') }}"
                    class="{{ request()->routeIs('admin.office-locations.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="{{ request()->routeIs('admin.office-locations.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <span class="text-sm">Office Locations</span>
                </a>
                @endcan

                @if (auth('admin')->user()?->isSuperAdmin())
                    {{-- Admin Management (Super Admin) --}}
                    <a href="{{ route('admin.admins.index') }}"
                        class="{{ request()->routeIs('admin.admins.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.admins.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <line x1="19" x2="19" y1="8" y2="14" />
                            <line x1="22" x2="16" y1="11" y2="11" />
                        </svg>
                        <span class="text-sm">Admins</span>
                    </a>

                    {{-- Roles & Permissions (Super Admin) --}}
                    <a href="{{ route('admin.roles.index') }}"
                        class="{{ request()->routeIs('admin.roles.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.roles.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        <span class="text-sm">Roles &amp; Permissions</span>
                    </a>

                    {{-- Email Settings (Super Admin) --}}
                    <a href="{{ route('admin.settings.email') }}"
                        class="{{ request()->routeIs('admin.settings.email') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.settings.email') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                        <span class="text-sm">Email Settings</span>
                    </a>

                    {{-- Activity Log (Super Admin) --}}
                    <a href="{{ route('admin.activity-logs.index') }}"
                        class="{{ request()->routeIs('admin.activity-logs.*') ? 'bg-equator-dark/5 text-equator-dark font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="{{ request()->routeIs('admin.activity-logs.*') ? 'text-equator-dark' : 'text-gray-400 group-hover:text-gray-600' }} shrink-0">
                            <path d="M3 3v16a2 2 0 0 0 2 2h16" />
                            <path d="m19 9-5 5-4-4-3 3" />
                        </svg>
                        <span class="text-sm">Activity Log</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    {{-- Footer / Logout Area --}}
    <div class="shrink-0 border-t border-gray-100 p-4">
        <a href="/"
            class="group flex w-full items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-600 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 hover:shadow">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="shrink-0 text-gray-400 group-hover:text-gray-600">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" x2="9" y1="12" y2="12" />
            </svg>
            <span>Back to Public Site</span>
        </a>
    </div>
</aside>
